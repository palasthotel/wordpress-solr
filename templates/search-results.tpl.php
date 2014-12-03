<form role="search" method="get" class="search-form"
  action="<?php echo home_url('/') ?>">
  <input type="hidden" name="page_id"
    value="<?php echo $phsolr_search_page_id ?>" />
  <div>
    <label> <span class="screen-reader-text">Search for:</span> <input
      type="search" class="search-field"
      placeholder="<?php echo __('Search …') ?>"
      value="<?php echo $phsolr_search_args['text'] ?>" name="search"
      title="Search for:" />
    </label> <input type="submit" class="search-submit" value="Search" />
  </div>
  <div class="advanced-search-settings">
<?php
$facets = $phsolr_search_results->getFacetSet()->getFacets();
foreach ($facets as $key => $facet) :
  ?>
    <fieldset class="facet-type">
      <legend><?php echo $key; ?></legend>
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
    </fieldset>
<?php
endforeach
;
$spellcheck_result = $phsolr_search_results->getSpellcheck();

?>
  </div>
</form>
<div id="search-results">
  <h1>
    <em><?php echo $phsolr_search_results->getNumFound() ?></em> Result(s) found
    for <em>“<?php echo $phsolr_search_args['text'] ?>”</em>
  </h1>
<?php
if (!$spellcheck_result->getCorrectlySpelled()) :
  $collations = $spellcheck_result->getCollations();
  if (count($collations) > 0) :
    $corrections = $spellcheck_result->getCollation(0)->getCorrections();
    ?>
  <p>
    Did you mean <em><?php echo implode(' ', $corrections); ?>?</em>

  <?php
  endif;

endif;

$highlighting = $phsolr_search_results->getHighlighting();
foreach ($phsolr_search_results as $doc) {
  global $phsolr_document;
  global $phsolr_highlighted_document;
  $phsolr_document = $doc;

  $type = explode('/', $doc->id)[0];

  if ($highlighting) {
    $phsolr_highlighted_document = $highlighting->getResult($doc->id);
  }

  if ($type === 'post') {
    include __DIR__ . '/search-result-post.tpl.php';
  } else if ($type === 'comment') {
    include __DIR__ . '/search-result-comment.tpl.php';
  } else {
    throw new Exception('Unknown document type: ' . $doc->type);
  }
}
?>


</div>
<?php
