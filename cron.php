<?php
/**
 * load core
 */
define( 'WP_MEMORY_LIMIT', '2G' );
define( 'WP_USE_THEMES', false );
$paths = explode( 'wp-content', __FILE__ );
require_once( $paths[0] . 'wp-load.php' );

do_action(\SolrPlugin\Plugin::ACTION_CRON_START);

ini_set( 'memory_limit', '-1' );

// output to shell
while ( 0 != ob_get_level() ) {
	ob_end_clean();
}


/**
 * write logs
 *
 * @param $content
 */
function write_log( $content ) {

	$dir = wp_upload_dir();
	$log = $dir["basedir"] . "/solr-log.txt";

	$file = fopen( $log, 'a' );
	fwrite( $file, $content . "\n\n" );
	fclose( $file );

}

//write_log("Start ---->  ".date("Y.m.d H:i"));

/**
 * do the cron stuff
 * @var SolrPlugin\Plugin $solr_plugin
 */
$solr_plugin = solr_get_plugin();
$i           = 0;
$indexed     = 0;
$error       = 0;
$number      = $solr_plugin->config->get_option( \SolrPlugin\Plugin::OPTION_DOCUMENTS_PER_CALL );

do {
	if ( $error > 5 ) {
		print "Too many errors: " . $error . "\n";
		break;
	}

	/**
	 * index posts to solr
	 */
	$results = $solr_plugin->index_runner->index_posts( $number );


	if ( $results->error === true ) {

		print " >>> Error while indexing >>>\n";

		$error ++;
		continue;
	}

	$error = 0;
	$step_indexed = count( $results->posts );
	if ( count( $results->posts ) < 1 ) {
		break;
	}

	$indexed += $step_indexed;
	echo "Indexed + {$step_indexed} = $indexed\n";

	/**
	 * break if too many rounds
	 */
	$i ++;
	if ( $i > 999 ) {
		echo "\n --- security break --- \n";
		break;
	}
} while ( true );

do_action(\SolrPlugin\Plugin::ACTION_CRON_FINISH );

// TODO: optimize index
