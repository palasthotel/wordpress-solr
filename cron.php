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
print "looking\n";
$query = new WP_Query(array('s'=>''));

$solr_plugin = solr_get_plugin();
$posts = $solr_plugin->posts->getModifiedPosts(2);

foreach ($posts as $counter => $post) {
	print $post->post_title."\n";
}

// TODO: search for new posts
	// TODO: index posts
	// TODO: use settings to finde out which fields should be indexed how
	// TODO: label post as indexed in post meta

// TODO: do the same for comments

// TODO: optimize index