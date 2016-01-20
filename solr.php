<?php
/*
Plugin Name: Solr
Description: Use the Apache Solr search engine.
Version: 0.1.1
Author: Palasthotel by Edward Bock
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
	public $theme_path;
	public $prefix;

	/**
	 * @var \SolrPlugin\Solr
	 */
	private $solr;
	/**
	 * @var Solarium\Client
	 */
	private $solarium;
	/**
	 * @var \SolrPlugin\Config
	 */
	private $config;
	/**
	 * @var \SolrPlugin\Settings
	 */
	public $settings;
	/**
	 * @var \SolrPlugin\Posts
	 */
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
		/**
		 * solr templates folder in theme
		 */
		$theme = wp_get_theme();
		$theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();
		$this->theme_path = "$theme_dir/solr/";

		/**
		 * database prefix
		 */
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

		/**
		 * searchpage class
		 */
		require('classes/search-page.inc');
		$this->search_page = new \SolrPlugin\SearchPage($this);

	}

	/**
	 * @return \SolrPlugin\Solr
	 */
	public function get_solr(){
		if ($this->solr === NULL) {
			require_once dirname(__FILE__) . '/classes/solr.php';
			$this->solr = new SolrPlugin\Solr($this);
		}
		return $this->solr;
	}

	/**
	 * get the solarum client
	 * @return \Solarium\Client
	 */
	public function get_solarium(){
		if($this->solarium === null){
			/**
			 * autoload dependencies
			 */
			require_once $this->dir . '/vendor/autoload.php';
			$config = $this->get_config();
			/**
			 * init solarium configuration
			 */
			$endpoint = array(
			  'host' => $config->get_option($config::$HOST),
			  'port' => $config->get_option($config::$PORT),
			  'path' => $config->get_option($config::$PATH),
			  'core' => $config->get_option($config::$CORE),
			  'username' => $config->get_option($config::$USERNAME),
			  'password' => $config->get_option($config::$PW),
			);
			/**
			 * construct solarium
			 */
			$this->solarium = new Solarium\Client(array('endpoint' => array( $endpoint )));
		}
		return $this->solarium;
	}

	public function save_latest_run(){
		update_option( $this->prefix.'post_index_run', date("Y-m-d h:i:s"));
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
			require($this->dir.'/classes/config.inc');
			$this->config = new \SolrPlugin\Config($this);
		}
		return $this->config;
	}

	/**
	 * update index by number of posts
	 * @param int $number number of posts to be updated
	 * @return object
	 */
	public function index_posts($number = 100){
		/**
		 * get modified
		 */
		$posts = $this->posts->getModified($number);
		/**
		 * update index
		 */
		$result = $this->get_solr()->updatePostIndex($posts);
		/**
		 * set index
		 */
		foreach ($posts as $counter => $post) {
			/* @var $post WP_Post */
			print "<p>".$post->post_title."<p>";
			/**
			 * set indexed
			 */
			$this->posts->set_indexed($post->ID);
		}
		return (object) array("posts" => $posts, "result" => $result);
	}

	/**
	 * on activation
	 */
	public static function on_activate(){
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
 * activate and deactivate hook
 */
register_activation_hook(__FILE__, array('SolrPlugin','on_activate') );
register_deactivation_hook(__FILE__, array('SolrPlugin','on_deactivate') );
