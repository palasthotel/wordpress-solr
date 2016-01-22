<?php
/**
 * load core
 */
//define( 'WP_MEMORY_LIMIT','2G' );
define('WP_USE_THEMES', false);
$paths = explode( 'wp-content',__FILE__ );
require_once( $paths[0] . 'wp-load.php' );

//ini_set( 'memory_limit', '-1' );

/**
 * do the cron stuff
 */

$solr_plugin = solr_get_plugin();
$i = 0;
$indexed = 0;
do{
	/**
	 * index posts to solr
	 */
	$results = $solr_plugin->index_posts(200);
	if($results->error === true){
		print "Error while indexing: \n";
		var_dump($results);
		break;
	}
	/**
	 * break if too many rounds
	 */
	$indexed+= count($results->posts);
	print "Indexed: $indexed\n";

	$i++;
	if($i > 999){
		echo "\n --- security break --- \n";
		break;
	} 
} while( true );

$solr_plugin->save_latest_run();