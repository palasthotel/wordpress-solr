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
		$this->plugin = $plugin;
		
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
		
		$solarium = Solarium::instance($this->plugin);
		
		$query = $solarium->createSelect();
		$query->setQuery($param);
//		$query->setFields(array('item_id','ts_title'));
//
		$results = $solarium->execute($query);
		
		$json = array();
		foreach ($results as $document){
			$json[] = $document->ts_title;
		}
		wp_send_json($json);
	}
	
}