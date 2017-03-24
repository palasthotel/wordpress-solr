<?php

namespace SolrPlugin;


class FrontendSearch {
	
	/**
	 * FrontendSearch constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		if ( $this->plugin->is_enabled() ) {
			/**
			 * if enabled overwrite default search
			 * and do the search if needed
			 */
			add_filter( 'wp_title', array($this, 'title') );
			add_filter( 'wpseo_title', array($this, 'title') );
			add_action( 'init', array( $this, 'do_search' ) );
			add_filter( 'posts_request', array( $this, 'disable_search_query' ), 10, 2 );
		}
	}
	
	/**
	 * @param $title
	 *
	 * @return string
	 */
	function title( $title ) {
		if ( $this->plugin->request->is_search() ) {
			$title = "test";
		}
		
		return $title;
	}
	
	/**
	 * do the search in init action
	 */
	function do_search() {
		if ( $this->plugin->request->is_search() ) {
			$this->plugin->request->get_search_results();
			wp_title( 'geiles zeug' );
		}
		
	}
	
	/**
	 * disable_search_query
	 *
	 * @param array $request
	 * @param $query \WP_Query
	 *
	 * @return array|bool
	 */
	function disable_search_query( $request, $query ) {
		/**
		 * if it is the main query and its a search and not in backend
		 */
		if ( $query->is_main_query() &&
		     $this->plugin->request->is_search() &&
		     ! is_admin()
		) {
			return FALSE;
		}
		
		return $request;
	}
	
	
}