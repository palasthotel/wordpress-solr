<?php
/**
 * get solr plugin everywhere
 * @return SolrPlugin\Plugin
 */
function solr_get_plugin(){
	return SolrPlugin\Plugin::instance();
}

/**
 *
 * @param $s string
 * @return \Solarium\QueryType\Select\Result\Result $results
 */
function solr_search($s){

	$solarium = SolrPlugin\Solarium::instance( solr_get_plugin() );

	$query = $solarium->createSelect();
	$query->setQuery( $s );

	return $solarium->execute( $query );
}