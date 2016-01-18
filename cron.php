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
$solr_plugin->index_posts();