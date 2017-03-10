<?php
/**
 * @var \SolrPlugin\SearchPage $this
 * @var array $solr_search_args
 * @var \Solarium\QueryType\Select\Result\Result $solr_search_results;
 */

?>
<div id="solr-search-results">
<?php
if (!$solr_search_results){
	?>
	<h1><em>No results found for
			<em>“<?php echo $solr_search_args['s'] ?>”</em></h1>
	<?php
} else {
	?>
	<h1>
		<em><?php echo $solr_search_results->getNumFound(); ?></em> Result(s)
		found for <em>“<?php echo $solr_search_args['s'] ?>”</em>
	</h1>

	<?php


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

	$highlighting = $solr_search_results->getHighlighting();
// display the total number of documents found by solr


// show documents using the resultset iterator
	foreach ($solr_search_results as $document) {

	/*	echo '<hr/><table>';

		// the documents are also iterable, to get all fields
		foreach ($document as $field => $value) {
			// this converts multivalue fields to a comma-separated string
			if (is_array($value)) {
				$value = implode(', ', $value);
			}

			echo '<tr><th>' . $field . '</th><td>' . $value . '</td></tr>';
		}

		echo '</table><br/><b>Highlighting results:</b><br/>';*/

		// highlighting results can be fetched by document id (the field defined as uniquekey in this schema)
		$highlightedDoc = null;
		if($highlighting != null) $highlightedDoc = $highlighting->getResult($document->id);
		if ($highlightedDoc) {
			foreach ($highlightedDoc as $field => $highlight) {
				
			    echo $document->ts_title .'<br/>';
				echo $document->ts_author. '<br/>';
				echo implode(' (...) ', $highlight) . '<br/>';
				echo $document->url. '<br/>';

				echo '<br/>';
			}

		} else {
			echo $document->ts_title .'<br/>';
			echo $document->ts_author. '<br/>';
			echo $document->url. '<br/>';
			
			echo '<br/>';
		}

	}


} // !$solr_search_results
?>
</div>