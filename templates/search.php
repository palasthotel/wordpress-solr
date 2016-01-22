<?php
/**
 * @var \SolrPlugin\SearchPage $this
 * @var array $solr_search_args
 * @var \Solarium\QueryType\Select\Result\Result $solr_search_results;
 */

get_header();
?>

<div class="solr-search">

	<h1 class="solr-search-header">Solr Suche</h1>

	<?php
	/**
	 * render search form on top with advanced filter
	 */
	do_action('solr_search_results');
	?>

</div><!-- .solr-search -->

<?php
get_footer();
?>