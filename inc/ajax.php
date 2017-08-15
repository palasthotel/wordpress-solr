<?php

namespace SolrPlugin;

class Ajax {
	
	const KEY_SUGGESTS = "suggests";
	const KEY_SEARCH = "search";
	
	/**
	 * AjaxBootstrap constructor.
	 *
	 * @param \SolrPlugin\Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		
		require_once "ajax-endpoint.php";
		
		$this->suggests = new Ajax_Endpoint( self::KEY_SUGGESTS, array( $this, "suggests_handler" ) );
		add_action( Plugin::ACTION_AJAX_SUGGEST_RENDER, array( $this, "suggest_render" ), 99, 2 );
		add_filter(Plugin::FILTER_SOLR_AJAX_SUGGEST_QUERY, array($this, "suggest_query"),10,2);

		$this->search = new Ajax_Endpoint( self::KEY_SEARCH, array( $this, "search_handler" ) );
		add_action( Plugin::ACTION_AJAX_SEARCH_RENDER, array( $this, "search_render" ), 99, 2 );
	}
	
	/**
	 * add ajax endpoint
	 */
	function add_endpoints() {
		$this->suggests->add_endpoint();
		$this->search->add_endpoint();
	}

	/**
	 * @param \Solarium\QueryType\Suggester\Query $query
	 * @param String $param
	 *
	 * @return \Solarium\QueryType\Suggester\Query
	 */
	function suggest_query($query, $param){
		$query->setQuery( urldecode( $param ) );
		$query->setOnlyMorePopular(true);
		return $query;
	}
	
	/**
	 * @param $param
	 */
	function suggests_handler( $param ) {
		
		$solarium = Solarium::instance( $this->plugin );
		
		$query = $solarium->createSuggester();
		$query = apply_filters(Plugin::FILTER_SOLR_AJAX_SUGGEST_QUERY, $query, $param);
		
		/**
		 *
		 * @var \Solarium\QueryType\Suggester\Result\Result $results
		 */
		$results = $solarium->suggester( $query );
		
		do_action( Plugin::ACTION_AJAX_SUGGEST_RENDER, $results, $param );
		
	}
	
	/**
	 * @param \Solarium\QueryType\Suggester\Result\Result $results
	 * @param String $param
	 */
	function suggest_render( $results, $param ) {
		
		$collations = $results->getCollation();
		
		$terms = array();
		
		$suggest = array();
		if ( is_array( $collations ) ) {
			foreach ( $collations as $collation ) {
				// replace magic () of solr
				$col       = str_replace( "(", "", $collation );
				$col       = str_replace( ")", "", $col );
				$suggest[] = $col;
			}
		}
		
		foreach ( $results as $term => $termResults ) {
			
			$items = array();
			foreach ( $termResults as $termResult ) {
			    if(!in_array($termResult, $suggest)) {
                    $suggest[] = $termResult;
                }
                $items[]   = $termResult;
			}
			$terms[ $term ] = $items;
		}
		
		wp_send_json( array(
			"collation" => $collations,
			"terms"     => $terms,
			"suggest"   => $suggest,
		) );
		exit;
	}
	
	/**
	 * @param $param
	 */
	function search_handler( $param ) {

		$args = array();
		$args[SearchFields::PARAM_QUERY] = $param;
		$results = $this->plugin->solr_search->execute($args);
		
		do_action( Plugin::ACTION_AJAX_SEARCH_RENDER, $results, $param );
		
	}
	
	/**
	 * @param \Solarium\QueryType\Select\Result\Result $results
	 * @param $param
	 */
	function search_render( $results, $param ) {

		$json = array();
		foreach ($results as $document){

			// TODO: add all indexed fields

			$json[] = array(
				"title " => $document->ts_title,
				"id" => $document->item_id,
			);
		}
		wp_send_json($json);
		exit;
	}
	
	
}