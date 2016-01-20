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
		add_shortcode('solr_search_results', array($this, 'render_search_results') );
		add_shortcode('solr_search_form', array($this,'get_search_form') );
		add_filter('get_search_form', array($this, 'get_search_form') );
		add_action('solr_render_search_results_item', array($this,'render_search_results_item'));
	}

	/**
	 * render search results shortcode
	 */
	function render_search_results() {
		/**
		 * global use of search results
		 */
		$solr_search_args = $this->get_search_args();
		$solr_search_results = $this->plugin->get_solr()->search($solr_search_args);

		include $this->get_template_file('search-results.php');
	}

	/**
	 * render a single item of search results
	 * you can use global post function because we are in context of a loop
	 * @param \Solarium\QueryType\Select\Result\DocumentInterface $document
	 * @return string
	 */
	function render_search_results_item($document){
		include $this->get_template_file("search-results-item.php");
	}

	/**
	 * render search form
	 * @param $form
	 * @return string
	 */
	function get_search_form($form, $solr_search_results = null) {
		$search_args = $this->get_search_args();
		ob_start();
		include $this->get_template_file("search-form.php");
		$form = ob_get_clean();
		return $form;
	}

	/**
	 * Returns the search arguments as an associative array or FALSE if there was no
	 * search.
	 *
	 * @return array
	 */
	function get_search_args() {
		$args = array();
		if (isset($_GET['s'])) {
			$args['s'] = $_GET['s'];
		}

		// sanitize page param
		if (isset($_GET['page_num'])) {
			$args['page'] = (int) $_GET['page_num'];

			if ($args['page'] < 1) {
				$args['page'] = 1;
			}
		} else {
			$args['page'] = 1;
		}

		$facet_args = array();
		foreach ($_GET as $key => $value) {
			if (strpos($key, 'facet-') === 0) {
				$facet_args[substr($key, 6)] = $value === 'on';
			}
		}

		$args['facets'] = $facet_args;

		return $args;
	}

	/**
	 * returns path to theme template if exists
	 * else default template
	 * @param string $file filename of template
	 * @return string
	 */
	private function get_template_file($file){
		if(is_file($this->plugin->theme_path."/".$file)){
			return $this->plugin->theme_path."/".$file;
		}
		return $this->plugin->dir."/templates/$file";
	}

}