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
foreach ($facets as $facet):
?>
    <fieldset class="facet-type">
      <legend>Type</legend>
<?php foreach ($facet as $value => $count): ?>
      <input type="checkbox" name="facet-type-<?php echo $value ?>"
        id="facet-type-<?php echo $value ?>" /> <label
        for="facet-type-<?php echo $value ?>"><?php echo "$value ($count)" ?></label><br />
<?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>
    <!--fieldset class="cbgrp-year">
      <legend>Year</legend>
      <input type="checkbox" name="year-2014" id="year-2014" /> <label
        for="year-2014">2014 (15)</label><br /> <input type="checkbox"
        name="year-2013" id="year-2013" /> <label for="year-2013">2013 (11)</label><br />
      <input type="checkbox" name="year-2012" id="year-2012" /> <label
        for="year-2012">2012 (3)</label><br />
    </fieldset-->
  </div>
</form>
<div id="search-results">
  <h1>
    <em><?php echo $phsolr_search_results->getNumFound() ?></em> Result(s) found
    for <em>“<?php echo $phsolr_search_args['text'] ?>”</em>
  </h1>
<?php
foreach ($phsolr_search_results as $doc) {
  global $phsolr_document;
  $phsolr_document = $doc;

  $type = explode('/', $doc->id)[0];

  if ($type === 'post') {
    include __DIR__ . '/search-result-post.tpl.php';
  } else if ($type === 'page') {
    include __DIR__ . '/search-result-page.tpl.php';
  } else if ($type === 'page') {
    include __DIR__ . '/search-result-comment.tpl.php';
  } else {
    throw new Exception('Unknown document type: ' . $doc->type);
  }
}
?>
</div>
<?php
