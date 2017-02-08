<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 18.01.16
 * Time: 17:28
 */

namespace SolrPlugin;


class Config {

	/**
	 * config keys
	 */
	public static $ENABLED = "enabled";
	public static $HOST = "host";
	public static $PORT = "port";
	public static $PATH = "path";
	public static $CORE = "core";
	public static $USERNAME = "username";
	public static $PW = "password";
	public static $DOCUMENTS_PER_CALL = "documents_per_call";
	public static $TIMEOUT = "timeout";

	public static $SPELL_CHECK = "spellcheck";
	public static $QUERY_LIMIT = "query_limit";

	/**
	 * some other vars
	 */
	private $plugin;
	private $defaults;

	public function __construct(\SolrPlugin $plugin) {
		$this->plugin = $plugin;
		$this->defaults = array(
			self::$ENABLED => false,
		  self::$HOST => '127.0.0.1',
		  self::$PORT => '8983',
		  self::$PATH => '/solr/',
		  self::$DOCUMENTS_PER_CALL => 30,
		  self::$TIMEOUT => 10,
		);
	}

	/**
	 * get default value for key or FALSE
	 * @param $key
	 * @return mixed
	 */
	public function get_default($key) {
		return (!empty($this->defaults[$key])) ? $this->defaults[$key] : FALSE;
	}

	/**
	 * get option by config key
	 */
	public function get_option($key) {
		return get_option($this->plugin->prefix . $key, $this->get_default($key));
	}

	/**
	 * save option for key
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function set_option($key, $value) {
		return update_option($this->plugin->prefix . $key, $value);
	}

	/**
	 *    get the solr configuration
	 * @return mixed|void
	 */
	public function get_solr_config() {
		/**
		 * default configuration
		 */
		$config = array(
			'empty_query' => '*:*',
			// 'AND' or 'OR'
		  'default_query_operator' => 'AND',
			// enable spellchecker
		  'spellcheck' => TRUE,
			// maximum number of search results
		  'query_limit' => 15,
			// list of fields to search and their corresponding weight
		  'search_fields' => array(
			'ts_title' => 4.0,
			'ds_published' => 0.5,
			'ts_author' => 1.0,
			'content' => 3.0,
		    'sm_category'=> 2.0,
		  ),
			// list of additional boost functions
		  'boost_functions' => array(
			'recip(abs(ms(NOW,ds_published)),3.16e-11,1,1)^5'
		  ),
			// list of fields to return
		  'result_fields' => array(
			'id',
			'ts_title',
			'ds_published',
			'ts_author',
			'ss_type',
			'content',
			'url',
			  'sm_category',
		  ),
			// list of fields that are intended to be highlighted
		  'highlight_fields' => array(
			'ts_title',
			'content',
			  'ts_author',
			  'sm_category'

		  ),
	// list of fields that are intended to be highlighted
			'filter_fields' => array (
				'ss_type' => array(
					'field' => 'ss_type',
					'title' => 'Type',
				),
				'ss_status' => array(
					'field' => 'bs_status',
					'title' => 'status'
			),
			),
			// facet definitions
		  'facets' => array(
			'ss_type' => array(
			  'field' => 'ss_type',
			  'title' => 'Type'
			),
			'ds_published' => array(
				  'field' => 'ds_published',
				  'title' => 'Date'
			  ),
			'sm_category' => array(
				'field' => 'sm_category',
				'title' => 'Category'
			),
		  ),
		);

		/**
		 * apply filter for every config key
		 */
		foreach($config as $key => $value){
			$config[$key] = apply_filters('solr_config_'.$key, $value);
		}
		/**
		 * apply filter for complete config and return it
		 */
		return apply_filters('solr_config', $config);
	}
}