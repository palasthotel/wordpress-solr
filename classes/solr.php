<?php
namespace SolrPlugin;

/**
 * Class Solr
 * @package SolrPlugin
 */
class Solr {
	/**
	 * @var \SolrPlugin
	 */
	private $plugin;
	/**
	 * @var \Solarium\Client
	 */
	private $client;

	/**
	 * Solr constructor.
	 * @param \SolrPlugin $plugin
	 */
	public function __construct(\SolrPlugin $plugin) {
		$this->plugin = $plugin;
		$this->client = $this->plugin->get_solarium();
		add_filter('solr_add_post_fields', array($this, 'add_post_fields'),10,2);
		add_filter('solr_add_comment_fields', array($this, 'add_comment_fields'),10,2);
		add_filter('solr_is_supported_type', array($this, 'is_supported_type'),10,2);
	}

	/**
	 * get solr configuration
	 * @return mixed|void
	 */
	public function getConfiguration() {
		return $this->plugin->get_config()->get_solr_config();
	}

	/**
	 * get search args from url query
	 * @return array
	 */
	public function getSearchArgs() {
		return $this->plugin->get_search_args();
	}

	/**
	 * Sets the fields from a WP_Post object to a Solarium Document, which will be
	 * uploaded to Solr.
	 *
	 * @param \Solarium\QueryType\Update\Query\Document\DocumentInterface $document
	 * @param \WP_Post $post
	 * @return \Solarium\QueryType\Update\Query\Document\DocumentInterface
	 */
	public function add_post_fields(\Solarium\QueryType\Update\Query\Document\DocumentInterface $document, \WP_Post $post) {
		// get the authors name
		$author_name = get_user_by('id', $post->post_author)->display_name;
		$document->title = $post->post_title;
		$document->date = date('Y-m-d\TH:i:s\Z', strtotime($post->post_date_gmt));
		$document->modified = date('Y-m-d\TH:i:s\Z', strtotime($post->post_modified_gmt));
		$document->author = $author_name;
		$document->content = strip_tags($post->post_content);
		$document->url = $post->guid;
		// set categories
		$categories = get_the_category($post->ID);
		foreach ($categories as $category) {
			$document->addField('category', $category->cat_name);
		}
		// set published field (boolean value)
		$document->published = $post->post_status === 'publish';
		$document->type = $post->type;
		return $document;
	}

	/**
	 * Sets the fields from a WP_Comment object to a Solarium Document, which will be
	 * uploaded to Solr.
	 *
	 * @param \Solarium\QueryType\Update\Query\Document\DocumentInterface $document
	 * @param \WP_Comment $comment
	 * @return \Solarium\QueryType\Update\Query\Document\DocumentInterface
	 */
	public function add_comment_fields( \Solarium\QueryType\Update\Query\Document\DocumentInterface $document,  \WP_Comment $comment) {
		$document->content = $comment['comment_content'];
		$document->author = $comment['comment_author'];
		$document->date = date('Y-m-d\TH:i:s\Z', strtotime($comment['comment_date_gmt']));
		$document->type = 'comment';
		return $document;
	}

	/**
	 * supported types by core are post and comment
	 * @param boolean $supported is true if any filter before supports type
	 * @param string $type name of the document type
	 * @return boolean
	 */
	public function is_supported_type($supported, $type){
		/**
		 * if already supported by other filter or supported by solr core itself return true
		 */
		if($supported || $type == 'comment' || $type == 'post') return true;
		return false;
	}

	/**
	 * create update
	 * @param string $type
	 * @return \Solarium\QueryType\Update\Query\Query
	 * @throws \SolrPlugin\Exception
	 */
	private function createUpdate($type){
		/**
		 * check if type is supported else throw exception
		 */
		$supported = apply_filters('solr_is_supported_type',false, $type);
		if(!$supported) throw new Exception('unknown document type');

		/**
		 * so create new update
		 */
		return $this->client->createUpdate();
	}

	/**
	 * Update solr index for items
	 * @param array $changedItems
	 * @param string $type type of solr content (post or comment)
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \SolrPlugin\Exception
	 */
	private function updateItemIndex(array $changedItems, $type) {

		/**
		 * so create new update
		 */
		$update = $this->createUpdate($type);

		/**
		 * for each item, add a document to the update query
		 */
		foreach ($changedItems as $item) {
			/**
			 * create a new document for the data
			 */
			$doc = $update->createDocument();

			/**
			 * add type specific fields
			 * filters are dynamically extensible
			 */
			$doc = apply_filters('solr_add_'.$type.'_fields',$doc,$item);

			/**
			 * set the ID
			 */
			$doc->id = $type . '/' . $item->ID;

			/**
			 * add document to update
			 */
			$update->addDocument($doc);
		}

		return $this->doUpdate($update);

	}

	/**
	 * @param array $deleted
	 * @param string $type
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \SolrPlugin\Exception
	 */
	private function deleteItemIndex(array $deleted, $type){
		$update = $this->createUpdate($type);
		/**
		 * delete items from index
		 */
		foreach ($deleted as $item) {
			$update->addDeleteById($type . '/' . $item->ID);
		}
		return $this->doUpdate($update);
	}

	/**
	 * @param $update \Solarium\QueryType\Update\Query\Query
	 * @return \Solarium\QueryType\Update\Result
	 */
	private function doUpdate(\Solarium\QueryType\Update\Query\Query $update){
		/**
		 * commit the update
		 */
		$update->addCommit();

		/**
		 * execute the update or throw exception if it fails
		 */
		try {
			$result = $this->client->update($update);
			return $result;
		} catch (\Solarium\Exception\HTTPException $e) {
			die($e->getMessage());
		}
	}

	/**
	 * Update posts to solr
	 * @param array $modified
	 * @return \Solarium\QueryType\Update\Result
	 */
	public function updatePostIndex(array $modified) {
		return $this->updateItemIndex($modified,'post');
	}

	/**
	 * delete index of items
	 * @param array $deleted
	 * @return \Solarium\QueryType\Update\Result
	 */
	public function deletePostIndex(array $deleted){
		return $this->deleteItemIndex($deleted, 'post');
	}

	/**
	 * Update comments to solr
	 * @param array $modified
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \SolrPlugin\Exception
	 */
	public function updateCommentIndex( $modified ) {
		return $this->updateItemIndex($modified,'comment');
	}



	/**
	 * optimize solr index
	 * @return \Solarium\QueryType\Update\Result
	 */
	public function optimizeIndex() {
		// get an update query instance
		$update = $this->client->createUpdate();

		// optimize the index
		$update->addOptimize(NULL, NULL, NULL); // use solr defaults

		// this executes the query and returns the result
		try {
			$result = $this->client->update($update);
			return $result;
		} catch (\Solarium\Exception\HTTPException $e) {
			die($e->getMessage());
		}
	}

	/**
	 * delete solr index completely
	 * @return \Solarium\QueryType\Update\Result
	 */
	public function deleteIndex() {
		// get an update query instance
		$update = $this->client->createUpdate();

		// delete the index
		$update->addDeleteQuery('*:*');

		// this executes the query and returns the result
		try {
			$result = $this->client->update($update);
			return $result;
		} catch (\Solarium\Exception\HTTPException $e) {
			die($e->getMessage());
		}
	}

	/**
	 * Runs the search.
	 *
	 * @return array search results
	 */
	public function search() {
		if ($this->search_args === FALSE) {
			return array();
		}

		$select = $this->client->createSelect();

		// set search query
		$query = $this->search_args['text'];
		$select->setQuery($query);

		$filter = $select->createFilterQuery('published')
		  ->setQuery('published:true');

		// set query offset and limit
		$select->setStart(($this->search_args['page'] - 1) * $this->config['query_limit']);
		$select->setRows($this->config['query_limit']);

		// set result fields
		$select->setFields($this->config['result_fields']);

		if (count($this->config['boost_functions']) > 0) {
			$dismax = $select->getDisMax();
			$dismax->setBoostFunctions(implode(' ', $this->config['boost_functions']));
		}

		// enable spellchecker
		if ($this->config['spellcheck']) {
			$spellcheck = $select->getSpellcheck();
			$spellcheck->setQuery($query);
			$spellcheck->setCount(10);
			$spellcheck->setBuild(TRUE);
			$spellcheck->setCollate(TRUE);
			$spellcheck->setExtendedResults(TRUE);
			$spellcheck->setCollateExtendedResults(TRUE);
		}

		// apply facets
		if ($this->search_args['facets']) {
			foreach ($this->search_args['facets'] as $facet_key_val => $enabled) {
				if ($enabled) {
					$kv = explode('-', $facet_key_val);
					$filter_query = new \Solarium\QueryType\Select\Query\FilterQuery();
					$filter_query->setKey(strtolower($kv[0]));
					$filter_query->setQuery($kv[1]);
					$select->addFilterQuery($filter_query);
				}
			}
		}

		// show other facets
		if (isset($this->config['facets'])) {
			$facetSet = $select->getFacetSet();

			// content type facet
			if (isset($this->config['facets']['type'])) {
				$facet = $this->config['facets']['type'];
				// type facet
				$facetSet->createFacetField($facet['title'])
				  ->setField($facet['field'])
				  ->setMinCount(
					1);
			}

			// year facet
			if (isset($this->config['facets']['date'])) {
				$facet = $this->config['facets']['date'];
				// the date facet
				// from epoch until now
				$facetSet->createFacetRange($facet['title'])
				  ->setField($facet['field'])
				  ->setStart(
					'1970-01-01T00:00:00Z')
				  ->setEnd(
					str_replace('+00:00', 'Z', date('c')))
				  ->setGap('+1YEAR');
			}
		}

		// weight of fields
		$dismax = $select->getDisMax();

		// build the weight string
		$weightString = '';
		foreach ($this->config['search_fields'] as $field => $weight) {
			$weightString .= " $field^$weight";
		}

		$dismax->setQueryFields(substr($weightString, 1));

		// set the default operator
		$select->setQueryDefaultOperator($this->config['default_query_operator']);

		$search_results = $this->client->select($select);

		return $search_results;
	}

	public function showResults($search_page_id, $search_results) {

	}
}
