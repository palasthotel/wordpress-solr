<?php

namespace SolrPlugin;


class Config {
	
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
	 * get option by config key
	 */
	public function get_option( $key ) {
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
			// list of fields to search and their corresponding weight
			'search_fields' => array(
				'default_search_field' => 2.0,
				'ts_title'             => 4.0,
				'ds_published'         => 0.5,
				'sm_author'            => 1.0,
				'content'              => 3.0,
				'sm_category'          => 2.0,
				'sm_tag'               => 2.0,
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