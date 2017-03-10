<?php

namespace SolrPlugin;


class SearchResults {
	/**
	 * SearchResults constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin ){
		$this->plugin = $plugin;
		
		/**
		 * render results
		 */
		add_shortcode(Plugin::ACTION_SEARCH_RESULTS, array($this, 'shortcode') );
		add_action(Plugin::SHORTCODE_SEARCH_RESULTS, array($this, 'action_search_results'));
		
		/**
		 * render a single search item
		 */
		add_action(Plugin::ACTION_SEARCH_RESULTS_ITEM, array($this,'action_search_results_item'),10,2);
	}
	
	/**
	 * search results for shortcode
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	function shortcode($atts){
		return $this->get_results();
	}
	
	/**
	 * render search results shortcode
	 */
	function action_search_results() {
		echo $this->get_results();
	}
	
	/**
	 * get html of results
	 * @return string
	 */
	function get_results(){
		ob_start();
		
		$solr_search_results = $this->plugin->request->get_search_results();
		$solr_search_args = $this->plugin->request->get_search_args();
		$e = $this->plugin->request->search_error_object;
		
		if($this->plugin->request->search_error){
			$error = $this->plugin->request->search_error_object;
			include $this->plugin->render->get_template_path(Plugin::TEMPLATE_ERROR);
		} else {
			include $this->plugin->render->get_template_path(Plugin::TEMPLATE_RESULTS);
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	/**
	 * render a single item of search results
	 * you can use global post function because we are in context of a loop
	 * @param \WP_Post
	 * @param \Solarium\QueryType\Select\Result\DocumentInterface $document
	 * @return string
	 */
	function action_search_results_item(\WP_Post $post, \Solarium\QueryType\Select\Result\DocumentInterface $document){
		include $this->plugin->render->get_template_path(Plugin::TEMPLATE_RESULTS_ITEM);
	}
}