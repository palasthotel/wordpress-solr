<?php

namespace SolrPlugin;


class Render {
	
	/**
	 * Render constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct($plugin) {
		$this->plugin = $plugin;
		if($this->plugin->is_enabled()) {
			/**
			 * if enabled activate template suggestions
			 */
			add_filter( 'template_include', array( $this, 'search_template' ), 99 );
		}
	}
	
	/**
	 * Add a new template when solr search is triggered
	 * @param $template
	 * @return string
	 */
	function search_template( $template ) {
		
		if ( $this->plugin->request->is_search() ) {
			/**
			 * return solr plugin search template
			 */
			return $this->get_template_file(Plugin::TEMPLATE_SEARCH);
		}
		
		/**
		 * return WordPress default template
		 */
		return $template;
	}
	
	/**
	 * Render template
	 *
	 * @param $template
	 */
	public function render($template){
		$solr_search_results = $this->plugin->request->get_search_results();
		$solr_search_args = $this->plugin->request->get_search_args();
		$e = $this->plugin->request->search_error_object;
		include $this->get_template_file($template);
	}
	
	/**
	 * returns path to theme template if exists
	 * else plugin template
	 * @param string $file filename of template
	 * @return string
	 */
	public function get_template_file($file){
		$solr_template = locate_template( Plugin::THEME_FOLDER.'/'.$file );
		if ( '' != $solr_template ) {
			return $solr_template;
		}
		return "{$this->plugin->dir}/templates/{$file}";
	}
}