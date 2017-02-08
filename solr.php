<?php
/*
Plugin Name: Fast Search powered by Solr
Description: Replaces Wordpress search engine by Solr search engine.
Version: 0.4.0
Author: Palasthotel by Edward Bock, Katharina Rompf
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
	 * @var \SolrPlugin\Schedule
	 */
	public $schedule;

	/**
	 * @var array|null
	 */
	public $search_args;

	/**
	 * @var \Solarium\QueryType\Select\Result\Result|null
	 */
	public $search_results;

	/**
	 * @var boolean
	 */
	public $search_error;
	/**
	 * @var \Solarium\Exception\HttpException $e
	 */
	public $search_error_object;

	/**
	* construct grid plugin
	*/
	function __construct()
	{
		/**
		 * search cache
		 */
		$this->search_error = false;
		$this->search_args = null;
		$this->search_results = null;
		$this->is_disabled_backend = false;

		/**
		* base paths
		*/
		$this->dir = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);

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

		/**
		 * schedule class
		 */
		require('classes/schedule.inc');
		$this->schedule = new \SolrPlugin\Schedule($this);


		/**
		 * if enabled activate template suggestions
		 * and overwrite default search
		 * and do the search if needed
		 */
		if($this->get_config()->get_option(\SolrPlugin\Config::$ENABLED)){
			/**
			 * do the search on init
			 */
			add_action('init', array($this, 'do_search'));
			/**
			 * template paths for solr
			 */
			add_filter('posts_request', array($this, 'disable_search_query'), 10, 2);
			add_filter('template_include', array($this, 'search_template'), 99 );
			add_action('pre_get_posts', array($this, 'backend_search_query'), 99 );
		}
	}

	/**
	 * is search if there is a search query
	 * @return bool
	 */
	function is_search(){
		return isset($args['s']);
	}

	/**
	 * do the search in init action
	 */
	function do_search(){
		if($this->is_search()){
			$this->get_search_results();
		}
	}

	/**
	 * get the search results
	 * @return null|\Solarium\QueryType\Select\Result\Result
	 */
	function get_search_results(){
		if($this->search_results == null
		  && $this->search_error == false){
			try{
				/**
				 * @var \Solarium\QueryType\Select\Result\Result
				 */
				$this->search_results = $this->get_solr()->search($this->get_search_args());
				$this->search_error =  false;
			} catch (\Solarium\Exception\HttpException $e){
				$this->search_error = true;
				$this->search_error_object = $e;
			}
		}
		return $this->search_results;
	}

	/**
	 * Returns the search arguments as an associative array or FALSE if there was no
	 * search.
	 *
	 * @return array
	 */
	function get_search_args() {
		if($this->search_args == null){
			$args = array();
			if (isset($_GET['s'])) {
				$args['s'] = $_GET['s'];
			}

			/**
			 * wild guess paged variable
			 */
			if (isset($_GET['page'])) {
				$args['page'] = (int) $_GET['page'];
			} else if(isset($args["paged"])) {
				$args['page'] = (int) $_GET['paged'];
			} else {
				global $paged;
				if(isset($paged) && is_int($paged)){
					$args['page'] = $paged;
				} else {
					$args['page'] = 1;
				}
			}
			if ($args['page'] < 1) {
				$args['page'] = 1;
			}

			/**
			 * get facets
			 */
			$facet_args = array();
			foreach ($_GET as $key => $value) {
				if (strpos($key, 'facet-') === 0) {
					$facet_args[substr($key, 6)] = $value === 'on';
				}
			}

			$args['facets'] = $facet_args;

			/**
			 * filter args
			 */

			$filter_args = array();

			$this->search_args = $args;
		}
		return $this->search_args;
	}

	/**
	 * disable_search_query
	 * @param array $request
	 * @param $query WP_Query
	 * @return array|bool
	 */
	function disable_search_query($request, $query){
		/**
		 * if it is the main query and there is a search param
		 * important: empty search string is valid too
		 */
		if($query->is_main_query() &&
		  isset($_GET['s']) &&
		  !is_admin() ){
			return false;
		}
		return $request;
	}

	function backend_search_query($query){
		global $pagenow;
		if ( is_admin() && $query->is_main_query() ) {
			if ($query->is_search) {

				if($pagenow == 'edit.php'){
					//echo '<pre>' . var_dump($query) . '</pre>';
					// echo("hallo suche");
					$this->is_disabled_backend = true;

					echo ('hallo backendsuche');
					//var_dump($query);
					//$this->search_args['s'] = '*:*';
					//$query->setQuery('*:*');
				//	$this->search_args['filter']['bs_status'] = true;
				//	$this->search_args['filter']['ss_type'] = 'post';
					$my_backend_search = $this->get_search_results();
					var_dump($my_backend_search);
					die();

				}
			}
		}

	}


	/**
	 * Add a new template when solr search is triggered
	 * @param $template
	 * @return string
	 */
	function search_template( $template ) {
		/**
		 * guess that solr search is triggered when GET s isset
		 */
		if ( isset($_GET['s']) && !empty($_GET['s']) ) {
			/**
			 * return theme search template if exists
			 */
			$search_template = locate_template( array( 'solr/search.php' ) );
			if ( '' != $search_template ) {
				return $search_template ;
			}
			/**
			 * return solr plugin search template
			 */
			return $this->dir.'/templates/search.php';
		}
		/**
		 * return wordpress template
		 */
		return $template;
	}


	/**
	 * @return \SolrPlugin\Solr
	 */
	public function get_solr(){
		if ($this->solr == NULL) {
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
			 * get config
			 */
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
			  'timeout' => $config->get_option($config::$TIMEOUT),
			);

			/**
			 * solarium class
			 */
			$solarium_path = $this->dir . '/lib/autoload.php';
			$solarium_path = apply_filters('solr_solarium_path',$solarium_path);
			require_once $solarium_path;

			/**
			 * construct solarium
			 */
			$this->solarium = new Solarium\Client(array('endpoint' => array( $endpoint )));

		}
		return $this->solarium;
	}

	/**
	 * save latest run to options
	 */
	public function save_latest_run(){
		update_option( $this->prefix.'post_index_run', date("Y-m-d h:i:s"));
	}

	/**
	 * get last run
	 * @return string
	 */
	public function get_latest_run(){
		return get_option( $this->prefix.'post_index_run');
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
		} catch (Solarium\Exception\HTTPException $e) {
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
				/* @var $post WP_Post */
				$this->posts->set_indexed($post->ID);
			}
			return (object) array("posts" => $posts, "result" => $result, "error" => false);
		}
		return (object)array("error" => true);
	}

	/**
	 * on activation
	 */
	public static function on_activate(){
		$self = solr_get_plugin();
		/**
		 * first unregister to prevent double events
		 * than register schedules
		 */
		$self->schedule->register();
	}
	/**
	 * on deactivation
	 */
	public static function on_deactivate(){
		$self = solr_get_plugin();
		$self->schedule->unregister_all();
	}

	/**
	 * returns path to theme template if exists
	 * else plugin template
	 * @param string $file filename of template
	 * @return string
	 */
	public function get_template_file($file){
		$solr_template = locate_template( array( 'solr/'.$file ) );
		if ( '' != $solr_template ) {
			return $solr_template;
		}
		return $this->dir."/templates/$file";
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
