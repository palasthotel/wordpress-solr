<?php
class PhSolr {
  private $client;

  private $config;

  private $search_args;

  public function __construct(Solarium\Client $client, array $config,
      $search_args) {
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

  private function getModifiedPosts() {
    // get the modification time of the last indexed post
    $last_post_modified = get_option('phsolr_last_post_modified',
        '1970-01-01T00:00:00Z'); // default is unix epoch

    // find newer posts
    $posts = get_posts(
        array(
          'post_type' => $this->config['post_types'],
          'post_status' => 'publish',
          'orderby' => 'modified',
          'order' => 'ASC',
          'posts_per_page' => $this->config['posts_per_index_update'],
          'date_query' => array(
            'after' => $last_post_modified,
            'column' => 'post_modified_gmt',
            'inclusive' => FALSE
          )
        ));

    // when posts have been found
    if (count($posts) > 0) {
      $last_post_modified = date('c',
          strtotime($posts[count($posts) - 1]->post_modified_gmt));

      // remember the time
      update_option('phsolr_last_post_modified', $last_post_modified);
    }

    // how many posts did we get?
    $posts_count = count($posts);
    $max_pages_count = $this->config['posts_per_index_update'] - $posts_count;

    $pages = array();

    if ($max_pages_count > 0) {

      // get the modification time of the last indexed page
      $last_page_modified = get_option('phsolr_last_page_modified',
          '1970-01-01T00:00:00Z'); // default it unix epoch

      $pages = get_pages(
          array(
            'post_status' => 'publish',
            'orderby' => 'modified',
            'order' => 'ASC',
            'posts_per_page' => $this->config['posts_per_index_update'] -
                 $posts_count,
                'date_query' => array(
                  'after' => $last_page_modified,
                  'column' => 'post_modified_gmt',
                  'inclusive' => FALSE
                )
          ));

      // when pages have been found
      if (count($pages) > 0) {
        $last_page_modified = date('c',
            strtotime($pages[count($pages) - 1]->post_modified_gmt));

        // remember the time
        update_option('phsolr_last_page_modified', $last_page_modified);
      }
    }

    return array_merge($posts, $pages);
  }

  private function getNewComments() {
    // get the modification time of the last indexed comment
    $last_comment_modified = get_option('phsolr_last_comment_modified',
        '1970-01-01T00:00:00Z'); // default it unix epoch

    return array();
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
    } catch (Solarium\Exception\HTTPException $e) {
      die($e->getMessage());
    }
  }

  private function getDeletedPosts() {
    $posts = get_posts(
        array(
          'post_status' => 'trash',
          'posts_per_page' => 500
        ));

    $pages = get_pages(
        array(
          'post_status' => 'trash',
          'posts_per_page' => 500
        ));

    return array_merge($posts, $pages);
  }

  private function getDeletedComments() {
    return array();
  }

  public function updatePostIndex() {
    $this->updateIndex($this->getModifiedPosts(), $this->getDeletedPosts(),
        'post');
  }

  public function updateCommentIndex() {
    $this->updateIndex($this->getNewComments(), $this->getDeletedComments(),
        'comment');
  }

  public function resetPostIndex() {
    // reset the last modified time, so the index will be rebuilt
    update_option('phsolr_last_post_modified', '1970-01-01T00:00:00Z');

    // reset the last modified time, so the index will be rebuilt
    update_option('phsolr_last_page_modified', '1970-01-01T00:00:00Z');
  }

  public function resetCommentIndex() {
    update_option('phsolr_last_comment_modified', '1970-01-01T00:00:00Z');
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
    } catch (Solarium\Exception\HTTPException $e) {
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
    } catch (Solarium\Exception\HTTPException $e) {
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
    if ($this->config['facets']) {
      $facetSet = $select->getFacetSet();

      // content type facet
      if ($this->config['facets']['type']) {
        $facet = $this->config['facets']['type'];
        // type facet
        $facetSet->createFacetField($facet['title'])->setField($facet['field'])->setMinCount(
            1);
      }

      // year facet
      if ($this->config['facets']['date']) {
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
