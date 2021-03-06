<?php

namespace SolrPlugin;


class Api {
	
	/**
	 * Api constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'init', array( $this, 'register_scripts' ), 0 );
	}
	
	/**
	 * JS API
	 */
	function register_scripts(){
		wp_register_script( Plugin::HANDLE_JS_API, $this->plugin->url . 'js/ajax.js', array( 'jquery' ), 1, TRUE );
		
		wp_localize_script( Plugin::HANDLE_JS_API, 'Solr', array(
			"endpoints" => array(
				"suggest" => home_url($this->plugin->ajax->suggests->getURL()),
				"search" => home_url($this->plugin->ajax->search->getURL()),
			),
		) );
	}
}