<?php

namespace SolrPlugin;


class FrontendSearchPage {
	
	/**
	 * Posts constructor.
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ){
		$this->plugin = $plugin;
		
		require_once "frontend-search-page/body-class.php";
		$this->body_class = new BodyClass($plugin);
		
		require_once "frontend-search-page/search-form.php";
		$this->search_form = new SearchForm($plugin);
		
		require_once "frontend-search-page/search-results.php";
		$this->search_results = new SearchResults($plugin);
		
		require_once "frontend-search-page/search-spellcheck.php";
		$this->search_spellcheck = new SearchSpellcheck($plugin);
		
		require_once "frontend-search-page/search-pagination.php";
		$this->search_pagination = new SearchPagination($plugin);
		
		/**
		 * intercept template suggestion
		 */
		add_filter( 'template_include', array( $this, 'search_template' ), 99 );

		/**
		 * change page title
		 */
		add_filter( 'wpseo_title', array( $this, 'title' ) );

	}
	
	/**
	 * Add a new template when solr search is triggered
	 * @param $template
	 * @return string
	 */
	function search_template( $template ) {
		
		if ( $this->plugin->is_enabled() && $this->plugin->request->is_search() ) {
			/**
			 * return solr plugin search template
			 */
			return $this->plugin->render->get_template_path(Plugin::TEMPLATE_SEARCH);
		}
		
		/**
		 * return WordPress default template
		 */
		return $template;
	}

	/**
	 * @param $title
	 *
	 * @return string
	 */
	function title( $title ) {
		if ( $this->plugin->request->is_search() ) {
			$s = $this->plugin->request->get_search_args()[ Request::VAR_QUERY ];

			return apply_filters( Plugin::FILTER_SEARCH_PAGE_TITLE, __( "Search: ", Plugin::DOMAIN ) . " {$s}", $s );
		}

		return $title;
	}
	
}