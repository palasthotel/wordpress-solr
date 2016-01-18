<?php
/*
Plugin Name: Solr
Description: Use the Apache Solr search engine in WordPress.
Version: 0.1.0
Author: Palasthotel (Edward Bock, Daniel Blume)
URI: http://palasthotel.de/
Plugin URI: https://github.com/palasthotel/wordpress-solr
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

class SolrPlugin
{
	/**
	 * simple vars
	 */
	public $dir;
	public $url;
	public $prefix;

	/**
	 * subclasses
	 */
	public $solr;
	public $config;
	public $settings;
	public $posts;

	/**
	* construct grid plugin
	*/
	function __construct()
	{
		/**
		* base paths
		*/
		$this->dir = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);
		$this->prefix = "solr_";

		/**
		* settings page
		*/
		require('classes/settings.inc');
		$this->settings = new \SolrPlugin\Settings($this);

		/**
		 * posts class
		 */
		require('classes/posts.inc');
		$this->posts = new \SolrPlugin\Posts($this);

	}

	/**
	 * @return \SolrPlugin\PhSolr
	 */
	public function get_solr(){
		if ($this->solr === NULL) {
			// autoload dependencies
			require_once dirname(__FILE__) . '/vendor/autoload.php';

			// load configuration
			if (file_exists(dirname(__FILE__) . '/config.php')) {
				require_once dirname(__FILE__) . '/config.php';
			} else {
				die('Configuration file missing. Please add authentication information to' .
					' "config.sample.php" and rename it to "config.php".');
			}

			// instantiate PhSolr
			require_once dirname(__FILE__) . '/classes/solr.php';
//			$this->solr = new SolrPlugin\Solr(new Solarium\Client($solarium_config), $phsolr_config,
//				phsolr_get_search_args());
		}

		return $this->solr;
	}

	public function save_latest_run(){
		update_option('phsolr_post_index_run', date("Y-m-d h:i:s"));
	}

	/**
	 * get config object
	 * @return \SolrPlugin\Config
	 */
	public function get_config(){
		if($this->config === null){
			/**
			 * init config
			 */
			require('classes/config.inc');
			$this->config = new \SolrPlugin\Config($this);
		}
		return $this->config;
	}

	/**
	 * on activation
	 */
	public static function on_activate(){
		// TODO: create default search results page
	}
	/**
	 * on deactivation
	 */
	public static function on_deactivate(){
		// TODO: delete default search results page?
	}
}

/**
 * make it global
 */
global $solr_plugin;
$solr_plugin = new SolrPlugin();

/**
 * get solr plugin everywhere
 * @return \SolrPlugin
 */
function solr_get_plugin(){
	global $solr_plugin;
	return $solr_plugin;
}

/**
 * LEGACY
 * Returns an instance of Solr.
 * @return Solr
 */
function phsolr_get_instance() {
	return solr_get_plugin()->get_solr();
}


/**
 * create search page on activate if doesnt exist
 */

function phsolr_create_search_result_page() {
  // search for a page titled 'Search Results'
  $page = get_page_by_title('Search Results', 'OBJECT', 'page');

  if (!$page) {
    // if it doesn't exist, create a new page
    $page = array(
      'post_type' => 'page',
      'post_name' => 'search',
      'post_title' => 'Search Results',
      'post_content' => '[phsolr_search_results]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_name' => 'Search Results',
      'comment_status' => 'closed'
    );

    $page_id = wp_insert_post($page);
  } else if ($page->post_status != 'publish') {
    // if the page is unpublished, publish it
    $page->post_status = 'publish';

    wp_update_post($page);
  } else {
    // otherwise remember its ID
    $page_id = $page->ID;
  }
}

/**
 * activate and deactivate hook
 */
register_activation_hook(__FILE__, array('SolrPlugin','on_activate') );
register_deactivation_hook(__FILE__, array('SolrPlugin','on_deactivate') );


// ---------------- refactor ------------ .........

function phsolr_update_post_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->updatePostIndex();
}
add_action('phsolr_update_post_index_event', 'phsolr_update_post_index');

function phsolr_update_comment_index() {
  // do nothing for now
  // $phsolr = phsolr_get_instance();
  //
  // $phsolr->updateCommentIndex();
}
add_action('phsolr_update_comment_index_event', 'phsolr_update_comment_index');

function phsolr_optimize_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->optimizeIndex();
}
add_action('phsolr_optimize_index_event', 'phsolr_optimize_index');



function phsolr_get_search_page_id() {
  $page = get_page_by_title('Search Results');
  return $page->ID;
}



