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
			add_filter( 'posts_request', array( $this, 'disable_search_query' ), 10, 2 );
			add_filter( 'pre_handle_404', array($this, 'pre_handle_404' ));
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
			global $wpdb;
			// deliver zero results as we will use solr for results
			return "SELECT SQL_CALC_FOUND_ROWS  $wpdb->posts.* FROM $wpdb->posts WHERE 1=0";
		}

		return $request;
	}

	function pre_handle_404($preempt){
		global $wp_query;
		if ( $wp_query->is_main_query() &&
		     $this->plugin->request->is_search() &&
		     ! is_admin()
		) {
			status_header( 200 );
			return true;
		}
		return $preempt;
	}

}