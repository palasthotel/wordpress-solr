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
			add_action( 'posts_request', array( $this, 'do_search' ), 10, 2 );
			add_filter( 'posts_request', array( $this, 'disable_search_query' ), 10, 2 );
		}
	}

	/**
	 * do the search in init action
	 * @param array $request
	 * @param $query \WP_Query
	 *
	 * @return array|bool
	 */
	function do_search($request, $query) {
		if ( $this->plugin->request->is_search() ) {
			$this->plugin->request->get_search_results();
		}
		return $request;

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
			return false;
		}

		return $request;
	}


}