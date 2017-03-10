<?php

namespace SolrPlugin;


class Request {
	
	const VAR_QUERY = 's';
	const VAR_PAGED = 'paged';
	const VAR_FACETS = 'facets';
	
	/**
	 * @var \Solarium\QueryType\Select\Result\Result|null
	 */
	public $search_results;
	
	/**
	 * @var boolean
	 */
	public $search_error;
	/**
	 * @var \Solarium\Exception\HttpException $e
	 */
	public $search_error_object;
	
	/**
	 * Request constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin      = $plugin;
		$this->search_args = NULL;
	}
	
	/**
	 * is search if there is a search query
	 * @return bool
	 */
	function is_search(){
		return isset($_GET['s']);
	}
	
	/**
	 * Returns the search arguments as an associative array or FALSE if there was no
	 * search.
	 *
	 * @return array
	 */
	function get_search_args() {
		if ( $this->search_args == NULL ) {
			$this->search_args = array();
			if ( isset( $_GET[ self::VAR_QUERY ] ) ) {
				$this->search_args[ self::VAR_QUERY ] = $_GET[ self::VAR_QUERY ];
			}
			
			/**
			 * wild guess paged variable
			 */
			if ( isset( $_GET[self::VAR_PAGED ] ) ) {
				$this->search_args[ self::VAR_PAGED ] = (int) $_GET[self::VAR_PAGED ];
			} else {
				global $paged;
				if ( isset( $paged ) && is_int( $paged ) ) {
					$this->search_args[ self::VAR_PAGED ] = $paged;
				} else {
					$this->search_args[ self::VAR_PAGED ] = 1;
				}
			}
			
			/**
			 * paged validation
			 */
			if ( $this->search_args[ self::VAR_PAGED ] < 1 ) {
				$this->search_args[ self::VAR_PAGED ] = 1;
			}
			
			/**
			 * get facets
			 */
			// TODO: use facets like in backend
			$facet_args = array();
			foreach ( $_GET as $key => $value ) {
				if ( strpos( $key, 'facet-' ) === 0 ) {
					$parts                   = explode( '-', $key );
					$facet_args[ $parts[1] ] = $_GET[ $key ];
				}
			}
			$this->search_args[ self::VAR_FACETS ] = $facet_args;
		}
		
		return $this->search_args;
	}
	
	/**
	 * get the search results
	 * @return null|\Solarium\QueryType\Select\Result\Result
	 */
	function get_search_results( $args = NULL ) {
		if ( $this->search_results == NULL
		     && $this->search_error == FALSE
		) {
			try {
				if ( $args != NULL ) {
					$this->search_results = $this->plugin->solr_search->execute( $args );
				} else {
					$this->search_results = $this->plugin->solr_search->execute( $this->get_search_args() );
				}
				$this->search_error = FALSE;
			} catch ( \Solarium\Exception\HttpException $e ) {
				$this->search_error        = TRUE;
				$this->search_error_object = $e;
			}
		}
		
		return $this->search_results;
	}
}