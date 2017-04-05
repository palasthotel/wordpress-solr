<?php

namespace SolrPlugin;


class SolrIndex {
	
	/**
	 * SolrSearch constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		require_once "solr-index/solr-post-fields.php";
		$this->post_fields = new SolrPostFields($plugin);
		
		require_once "solr-index/solr-comment-fields.php";
		$this->comment_fields = new SolrCommentFields($plugin);
	}
	
	/**
	 * get solarium client instance
	 * @return \Solarium\Client
	 */
	private function client() {
		return Solarium::instance( $this->plugin );
	}
	
	/**
	 * Update posts to solr
	 *
	 * @param array $modified
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	public function updatePost( array $modified ) {
		return $this->updateItem( $modified, 'post' );
	}
	
	/**
	 * Update comments to solr
	 *
	 * @param array $modified
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	public function updateComment( $modified ) {
		return $this->updateItem( $modified, 'comment' );
	}
	
	/**
	 * Update solr index for items
	 *
	 * @param array $changedItems
	 * @param string $type type of solr content (post or comment)
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	private function updateItem( array $changedItems, $type ) {
		
		/**
		 * so create new update
		 * @var \Solarium\QueryType\Update\Query\Query $update
		 */
		$update = $this->client()->createUpdate();
		
		/**
		 * for each item, add a document to the update query
		 */
		foreach ( $changedItems as $item ) {
			/**
			 * create a new document for the data
			 */
			$doc = $update->createDocument();
			
			/**
			 * add type specific fields
			 * filters are dynamically extensible
			 */
			$doc = apply_filters( str_replace( Plugin::FILTER_SOLR_INDEX_FIELDS_PLACEHOLDER, $type, Plugin::FILTER_SOLR_INDEX_FIELDS ), $doc, $item, $update );
			
			/**
			 * set the ID
			 */
			$doc->id = $type . '/' . $item->ID;
			
			/**
			 * add document to update
			 */
			$update->addDocument( $doc );
		}
		
		return $this->doUpdate( $update );
		
	}
	
	/**
	 * delete index of items
	 *
	 * @param array $deleted of WP_Post
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	public function deletePost( array $deleted ) {
		return $this->deleteItem( $deleted, 'post' );
	}
	
	/**
	 * @param array $deleted
	 * @param string $type
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	private function deleteItem( array $deleted, $type ) {
		$update = $this->client()->createUpdate();
		/**
		 * delete items from index
		 */
		foreach ( $deleted as $item ) {
			$update->addDeleteById( $type . '/' . $item->ID );
		}
		
		return $this->doUpdate( $update );
	}
	
	/**
	 * @param $update \Solarium\QueryType\Update\Query\Query
	 *
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	private function doUpdate( \Solarium\QueryType\Update\Query\Query $update ) {
        try {
            /**
             * commit the update
             */
            $update->addCommit();

            /**
             * execute the update or throw exception if it fails
             */
            return $this->client()->update( $update );

        } catch( HttpException $e) {
            if(defined('SOLR_DEBUG')) {
                throw $e;
            }
        }
	}
	
	/**
	 * optimize solr index
	 * @return \Solarium\QueryType\Update\Result
	 */
	public function optimize() {
		// get an update query instance
		$update = $this->client()->createUpdate();
		
		// optimize the index
		$update->addOptimize( NULL, NULL, NULL ); // use solr defaults
		
		// this executes the query and returns the result
		try {
			$result = $this->client()->update( $update );
			
			return $result;
		} catch ( \Solarium\Exception\HTTPException $e ) {
			die( $e->getMessage() );
		}
	}
	
	/**
	 * delete solr index completely
	 * @return \Solarium\QueryType\Update\Result
	 * @throws \Solarium\Exception\HTTPException
	 */
	public function deleteAll() {
		// get an update query instance
		$update = $this->client()->createUpdate();
		
		// delete the index
		$update->addDeleteQuery( '*:*' );
		
		// this executes the query and returns the result
		return $this->client()->update( $update );
	}
	
	/**
	 * get number of documents in index
	 * @return integer
	 */
	public function size(){
		try{
			$query = $this->client()->createQuery(\Solarium\Client::QUERY_SELECT);
			/**
			 * @var \Solarium\QueryType\Select\Result\Result $result
			 */
			$result = $this->client()->execute($query);
			if(is_object($result)){
				return $result->getNumFound();
			}
		} catch (\Solarium\Exception\HttpException $e){
			var_dump($e);
		}
		return -1;
	}
	
}