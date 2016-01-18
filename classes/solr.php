<?php
namespace SolrPlugin;

class Solr {
  private $client;

  private $config;

  private $search_args;

  public function __construct(\Solarium\Client $client, array $config, $search_args) {
    $this->client = $client;
    $this->config = $config;
    $this->search_args = $search_args;
  }

  public function getConfiguration() {
    return $this->config;
  }

  public function getSearchArgs() {
    return $this->searchArgs;
  }



  private function updateIndex(array $changedItems, array $deletedItems, $type) {
    // new update
    $update = $this->client->createUpdate();


    $set_fields = NULL;
    if ($type === 'post') {
      $set_fields = 'phsolr_set_post_fields';
    } else if ($type === 'comment') {
      $set_fields = 'phsolr_set_comment_fields';
    } else {
      throw new Exception('unknown document type');
    }

    // for each item, add a document to the update query
    foreach ($changedItems as $item) {
      // create a new document for the data
      $doc = $update->createDocument();

      // set the ID
      $doc->id = $type . '/' . $item->ID;

      // set the other fields
      $set_fields($doc, $item);

      // and the type
      $doc->type = $type;

      // add document to query
      $update->addDocument($doc);
    }

    // for each deleted item
    foreach ($deletedItems as $item) {
      // add a delete command to the query
      $update->addDeleteById($type . '/' . $item->ID);
    }

    // commit
    $update->addCommit();

    // execute the update
    try {
      $result = $this->client->update($update);
      return $result;
    } catch (\Solarium\Exception\HTTPException $e) {
      die($e->getMessage());
    }
  }

  public function updatePostIndex() {
    $this->updateIndex($this->getModifiedPosts(), $this->getDeletedPosts(),
        'post');
  }

  public function updateCommentIndex() {
    $this->updateIndex($this->getNewComments(), $this->getDeletedComments(),
        'comment');
  }

  public function optimizeIndex() {
    // get an update query instance
    $update = $this->client->createUpdate();

    // optimize the index
    $update->addOptimize(NULL, NULL, NULL); // use solr defaults

    // this executes the query and returns the result
    try {
      $result = $this->client->update($update);
      return $result;
    } catch (\Solarium\Exception\HTTPException $e) {
      die($e->getMessage());
    }
  }

  public function deleteIndex() {
    // get an update query instance
    $update = $this->client->createUpdate();

    // delete the index
    $update->addDeleteQuery('*:*');

    // this executes the query and returns the result
    try {
      $result = $this->client->update($update);
      return $result;
    } catch (\Solarium\Exception\HTTPException $e) {
      die($e->getMessage());
    }
  }

  /**
   * Runs the search.
   *
   * @return array search results
   */
  public function search() {
    if ($this->search_args === FALSE) {
      return array();
    }

    $select = $this->client->createSelect();

    // set search query
    $query = $this->search_args['text'];
    $select->setQuery($query);

    $filter = $select->createFilterQuery('published')->setQuery('published:true');

    // set query offset and limit
    $select->setStart(($this->search_args['page'] - 1) * $this->config['query_limit']);
    $select->setRows($this->config['query_limit']);

    // set result fields
    $select->setFields($this->config['result_fields']);

    if (count($this->config['boost_functions']) > 0) {
      $dismax = $select->getDisMax();
      $dismax->setBoostFunctions(implode(' ', $this->config['boost_functions']));
    }

    // enable spellchecker
    if ($this->config['spellcheck']) {
      $spellcheck = $select->getSpellcheck();
      $spellcheck->setQuery($query);
      $spellcheck->setCount(10);
      $spellcheck->setBuild(TRUE);
      $spellcheck->setCollate(TRUE);
      $spellcheck->setExtendedResults(TRUE);
      $spellcheck->setCollateExtendedResults(TRUE);
    }

    // apply facets
    if ($this->search_args['facets']) {
      foreach ($this->search_args['facets'] as $facet_key_val => $enabled) {
        if ($enabled) {
          $kv = explode('-', $facet_key_val);
          $filter_query = new \Solarium\QueryType\Select\Query\FilterQuery();
          $filter_query->setKey(strtolower($kv[0]));
          $filter_query->setQuery($kv[1]);
          $select->addFilterQuery($filter_query);
        }
      }
    }

    // show other facets
    if (isset($this->config['facets'])) {
      $facetSet = $select->getFacetSet();

      // content type facet
      if (isset($this->config['facets']['type'])) {
        $facet = $this->config['facets']['type'];
        // type facet
        $facetSet->createFacetField($facet['title'])->setField($facet['field'])->setMinCount(
            1);
      }

      // year facet
      if (isset($this->config['facets']['date'])) {
        $facet = $this->config['facets']['date'];
        // the date facet
        // from epoch until now
        $facetSet->createFacetRange($facet['title'])->setField($facet['field'])->setStart(
            '1970-01-01T00:00:00Z')->setEnd(
            str_replace('+00:00', 'Z', date('c')))->setGap('+1YEAR');
      }
    }

    // weight of fields
    $dismax = $select->getDisMax();

    // build the weight string
    $weightString = '';
    foreach ($this->config['search_fields'] as $field => $weight) {
      $weightString .= " $field^$weight";
    }

    $dismax->setQueryFields(substr($weightString, 1));

    // set the default operator
    $select->setQueryDefaultOperator($this->config['default_query_operator']);

    $search_results = $this->client->select($select);

    return $search_results;
  }

  public function showResults($search_page_id, $search_results) {
    // make parameters global, so they can be used in the template
    global $phsolr_search_page_id;
    global $phsolr_search_args;
    global $phsolr_search_results;
    global $phsolr_search_config;

    $phsolr_search_page_id = $search_page_id;
    $phsolr_search_args = $this->search_args;
    $phsolr_search_results = $search_results;
    $phsolr_search_config = $this->config;

    $template_file = 'search-results.php';

    // theme/template paths
    $theme = wp_get_theme();
    $theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();
    $include_path = "$theme_dir/$template_file";

    // if a custom template exists in the directory of the current template,
    // use it, otherwise use the default template in this plugin's directory
    if (!file_exists($include_path)) {
      $include_path = __DIR__ . "/templates/$template_file";
    }

    include $include_path;
  }
}