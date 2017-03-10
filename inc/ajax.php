<?php

namespace SolrPlugin;


class Ajax {
	
	const KEY_SUGGESTS = "suggests";
	
	/**
	 * AjaxBootstrap constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	function __construct(Plugin $plugin) {
		
		require_once "ajax-endpoint.php";
		
		$this->suggests = new Ajax_Endpoint(self::KEY_SUGGESTS, array($this, "suggests"));
		
	}
	
	/**
	 * add ajax endpoint
	 */
	function add_endpoints(){
		$this->suggests->add_endpoint();
	}
	
	function suggests($param){
		die("suggesting");
	}
	
}