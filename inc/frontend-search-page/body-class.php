<?php

namespace SolrPlugin;


class BodyClass {
	
	/**
	 * BodyClass constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( $plugin ){
		$this->plugin = $plugin;
		
		/**
		 * body class filter
		 */
		add_filter('body_class', array($this, 'body_class'));
		
	}
	
	/**
	 * add body classes depending on search results
	 * @param array $classes
	 * @return array
	 */
	function body_class( array $classes ) {
		if(!$this->plugin->request->is_search()) return $classes;
		if(!$this->plugin->request->search_error && $this->plugin->request->get_search_results()!= null){
			$found = ($this->plugin->request->get_search_results()->getNumFound() > 0);
			$classes[] = "solr-search";
			if($found){
				$classes[] = "search-results";
				for($i = 0; $i < count($classes); $i++){
					if($classes[$i] == "search-no-results" && $found){
						array_splice($classes,$i,1);
					}
				}
			} else {
				$classes[] = "search-no-results";
			}
		}
		return $classes;
	}
}