<?php
/**
 * calculate pages
 */
$plugin = solr_get_plugin();

$solr_search_args = $plugin->request->get_search_args();

$solr_search_results = $plugin->request->get_search_results();


if($solr_search_results != null){
	// TODO: per page ist falsch, weil die letzte seite ärger macht
	$per_page = count($solr_search_results);
	$pages = 1;
	if($per_page > 0) $pages = ceil($solr_search_results->getNumFound()/$per_page);
	
	/**
	 * render pagination
	 */
	echo paginate_links( array(
		'base'               => '%_%',
		'format'             => '?paged=%#%',
		'total'              => $pages,
		'current'            => $solr_search_args['paged'],
		'show_all'           => False,
		'end_size'           => 1,
		'mid_size'           => 2,
		'prev_next'          => True,
		'prev_text'          => __('« Previous'),
		'next_text'          => __('Next »'),
		'type'               => 'list',
		'add_args'           => False,
		'add_fragment'       => '',
		'before_page_number' => '',
		'after_page_number'  => ''
	) );
}