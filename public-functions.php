<?php
/**
 * get solr plugin everywhere
 * @return SolrPlugin\Plugin
 */
function solr_get_plugin(){
	return SolrPlugin\Plugin::instance();
}