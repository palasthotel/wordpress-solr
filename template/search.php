<?php
get_header();
?>

<div class="solr-search">
	
	<h1 class="solr-search__header"><?php __('Solr Search', \SolrPlugin\Plugin::DOMAIN); ?></h1>

	<?php
	
	/**
	 * render the search form
	 */
	get_search_form();
	
	/**
	 * render spellcheck template
	 */
	do_action(\SolrPlugin\Plugin::ACTION_SEARCH_SPELLCHECK);
	
	/**
	 * render search form on top with advanced filter
	 */
	do_action(\SolrPlugin\Plugin::ACTION_SEARCH_RESULTS);
	
	/**
	 * render the pagination
	 */
	do_action(\SolrPlugin\Plugin::ACTION_SEARCH_PAGINATION);
	
	?>

</div><!-- .solr-search -->

<?php
get_footer();
?>