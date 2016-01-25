<?php
/*
Plugin Name: Solr
Description: Use the Apache Solr search engine.
Version: 0.3
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
		 * sniff incoming requests
		 */
		add_filter('posts_request', array($this, 'disable_search_query'), 10, 2);
		add_filter('template_include', array($this, 'search_template'), 99 );
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
		 */
		if($query->is_main_query() && !empty($_GET['s'])){
			return false;
		}
		return $request;
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
			 * autoload dependencies
			 */
			require_once $this->dir . '/lib/autoload.php';
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

	/**
	 * save latest run to options
	 */
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
	 * @return array
	 */
	public function get_search_args(){
		return $this->search_page->get_search_args();
	}

	/**
	 * @return null|array
	 */
	public function get_search_results(){
		if($this->solr == null) return null;
		return $this->get_solr()->get_search_results();
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
		 * update index with a number of posts because thats faster
		 * @var  \Solarium\QueryType\Update\Result $result
		 */
		try{
			$result = $this->get_solr()->updatePostIndex($index_posts);
		} catch (Solarium\Exception\HTTPException $e) {
			var_dump($e->getMessage());
			/**
			 * on error try every single one and log error
			 */
			for($i = 0; $i < count($index_posts); $i++) {
				$post = $index_posts[$i];
				try{
					$result = $this->get_solr()->updatePostIndex(array($post));
				} catch (Solarium\Exception\HTTPException $e) {
					array_splice($posts, $i, 1);
					$this->posts->set_ignored($post->ID);
					$this->posts->set_error($post->ID);
					var_dump($e);
					var_dump("Cound not index post: ".$post->ID);
				}
			}
	      
	    }
		return $this->_verify_result($result, $index_posts);
		
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
		if($result->getStatus() === 0){
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
	}
	/**
	 * on deactivation
	 */
	public static function on_deactivate(){
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
