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
		add_action(Plugin::ACTION_AJAX_SUGGEST_RENDER, array($this, "suggest_render"), 99, 1);
		
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

		do_action(Plugin::ACTION_AJAX_SUGGEST_RENDER, $results, $param);
		
	}
	
	/**
	 * @param \Solarium\Core\Query\Result\ $results
	 */
	function suggest_render($results){
		$json = array();
		foreach ($results as $document){
			$json[] = array(
				"title " => $document->ts_title,
				"id" => $document->item_id,
			);
		}
		wp_send_json($json);
	}
	
}