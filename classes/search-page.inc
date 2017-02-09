<?php

namespace SolrPlugin;


class SearchPage
{

	/**
	 * Posts constructor.
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ){
		$this->plugin = $plugin;

		/**
		 * change search form
		 */
		add_filter('get_search_form', array($this, 'get_search_form_html') );

		/**
		 * render search forms
		 */
		add_shortcode('solr_search_form', array($this,'get_search_form_html') );
		add_action('solr_search_form', array($this, 'render_search_form'));

		/**
		 * render results
		 */
		add_shortcode('solr_search_results', array($this, 'get_search_results_html') );
		add_action('solr_search_results', array($this, 'render_search_results'));

		/**
		 * render a single search item
		 */
		add_action('solr_search_results_item', array($this,'render_search_results_item'),10,2);

		/**
		 * spell checking display
		 */
		add_action('solr_search_spellcheck', array($this, 'render_search_spellcheck'));

		/**
		 * body class filter
		 */
		add_filter('body_class', array($this, 'body_class'));

	}

	/**
	 * search results for shortcode
	 * @return string
	 */
	function get_search_results_html(){
		ob_start();
		$this->render_search_results();
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	/**
	 * render search results shortcode
	 */
	function render_search_results() {
		/**
		 * global use of search results
		 */
		if($this->plugin->request->search_error){
			$e = $this->plugin->request->search_error_object;
			$this->plugin->render->render(Plugin::TEMPLATE_ERROR);
		} else {
			$this->plugin->render->render(Plugin::TEMPLATE_RESULTS);
		}
	}

	/**
	 * render a single item of search results
	 * you can use global post function because we are in context of a loop
	 * @param \WP_Post
	 * @param \Solarium\QueryType\Select\Result\DocumentInterface $document
	 * @return string
	 */
	function render_search_results_item(\WP_Post $post, \Solarium\QueryType\Select\Result\DocumentInterface $document){
		include $this->plugin->render->get_template_file(Plugin::TEMPLATE_RESULTS_ITEM);
	}

	/**
	 * render spellcheck display
	 * @param $solr_search_results
	 */
	function render_search_spellcheck(){
		$this->plugin->render->render(Plugin::TEMPLATE_SPELLCHECK);
	}
	
	/**
	 * render search form
	 *
	 * @param String $form
	 *
	 * @return string
	 */
	function get_search_form_html($form) {
		if($this->plugin->request->search_error) return $form;
		ob_start();
		$this->render_search_form();
		$form = ob_get_clean();
		ob_end_clean();
		return $form;
	}

	/**
	 * render search form
	 * @param $form
	 */
	function render_search_form() {
		$this->plugin->render->render(Plugin::TEMPLATE_FORM);
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