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
	
	const DOMAIN = "solr-plugin";
	
	const HANDLE_JS_API = "solr-js";
	
	/**
	 * plugin actions
	 */
	const ACTION_SEARCH_RESULTS = "solr_search_results";
	const ACTION_SEARCH_RESULTS_ITEM = "solr_search_results_item";
	const ACTION_SEARCH_SPELLCHECK = "solr_search_spellcheck";
	const ACTION_SEARCH_PAGINATION = "solr_search_pagination";
	
	/**
	 * plugin filters
	 */
	const FILTER_SOLARIUM_PATH = "solr_solarium_path";
	const FILTER_SOLR_SELECT = "solr_search_select";
	const FILTER_SOLR_INDEX_FIELDS = "solr_add_fields_%type%";
	const FILTER_SOLR_INDEX_FIELDS_PLACEHOLDER = "%type%";
	const FILTER_SOLR_INDEX_FIELDS_POST = "solr_add_fields_post";
	const FILTER_SOLR_INDEX_FIELDS_COMMENT = "solr_add_fields_comment";
	const FILTER_SOLR_INDEX_IGNORE_POST = "solr_post_ignore";
	
	/**
	 * shortcodes
	 */
	CONST SHORTCODE_FORM = "solr_search_form";
	CONST SHORTCODE_SEARCH_RESULTS = "solr_search_results";
	
	/**
	 * all outputs via themes
	 */
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_SEARCH = "search.php";
	const TEMPLATE_ERROR = "search-error.php";
	const TEMPLATE_FORM = "search-form.php";
	const TEMPLATE_SPELLCHECK = "search-spellcheck.php";
	const TEMPLATE_RESULTS = "search-results.php";
	const TEMPLATE_RESULTS_ITEM = "search-results-item.php";
	const TEMPLATE_PAGINATION = "search-pagination.php";
	
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
	function __construct() {
		/**
		 * base paths
		 */
		$this->dir = plugin_dir_path( __FILE__ );
		$this->url = plugin_dir_url( __FILE__ );
		
		/**
		 * solarium singlelton
		 */
		require_once 'inc/solarium.php';
		
		/**
		 * solar index operations class
		 */
		require_once "inc/solr-index.php";
		$this->solr_index = new SolrIndex( $this );
		
		/**
		 * solar index runner class
		 */
		require_once "inc/index-runner.php";
		$this->index_runner = new IndexRunner( $this );
		
		/**
		 * solar search operations class
		 */
		require_once "inc/solr-search.php";
		$this->solr_search = new SolrSearch( $this );
		
		/**
		 * init config
		 */
		require_once( 'inc/config.php' );
		$this->config = new Config( $this );
		
		/**
		 * request
		 */
		require_once( 'inc/request.php' );
		$this->request = new Request( $this );
		
		/**
		 * render templates
		 */
		require_once( 'inc/render.php' );
		$this->render = new Render( $this );
		
		/**
		 * settings page
		 */
		require( 'inc/settings.php' );
		$this->settings = new Settings( $this );
		
		/**
		 * post modifications and meta flags
		 */
		require( 'inc/posts.php' );
		$this->posts = new Posts( $this );
		
		/**
		 * class for any needed endpoints
		 */
		require_once "inc/ajax.php";
		$this->ajax = new Ajax( $this );
		
		/**
		 * overwrite frontend search
		 */
		require( 'inc/frontend-search.php' );
		$this->frontend_search = new FrontendSearch( $this );
		
		/**
		 * API
		 */
		require( 'inc/api.php' );
		$this->api = new Api( $this );
		
		/**
		 * schedule class
		 */
		require( 'inc/schedule.php' );
		$this->schedule = new Schedule( $this );
		
		/**
		 * search page renderer
		 */
		require( 'inc/frontend-search-page.php' );
		$this->frontend_search_page = new FrontendSearchPage( $this );
		
		/**
		 * activate and deactivate hook
		 */
		register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );
		
	}
	
	/**
	 * solr search is enabled on settings page
	 * @return boolean
	 */
	function is_enabled() {
		return $this->config->get_option( Plugin::OPTION_ENABLED );
	}
	
	/**
	 * on activation
	 */
	function on_activate() {
		$this->ajax->add_endpoints();
		$this->schedule->register();
		flush_rewrite_rules();
	}
	
	/**
	 * on deactivation
	 */
	function on_deactivate() {
		$this->schedule->unregister_all();
		flush_rewrite_rules();
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

