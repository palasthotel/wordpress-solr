<?php
/**
 * load core
 */
define( 'WP_MEMORY_LIMIT','2G' );
define('WP_USE_THEMES', false);
$paths = explode( 'wp-content',__FILE__ );
require_once( $paths[0] . 'wp-load.php' );
ini_set( 'memory_limit', '-1' );

/**
 * do the cron stuff
 */

$solr_plugin = solr_get_plugin();
$posts = $solr_plugin->posts->getModifiedPosts(100);


var_dump($posts);

// TODO: search for new posts
	// TODO: index posts
	// TODO: use settings to finde out which fields should be indexed how
	// TODO: label post as indexed in post meta

// TODO: do the same for comments

// TODO: optimize index