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
		<p>Spellcheck</p>
		<p>Did you mean “<a href="?query=<?php
			echo implode('+', $corrections);
			?>"><?php
				echo implode(' ', $corrections);
				?></a>”?</p>
		<?php
	}
}