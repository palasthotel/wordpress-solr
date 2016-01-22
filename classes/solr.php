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

		$this->add_search_filters();
	}

	/**
	 * get solr configuration
	 * @return mixed|void
	 */
	public function getConfiguration() {
		return $this->plugin->get_config()->get_solr_config();
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
		$document->type = $post->post_type;
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
	 * add all filters for search select
	 */
	private function add_search_filters(){
		add_filter('solr_search_select',array($this,'search_select_query'),10,3);
		add_filter('solr_search_select',array($this,'search_select_state'),10,3);
		add_filter('solr_search_select',array($this,'search_select_page'),10,3);
		add_filter('solr_search_select',array($this,'search_select_boost'),10,3);
		add_filter('solr_search_select',array($this,'search_select_spellchecker'),10,3);
		add_filter('solr_search_select',array($this,'search_select_facets'),10,3);
		add_filter('solr_search_select',array($this,'search_select_weight'),10,3);
		add_filter('solr_search_select',array($this,'search_select_query_operator'),10,3);
	}

	/**
	 * search for query string
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_query($select,$search_args, $config){
		if(!empty($search_args)){
			$query = $search_args['s'];
			$select->setQuery($query);
		}
		return $select;
	}

	/**
	 * search for published
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_state($select,$search_args, $config){
		$select->createFilterQuery('published')->setQuery('published:true');
		return $select;
	}

	/**
	 * search on page num
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_page($select,$search_args, $config){
		$select->setStart(($search_args['page'] - 1) * $config['query_limit']);
		$select->setRows($config['query_limit']);
		return $select;
	}

	/**
	 * search boost function
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_boost($select,$search_args, $config){
		if (!empty($config['boost_functions']) && count($config['boost_functions']) > 0) {
			$dismax = $select->getDisMax();
			$dismax->setBoostFunctions(implode(' ', $config['boost_functions']));
		}
		return $select;
	}

	/**
	 * search spellchecker
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_spellchecker($select,$search_args, $config){
		if (!empty($config['spellcheck']) && $config['spellcheck']) {
			$spellcheck = $select->getSpellcheck();
			$spellcheck->setQuery($query);
			$spellcheck->setCount(10);
			$spellcheck->setBuild(TRUE);
			$spellcheck->setCollate(TRUE);
			$spellcheck->setExtendedResults(TRUE);
			$spellcheck->setCollateExtendedResults(TRUE);
		}
		return $select;
	}

	/**
	 * search for facets
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_facets($select,$search_args, $config){

		if(!empty($search_args['facets']) && $search_args['facets'] ) {
			foreach ($search_args['facets'] as $facet_key_val => $enabled) {
				if ($enabled) {
					$kv = explode('-', $facet_key_val);
					$filter_query = new \Solarium\QueryType\Select\Query\FilterQuery();
					$filter_query->setKey(strtolower($kv[0]));
					$filter_query->setQuery($kv[1]);
					$select->addFilterQuery($filter_query);
				}
			}
		}

		if ( isset($config['facets']) ) {
			$facetSet = $select->getFacetSet();

			// content type facet
			if (isset($config['facets']['type'])) {
				$facet = $config['facets']['type'];
				// type facet
				$facetSet->createFacetField($facet['title'])
				  ->setField($facet['field'])
				  ->setMinCount(1);
			}

			// year facet
			if (isset($config['facets']['date'])) {
				$facet = $config['facets']['date'];
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
		return $select;
	}

	/**
	 * search weight of fields
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_weight($select,$search_args,$config){
		// weight of fields
		$dismax = $select->getDisMax();

		// build the weight string
		$weightString = '';
		foreach ($config['search_fields'] as $field => $weight) {
			$weightString .= " $field^$weight";
		}
		$dismax->setQueryFields(substr($weightString, 1));
		return $select;
	}

	/**
	 * search query operator
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @param $search_args
	 * @param $config
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	public function search_select_query_operator($select,$search_args,$config){
		// set the default operator
		$select->setQueryDefaultOperator($config['default_query_operator']);
		return $select;
	}


	/**
	 * Runs the search.
	 *
	 * @param array|null $search_args
	 * @return \Solarium\QueryType\Select\Result\Result search results
	 */
	public function search(array $search_args = null) {
		/**
		 * no search args return empty result
		 */
		if (empty($search_args)) {
			return array();
		}

		$config = $this->plugin->get_config()->get_solr_config();

		/**
		 * create new select query
		 * @var \Solarium\QueryType\Select\Query\Query $select
		 */
		$select = $this->client->createSelect();

		/**
		 * build select with filters
		 */
		$select = apply_filters('solr_search_select',$select,$search_args,$config);

		/**
		 * set result fields
		 */
		$select->setFields($config['result_fields']);

		return $this->client->select($select);
	}
}
