<?php

namespace SolrPlugin;

use Solarium\QueryType\Select\Query\Query;

class Suggester {
	/**
	 * Suggester constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'wp_ajax_solr_suggest', array($this, 'suggest') );
		add_action( 'wp_ajax_no_priv_solr_suggest', array($this, 'suggest') );
	}
	
	function suggest(){
		$solarium = $this->plugin->get_solr()->get_solarium();
		
		$query = $solarium->createSelect();
		$query->setQuery($_GET["s"]);
//		$query->setFields(array('item_id','ts_title'));
//
		$results = $solarium->execute($query);
		
		foreach ($results as $document){
			echo $document->ts_title."<br>";
		}
		
		die("suggesting");
	}
	
	
}