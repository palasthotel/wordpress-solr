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
foreach ($facets as $legend => $facet) :
?>
    <fieldset class="facet-type">
      <legend><?php echo $legend; ?></legend>
<?php
  foreach ($facet as $value => $count) :
    if ($count > 0) :
      if ($legend === 'Year') {
        $value = date('Y', strtotime($value));
      }
      ?>
      <input type="checkbox" name="facet-<?php echo $value ?>"
        id="facet-<?php echo $value ?>" /> <label
        for="facet-<?php echo $value ?>"><?php echo "$value ($count)" ?></label><br />
<?php
endif;
endforeach;
?>
    </fieldset>
<?php endforeach; ?>
  </div>
</form>
<div id="search-results">
  <h1>
    <em><?php echo $phsolr_search_results->getNumFound() ?></em> Result(s) found
    for <em>“<?php echo $phsolr_search_args['text'] ?>”</em>
  </h1>
<?php
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
