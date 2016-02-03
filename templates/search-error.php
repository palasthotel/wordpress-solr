<?php
/**
 * @var \SolrPlugin\SearchPage $this
 * @var array $solr_search_args
 * @var \Solarium\Exception\HttpException $e
 */

/**
 * render search form template
 */
do_action('solr_search_form','',$solr_search_results);

?>

<div id="solr-search-error">
	<h2>Es ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.</h2>
	<span style="display:none"><?php print_r($e); ?></span>
</div>