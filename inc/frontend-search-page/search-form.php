<?php

namespace SolrPlugin;


class SearchForm {
	/**
	 * SearchForm constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ){
		$this->plugin = $plugin;
		
		/**
		 * change search form
		 */
		add_filter('get_search_form', array($this, 'get_search_form') );
		add_shortcode(Plugin::SHORTCODE_FORM, array($this,'shortcode') );
	}
	
	/**
	 * render form in shortcode
	 * @param $atts
	 *
	 * @return string
	 */
	function shortcode($atts){
		return $this->get_form();
	}
	
	/**
	 * render search form
	 *
	 * @param String $form
	 *
	 * @return string
	 */
	function get_search_form($form) {
		
		
		// TODO: why that if?
		if($this->plugin->request->search_error) return $form;
		
		
		return $this->get_form();
	}
	
	/**
	 * get search form html
	 * @return string
	 */
	function get_form(){
		
		
		ob_start();

		if($this->plugin->request->is_search()){
			$solr_search_results = $this->plugin->request->get_search_results();
			$solr_search_args = $this->plugin->request->get_search_args();
			$e = $this->plugin->request->search_error_object;
		}

		include $this->plugin->render->get_template_path(Plugin::TEMPLATE_FORM);
		
		
		$form = ob_get_contents();
		ob_end_clean();
		
		
		return $form;
		
	}
	
	
}