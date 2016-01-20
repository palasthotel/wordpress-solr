<?php
/**
 * @var \SolrPlugin\SearchPage $this
 * @var array $solr_search_args
 * @var \Solarium\QueryType\Select\Result\Result $solr_search_results;
 */

/**
 * render search form on top with advanced filter
 */
echo $this->get_search_form("",$solr_search_results);


?>
<div id="search-results">
<?php

if (!$solr_search_results){
	?>
	<h1><em>No results found for
			<em>“<?php echo $solr_search_args['s'] ?>”</em></h1>
	<?php
} else {
	?>
	<h1>
		<em><?php echo $solr_search_results->getNumFound() ?></em> Result(s)
		found for <em>“<?php echo $solr_search_args['s'] ?>”</em>
	</h1>
	<?php

	/**
	 * @var \Solarium\QueryType\Select\Result\Spellcheck\Result
	 */
	$spellcheck_result = $solr_search_results->getSpellcheck();
	if ($spellcheck_result != NULL && !$spellcheck_result->getCorrectlySpelled()) {
		$collations = $spellcheck_result->getCollations();
		if (count($collations) > 0) {
			$corrections = $spellcheck_result->getCollation(0)
			  ->getCorrections();
			?>
			<p>Did you mean “<a href="?query=<?php
				echo implode('+', $corrections);
				?>"><?php
					echo implode(' ', $corrections);
					?></a>”?</p>
			<?php
		}
	}

	// TODO: handle search results other than POSTs like comments
	/**
	 * get ids from solr result documents
	 */
	$ids = array();
	$documents = array();
	foreach ($solr_search_results as $document) {
		/**
		 * @var \Solarium\QueryType\Select\Result\DocumentInterface $document
		 */

		/**
		 * matches for posts
		 */
		if (preg_match("@^post/([0-9]+)$@ius", $document->id, $matches)) {
			$ids[] = $matches[1];
			$documents[] = $document;
		}
		// TODO: matches for other types like comments
	}

	if (count($ids) > 0) {
		/**
		 * do the loop with result ids
		 */
		$query = new \WP_Query(array(
		  'post__in' => $ids,
		  'order_by' => 'post__in',
		  'post_type' => 'any',
		));
		$i = 0;
		while ($query->have_posts()) {
			$query->the_post();
			/**
			 * render the result
			 */
			do_action('solr_render_search_results_item', $documents[$i]);
			$i++;
		}
		wp_reset_postdata();
	}

} // !$solr_search_results
?>
</div>