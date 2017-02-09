<?php
/*
Plugin Name: Fast Search powered by Solr
Description: Replaces Wordpress search engine by Solr search engine.
Version: 0.4.0
Author: Palasthotel by Edward Bock, Katharina Rompf
URI: http://palasthotel.de/
Plugin URI: https://github.com/palasthotel/wordpress-solr
*/

namespace SolrPlugin;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

class Plugin {
	
	/**
	 * all outputs via themes
	 */
	const THEME_FOLDER = "solr";
	const TEMPLATE_SEARCH = "search.php";
	const TEMPLATE_ERROR = "search-error.php";
	const TEMPLATE_FORM = "search-form.php";
	const TEMPLATE_SPELLCHECK = "search-spellcheck.php";
	const TEMPLATE_RESULTS = "search-results.php";
	const TEMPLATE_RESULTS_ITEM = "search-results-item.php";
	
	/**
	 * options
	 */
	const OPTION_ENABLED = "solr_enabled";
	const OPTION_HOST = "solr_host";
	const OPTION_PORT = "solr_port";
	const OPTION_PATH = "solr_path";
	const OPTION_CORE = "solr_core";
	const OPTION_USERNAME = "solr_username";
	const OPTION_PW = "solr_password";
	const OPTION_DOCUMENTS_PER_CALL = "solr_documents_per_call";
	const OPTION_TIMEOUT = "solr_timeout";
	const OPTION_LAST_INDEX_RUN = 'solr_post_index_run';
	
	const SPELLCHECK = "solr_spellcheck";
	const QUERY_LIMIT = "solr_query_limit";
	
	/**
	 * meta fields
	 */
	const POST_META_INDEXED = "solr_indexed";
	const POST_META_IGNORED = "solr_ignored";
	const POST_META_ERROR = "solr_error";
	
	/**
	 * @var Config $config
	 */
	public $config;
	
	/**
	 * @var Solr $solr
	 */
	private $solr;
	
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
		 * init config
		 */
		require_once('classes/config.inc');
		$this->config = new Config($this);
		
		/**
		 * request
		 */
		require_once ('classes/request.php');
		$this->request = new Request($this);
		
		/**
		 * render templates
		 */
		require_once ('classes/render.php');
		$this->render = new Render($this);
		
		/**
		* settings page
		*/
		require('classes/settings.inc');
		$this->settings = new Settings($this);

		/**
		 * posts class
		 */
		require('classes/posts.inc');
		$this->posts = new Posts($this);

		/**
		 * search page
		 */
		require('classes/search-page.inc');
		$this->search_page = new SearchPage($this);
		
		/**
		 * schedule class
		 */
		require('classes/suggester.php');
		$this->suggester = new Suggester($this);

		/**
		 * schedule class
		 */
		require('classes/schedule.inc');
		$this->schedule = new Schedule($this);
		
		/**
		 * init
		 */
		require('classes/init.php');
		$this->init = new Init($this);
		
		/**
		 * activate and deactivate hook
		 */
		register_activation_hook(__FILE__, array($this,'on_activate') );
		register_deactivation_hook(__FILE__, array($this,'on_deactivate') );
		
	}
	
	/**
	 * solr search is enabled on settings page
	 * @return boolean
	 */
	function is_enabled(){
		return $this->config->get_option(Plugin::OPTION_ENABLED);
	}

	/**
	 * Get Solr object on demand
	 * @return Solr
	 */
	public function get_solr(){
		if ($this->solr == NULL) {
			require_once 'classes/solr.php';
			$this->solr = new Solr($this);
		}
		return $this->solr;
	}
	
	/**
	 * save latest run to options
	 */
	public function save_latest_run(){
		update_option( self::OPTION_LAST_INDEX_RUN, date("Y-m-d h:i:s"));
	}

	/**
	 * get last run
	 * @return string
	 */
	public function get_latest_run(){
		return get_option( self::OPTION_LAST_INDEX_RUN);
	}

	/**
	 * update index by number of posts
	 * @param int $number number of posts to be updated
	 * @return boolean|object
	 */
	public function index_posts($number = 100){
		/**
		 * get modified
		 */
		$modified_posts = $this->posts->getModified($number);

		/**
		 * if there are no modified return
		 */
		if(count($modified_posts) < 1){
			return (object) array("posts" => $modified_posts, "error" => false);
		}

		/**
		 * check if goes through ignore filter
		 */
		$index_posts = array();
		for($i = 0; $i < count($modified_posts); $i++){
			$post = $modified_posts[$i];
			// function in config
			$post_ignored = false;
			$post_ignored = apply_filters('solr_post_ignore',$post_ignored,$post);
			/**
			 * if not ignored ad to
			 */
			if (!$post_ignored)
			{
				$index_posts[] = $post;
			} else {
				$this->posts->set_ignored($post->ID);
			}
		}

		/**
		 * if no posts left after filter rerun method
		 */
		if(count($index_posts) < 1){
			return $this->index_posts($number);
		}

		/**
		 * update index with a number of posts because that's faster
		 * @var  \Solarium\QueryType\Update\Result $result
		 */
		try{
			$result = $this->get_solr()->updatePostIndex($index_posts);
		} catch (\Solarium\Exception\HTTPException $e) {
			/**
			 * on error try every single one and log error
			 */
			for($i = 0; $i < count($index_posts); $i++) {
				$post = $index_posts[$i];
				$this->posts->set_error($post->ID);
			}
	    }

		$verify = $this->_verify_result($result, $index_posts);
		if(!$verify->error){
			$this->save_latest_run();
		}
		return $verify;
		
	}

	/**
	 * return result of indexing
	 * @param  \Solarium\QueryType\Update\Result $result
	 * @param  array $posts
	 * @return object $result
	 */
	private function _verify_result($result, $posts){
		/**
		 * Success response is 0
		 * http://solarium.readthedocs.org/en/stable/queries/update-query/the-result-of-an-update-query/
		 */
		if(is_object($result) && !empty($result) && $result->getStatus() === 0){
			foreach ($posts as $counter => $post) {
				/* @var $post \WP_Post */
				$this->posts->set_indexed($post->ID);
			}
			return (object) array("posts" => $posts, "result" => $result, "error" => false);
		}
		return (object)array("error" => true);
	}

	/**
	 * on activation
	 */
	function on_activate(){
		$this->schedule->register();
	}
	/**
	 * on deactivation
	 */
	function on_deactivate(){
		$this->schedule->unregister_all();
	}
}

/**
 * make it global
 */
global $solr_plugin;
$solr_plugin = new Plugin();

/**
 * all public functions
 */
require_once "public-functions.php";

