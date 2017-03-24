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
 * write logs
 * @param $content
 */
function write_log($content){
	
	$dir = wp_upload_dir();
	$log = $dir["basedir"] . "/solr-log.txt";
	
	$file = fopen( $log, 'a' );
	fwrite( $file, $content."\n\n" );
	fclose( $file );
	
}

write_log("Start ---->  ".date("Y.m.d H:i"));

/**
 * do the cron stuff
 * @var SolrPlugin\Plugin $solr_plugin
 */
$solr_plugin = solr_get_plugin();
$i = 0;
$indexed = 0;
$error = 0;
$number = $solr_plugin->config->get_option(\SolrPlugin\Plugin::OPTION_DOCUMENTS_PER_CALL);

do{
	if($error > 5){
		print "Too many errors: ".$error."\n";
		write_log("Too many errors: ".$error);
		break;
	}
	
	write_log("Index {$number} next Posts.");
	
	/**
	 * index posts to solr
	 */
	ob_start();
	$results = $solr_plugin->index_runner->index_posts($number);
	$output = ob_get_contents();
	ob_end_clean();
	
	ob_start();
	var_dump($results);
	$output = ob_get_contents();
	ob_end_clean();
	
	
	if($results->error === true){
		
		write_log($output);
		
		print " >>> Error while indexing >>> HAVE A LOOK IN LOG\n";
		write_log(" >>> Error while indexing >>> ");
		
		
		
		write_log($output);
		
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
