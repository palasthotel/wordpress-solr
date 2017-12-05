<?php

namespace SolrPlugin;


class Config {

	private $json_config = null;

	/**
	 * Config constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin   = $plugin;
		$this->defaults = array(
			Plugin::OPTION_ENABLED            => FALSE,
			Plugin::OPTION_HOST               => '127.0.0.1',
			Plugin::OPTION_PORT               => '8983',
			Plugin::OPTION_PATH               => '/solr/',
			Plugin::OPTION_DOCUMENTS_PER_CALL => 30,
			Plugin::OPTION_TIMEOUT            => 10,
		);
	}

	/**
	 * get default value for key or FALSE
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get_default( $key ) {
		return ( ! empty( $this->defaults[ $key ] ) ) ? $this->defaults[ $key ] : FALSE;
	}

	/**
	 * try to load config from json
	 * @return bool|object
	 */
	private function get_file_config(){
		if($this->json_config == null && is_file($this->plugin->dir."/config.json")){
			$contents = file_get_contents($this->plugin->dir."/config.json");
			$this->json_config = json_decode($contents, true);
		}
		if(!is_array($this->json_config)) $this->json_config = false;
		return $this->json_config;
	}

	/**
	 * check if there are configurations from file
	 * @return bool
	 */
	public function has_file_options(){
		return ($this->get_file_config() !== false && count( $this->get_file_config() ) > 0);
	}

	/**
	 * check if option is file option
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function is_file_option($key){
		return ($this->get_file_config() !== false && isset($this->get_file_config()[$key]));
	}
	
	/**
	 * get option by config key
	 */
	public function get_option( $key ) {

		if( $this->get_file_config() !== false && isset($this->get_file_config()[$key])){
			return $this->get_file_config()[$key];
		}

		return get_option( $key, $this->get_default( $key ) );
	}
	
	/**
	 * save option for key
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function set_option( $key, $value ) {
		return update_option( $key, $value );
	}
	
	/**
	 *    get the solr configuration
	 * @return array
	 */
	public function get_solr_config() {
		/**
		 * default configuration
		 */
		$config = array(
			'empty_query'            => '*:*',
			// 'AND' or 'OR'
			'default_query_operator' => 'AND',
			// enable spellchecker
			'spellcheck'             => TRUE,
			// maximum number of search results
			'query_limit'            => get_site_option('posts_per_page', 15),
			// list of additional boost functions
			'boost_functions'        => array(
				// magic...
				'recip(abs(ms(NOW,ds_published)),3.16e-11,1,1)^5',
			),
			'boost_query'            => array(
				// magic...
				' ss_type:"page"^32',
				'OR ss_type:"post"^8',
			),

			// list of fields to search and their corresponding weight
			'search_fields'          => array(
				// please only boost full text fields
				'default_search_field' => 2.0,
				'ts_title'             => 4.0,
				'ds_published'         => 0.5,
				'content'              => 3.0,
				'tm_author'            => 1.0,
				'tm_category'          => 2.0,
				'tm_tag'               => 2.0,
				'ts_type'              => 1.0,
			),
			// list of fields to return
			'result_fields' => array(
				'id',
				'default_search_field',
				'item_id',
				'ts_title',
				'ds_published',
				'sm_author',
				'ss_type',
				'content',
				'url',
				'sm_category',
			),
			// list of fields that are intended to be highlighted
			'highlight_fields'       => array(
				'ts_title',
				'content',
				'tm_author',
				'sm_category',
			),
			
			
			// list of fields that are intended to be highlighted
			'filter_fields'          => array(
				'ss_type'   => array(
					'field' => 'ss_type',
					'title' => 'Type',
				),
				'ss_status' => array(
					'field' => 'bs_status',
					'title' => 'status',
				),
			),
			// facet definitions
			'facets'                 => array(
				'ss_type'      => array(
					'field' => 'ss_type',
					'title' => 'Type',
				),
				'ds_published' => array(
					'field' => 'ds_published',
					'title' => 'Date',
				),
				'sm_category'  => array(
					'field' => 'sm_category',
					'title' => 'Category',
				),
				'sm_author'    => array(
					'field' => 'sm_author',
					'title' => 'Author',
				),
			),
		);
		
		/**
		 * apply filter for every config key
		 *
		 * @return array
		 */
		foreach ( $config as $key => $value ) {
			$config[ $key ] = apply_filters( 'solr_config_' . $key, $value );
		}
		
		/**
		 * apply filter for complete config and return it
		 */
		$config = apply_filters( 'solr_config', $config );
		
		return $config;
	}
}