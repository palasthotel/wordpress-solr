<?php
/**
 * load core
 */
define( 'WP_MEMORY_LIMIT','2G' );
define('WP_USE_THEMES', false);
$paths = explode( 'wp-content',__FILE__ );
require_once( $paths[0] . 'wp-load.php' );

ini_set( 'memory_limit', '-1' );

// output to shell
while( 0 != ob_get_level() ) {
	ob_end_clean();
}

/**
 * do the cron stuff
 * @var SolrPlugin\Plugin $solr_plugin
 */
$solr_plugin = solr_get_plugin();
$i = 0;
$indexed = 0;
$error = 0;
$number = $solr_plugin->get_config()->get_option(\SolrPlugin\Plugin::OPTION_DOCUMENTS_PER_CALL);
do{
	if($error > 5){
		print "Too many errors: ".$error."\n";
		break;
	}
	/**
	 * index posts to solr
	 */
	$results = $solr_plugin->index_posts($number);
	if($results->error === true){
		print "Error while indexing: \n";
		var_dump($results);
		$error++;
		continue;
	}
	$error = 0;
	if(count($results->posts)< 1){
		break;
	}
	/**
	 * break if too many rounds
	 */
	$i++;
	if($i > 999){
		echo "\n --- security break --- \n";
		break;
	} 
} while( true );