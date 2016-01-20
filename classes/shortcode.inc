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

	function render_search_results() {
		$search_page_id = phsolr_get_search_page_id();
		$solr = $this->plugin->get_solr();
		$search_results = $solr->search();
		$solr->showResults($search_page_id, $search_results);

	}

	function render_search_form($form) {
		$search_args = phsolr_get_search_args();
		$search_page_id = phsolr_get_search_page_id();
		ob_start();
		?>
		<form role="search" method="get" class="search-form"
			  action="<?php echo home_url('/') ?>">
			<input type="hidden" name="page_id" value="<?php echo $search_page_id; ?>" />
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