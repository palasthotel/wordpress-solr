<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.11.15
 * Time: 14:17
 */

namespace SolrPlugin;


class SearchPage
{
	public $plugin;

	/**
	 * Posts constructor.
	 * @param \SolrPlugin $plugin
	 */
	public function __construct(\SolrPlugin $plugin){
		$this->plugin = $plugin;

		/**
		 * change search form
		 */
		add_filter('get_search_form', array($this, 'get_search_form_html') );

		/**
		 * render search forms
		 */
		add_shortcode('solr_search_form', array($this,'get_search_form_html') );
		add_action('solr_search_form', array($this, 'render_search_form'),10,2);

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
		$solr_search_results = $this->plugin->get_search_results();
		$solr_search_args = $this->get_search_args();
		if($this->plugin->search_error){
			include $this->plugin->get_template_file('search-error.php');
		} else {
			include $this->plugin->get_template_file('search-results.php');
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
		include $this->plugin->get_template_file("search-results-item.php");
	}

	/**
	 * render spellcheck display
	 * @param $solr_search_results
	 */
	function render_search_spellcheck($solr_search_results){
		include $this->plugin->get_template_file("search-spellcheck.php");
	}

	/**
	 * render search form
	 * @param $form
	 * @return string
	 */
	function get_search_form_html($form, $solr_search_results = null) {
		ob_start();
		$this->render_search_form($form, $solr_search_results);
		$form = ob_get_clean();
		return $form;
	}

	/**
	 * render search form
	 * @param $form
	 * @param null|\Solarium\QueryType\Select\Result\Result  $solr_search_results
	 * @return string
	 */
	function render_search_form($form, \Solarium\QueryType\Select\Result\Result $solr_search_results = null) {
		$solr_search_args = $this->get_search_args();
		include $this->plugin->get_template_file("search-form.php");
	}

	/**
	 * Returns the search arguments as an associative array or FALSE if there was no
	 * search.
	 *
	 * @return array
	 */
	function get_search_args() {
		return $this->plugin->get_search_args();
	}

	/**
	 * add body classes depending on search results
	 * @param array $classes
	 * @return array
	 */
	function body_class( array $classes ) {
		$found = ($this->plugin->get_search_results()->getNumFound() > 0);
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
		return $classes;
	}
}