<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 23.11.15
 * Time: 14:17
 */

namespace SolrPlugin;


class Shortcode
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

		/**
		 * apply filter for custom search results page
		 */
		$custom_file = '';
		$custom_file = apply_filters('solr_search_results_template', $custom_file);
		if(is_file($custom_file)){
			include $custom_file;
		} else {
			include $this->plugin->dir."/templates/search-results.php";
		}
	}

	/**
	 * render search form
	 * @param $form
	 * @return string
	 */
	function get_search_form($form) {
		$search_args = $this->get_search_args();
		ob_start();
		?>
		<form role="search" method="get" class="search-form" action="<?php echo home_url('/') ?>">
			<div>
				<label> <span class="screen-reader-text">Search for:</span> <input
					  type="search" class="search-field"
					  placeholder="<?php echo __('Search …') ?>"
					  value="<?php echo $search_args['s']; ?>" name="s"
					  title="Search for:" />
				</label> <input type="submit" class="search-submit" value="Search" />
			</div>
		</form>
		<?php
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

}