<?php

namespace SolrPlugin;


class IndexRunner {
	
	/**
	 * IndexRun constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}
	
	/**
	 * save latest run to options
	 */
	public function save_latest_run() {
		update_option( Plugin::OPTION_LAST_INDEX_RUN, date( "Y-m-d h:i:s" ) );
	}
	
	/**
	 * get last run
	 * @return string
	 */
	public function get_latest_run() {
		return get_option( Plugin::OPTION_LAST_INDEX_RUN );
	}
	
	/**
	 * update index by number of posts
	 *
	 * @param int $number number of posts to be updated
	 *
	 * @return boolean|object
	 */
	public function index_posts( $number = 100 ) {
		/**
		 * get modified
		 */
		$modified_posts = $this->plugin->posts->getModified( $number );
		
		/**
		 * if there are no modified return
		 */
		if ( count( $modified_posts ) < 1 ) {
			return (object) array( "posts" => $modified_posts, "error" => FALSE );
		}
		
		/**
		 * check if goes through ignore filter
		 */
		$index_posts = array();
		for ( $i = 0; $i < count( $modified_posts ); $i ++ ) {
			global $post;
			$post = $modified_posts[ $i ];
			setup_postdata($post);
			
			/**
			 * if not ignored ad to
			 */
			if ( ! apply_filters( Plugin::FILTER_SOLR_INDEX_IGNORE_POST, false, $post ) ) {
				$index_posts[] = $post;
			} else {
				$this->plugin->posts->set_ignored( $post->ID );
			}
		}

		// reset global post
		wp_reset_postdata();
		
		/**
		 * if no posts left after filter rerun method
		 */
		if ( count( $index_posts ) < 1 ) {
			return $this->index_posts( $number );
		}
		
		/**
		 * update index with a number of posts because that's faster
		 * @var  \Solarium\QueryType\Update\Result $result
		 */
		try {
			$result = $this->plugin->solr_index->updatePost( $index_posts );
		} catch ( \Solarium\Exception\HTTPException $e ) {
			var_dump($e);
			
			/**
			 * on error try every single one and log error
			 */
			for ( $i = 0; $i < count( $index_posts ); $i ++ ) {
				$post = $index_posts[ $i ];
				$this->plugin->posts->set_error( $post->ID );
			}
		}
		
		$verify = $this->_verify_result( $result, $index_posts );
		if ( ! $verify->error ) {
			$this->save_latest_run();
		}
		
		return $verify;
		
	}
	
	/**
	 * return result of indexing
	 *
	 * @param  \Solarium\QueryType\Update\Result $result
	 * @param  array $posts
	 *
	 * @return object $result
	 */
	private function _verify_result( $result, $posts ) {
		/**
		 * Success response is 0
		 * http://solarium.readthedocs.org/en/stable/queries/update-query/the-result-of-an-update-query/
		 */
		if ( is_object( $result ) && ! empty( $result ) && $result->getStatus() === 0 ) {
			foreach ( $posts as $counter => $post ) {
				/* @var $post \WP_Post */
				$this->plugin->posts->set_indexed( $post->ID );
			}
			
			return (object) array( "posts" => $posts, "result" => $result, "error" => FALSE );
		}
		
		return (object) array( "error" => TRUE );
	}
}