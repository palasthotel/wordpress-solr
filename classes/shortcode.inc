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
		add_shortcode('solr_search_form', array($this,'render_search_form') );
		add_shortcode('solr_search_results', array($this, 'render_search_results') );
		add_filter('get_search_form', array($this, 'render_search_form') );
	}

	/**
	 * render search results shortcode
	 */
	function render_search_results() {
		/**
		 * global use of search results
		 */
		global $search_results;
		$search_results = $this->plugin->get_solr()->search();
		/**
		 * apply filter for custom search results page
		 */
		$custom_file = '';
		apply_filters('solr_search_results_template', $custom_file);
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
	function render_search_form($form) {
		$search_args = phsolr_get_search_args();
		ob_start();
		?>
		<form role="search" method="get" class="search-form" action="<?php echo home_url('/') ?>">
			<div>
				<label> <span class="screen-reader-text">Search for:</span> <input
					  type="search" class="search-field"
					  placeholder="<?php echo __('Search …') ?>"
					  value="<?php echo $search_args['text']; ?>" name="query"
					  title="Search for:" />
				</label> <input type="submit" class="search-submit" value="Search" />
			</div>
		</form>
		<?php
		$form = ob_get_clean();
		return $form;
	}

}