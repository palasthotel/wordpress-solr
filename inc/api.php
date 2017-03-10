<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 10.03.17
 * Time: 13:20
 */

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
				"suggests" => home_url($this->plugin->ajax->suggests->getURL()),
			),
		) );
		
		wp_enqueue_script(Plugin::HANDLE_JS_API);
	}
}