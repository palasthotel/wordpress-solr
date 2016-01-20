<?php
/**
 * @var \SolrPlugin\Shortcode $this
 * @var array $solr_search_args
 * @var \Solarium\QueryType\Select\Result\Result $solr_search_results;
 */
?>
<form role="search" method="get" class="search-form"
  action="<?php echo home_url('/') ?>">
  <div>
    <label> <span class="screen-reader-text">Search for:</span> <input
      type="search" class="search-field"
      placeholder="<?php echo __('Search …') ?>"
      value="<?php echo $solr_search_args['s'] ?>" name="s"
      title="Search for:" />
    </label> <input type="submit" class="search-submit" value="Search" />
  </div>
<?php
if ($solr_search_results) :
?>
  <div class="advanced-search-settings">
<?php
  $facets = $solr_search_results->getFacetSet()->getFacets();
  foreach ($facets as $key => $facet) :
    ?>
    <div class="facet-type">
      <span class="facet-type-id"><?php echo $key; ?></span>
<?php
    foreach ($facet as $value => $count) :
      if ($count > 0) :
        if ($key === 'Date') {
          $value = date('Y', strtotime($value));
        }
        ?>
      <input type="checkbox" name="facet-<?php echo $key.'-'.$value ?>"
        id="facet-<?php echo $key.'-'.$value ?>" /> <label
        for="facet-<?php echo $key.'-'.$value ?>"><?php echo "$value ($count)" ?></label><br />

      <?php
    endif;
    endforeach
    ;
    ?>
    </div>
<?php
  endforeach
  ;

/**
 * @var \Solarium\QueryType\Select\Result\Spellcheck\Result
 */
$spellcheck_result = $solr_search_results->getSpellcheck();

?>
  </div>
<?php
endif;
?>
</form>
<div id="search-results">
<?php
if (!$solr_search_results):
?>
<h1><em>No results found for
  <em>“<?php echo $solr_search_args['s'] ?>”</em></h1>
<?php
else:
?>
  <h1>
    <em><?php echo $solr_search_results->getNumFound() ?></em> Result(s) found
    for <em>“<?php echo $solr_search_args['s'] ?>”</em>
  </h1>
<?php
  if ($spellcheck_result != null && !$spellcheck_result->getCorrectlySpelled()) :
    $collations = $spellcheck_result->getCollations();
    if (count($collations) > 0) :
      $corrections = $spellcheck_result->getCollation(0)->getCorrections();
      ?>
    <p>
      Did you mean “<a href="?query=<?php echo implode('+', $corrections); ?>"><?php echo implode(' ', $corrections); ?></a>”?
    </p>
    <?php
    endif;

  endif;

	// TODO: handle search results other than POSTs like comments

	/**
	 * get ids from solr result documents
	 */
	$ids = array();
	foreach ($solr_search_results as $document) {
		/**
		* @var \Solarium\QueryType\Select\Result\DocumentInterface $document
		*/

		/**
		 * matches for posts
		 */
		if( preg_match("@^post/([0-9]+)$@ius", $document->id, $matches) ){
			$ids[] = $matches[1];
		}
		// TODO: matches for other types like comments
	}

	/**
	 * do the loop with result ids
	 */
	$query = new \WP_Query(array(
		'post__in' => $ids,
	  	'order_by' => 'post__in',
	  	'post_type' => 'any',
	));
	while($query->have_posts()){
		$query->the_post();
		include $this->plugin->dir."/templates/search-result-item.php";
	}
	wp_reset_postdata();

endif;
?>

</div>
<?php
