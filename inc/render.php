<?php

namespace SolrPlugin;


class Render {
	
	/**
	 * Render constructor
	 *
	 * @param Plugin $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->sub_dirs = null;
	}
	
	/**
	 * Look for existing template path
	 * @return string|false
	 */
	function get_template_path( $template ) {
		
		if ( $overridden_template = locate_template( $this->get_template_dirs($template) ) ) {
			return $overridden_template;
		}
		
		return $this->plugin->dir . 'template/' . $template;
	}
	
	/**
	 * get array of possible template files in theme
	 * @param $template
	 *
	 * @return array
	 */
	function get_template_dirs($template){
		$dirs = array(
			Plugin::THEME_FOLDER . "/" . $template,
		);
		foreach ($this->get_sub_dirs() as $sub){
			$dirs[] = $sub.'/'.$template;
		}
		return $dirs;
	}
	
	/**
	 * paths for locate_template
	 * @return array
	 */
	function get_sub_dirs(){
		if($this->sub_dirs == null){
			$this->sub_dirs = array();
			$dirs = array_filter(glob(get_template_directory().'/'.Plugin::THEME_FOLDER.'/*'), 'is_dir');
			foreach($dirs as $dir){
				$this->sub_dirs[] = str_replace(get_template_directory().'/', '', $dir);
			}
		}
		return $this->sub_dirs;
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
		include $this->get_template_path($template);
	}

	
	
}