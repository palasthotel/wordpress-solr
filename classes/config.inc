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
	public static $HOST = "host";
	public static $PORT = "port";
	public static $PATH = "path";
	public static $SPELL_CHECK = "spellcheck";
	public static $QUERY_LIMIT = "query_limit";
	public static $SEARCH_PAGE_ID = "search_page_id";
	/**
	 * some other vars
	 */
	private $plugin;
	private $defaults;
	public function __construct(\SolrPlugin $plugin){
		$this->plugin = $plugin;
		$this->defaults = array(
			self::$HOST => '127.0.0.1',
			self::$PORT => '8983',
		  	self::$PATH => '/solr/',
		  	self::$SPELL_CHECK => TRUE,
		  	self::$QUERY_LIMIT => 100,
		);
	}

	/**
	 * get default value for key or FALSE
	 * @param $key
	 * @return mixed
	 */
	public function get_default($key){
		return (!empty($this->defaults[$key]))? $this->defaults[$key]: FALSE;
	}

	/**
	 * get option by config key
	 */
	public function get_option($key){
		return get_option($this->plugin->prefix.$key,$this->get_default($key));
	}

	/**
	 * save option for key
	 * @param $key
	 * @param $value
	 */
	public function set_option($key, $value){
		$this->set_option($this->plugin->prefix.$key, $value);
	}
}