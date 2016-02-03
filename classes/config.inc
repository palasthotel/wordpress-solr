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
			// 'AND' or 'OR'
		  'default_query_operator' => 'AND',
			// enable spellchecker
		  'spellcheck' => TRUE,
			// maximum number of search results
		  'query_limit' => 15,
			// list of fields to search and their corresponding weight
		  'search_fields' => array(
			'title' => 4.0,
			'date' => 0.5,
			'author' => 0.5,
			'content' => 1.0
		  ),
			// list of additional boost functions
		  'boost_functions' => array(
			'recip(abs(ms(NOW,date)),3.16e-11,1,1)^5'
		  ),
			// list of fields to return
		  'result_fields' => array(
			'id',
			'title',
			'date',
			'author',
			'type',
			'content',
			'url',
		  ),
			// list of fields that are intended to be highlighted
		  'highlight_fields' => array(
			'title',
			'content'
		  ),
			// facet definitions
		  'facets' => array(
			'type' => array(
			  'field' => 'type',
			  'title' => 'Type'
			),
			'date' => array(
			  'field' => 'date',
			  'title' => 'Date'
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