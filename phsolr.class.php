<?php
class PhSolr {

  private $client;

  private $config;

  public function __construct(Solarium\Client $client, array $config) {
    $this->client = $client;
    $this->config = $config;
  }

  public function getConfiguration() {
    return $this->config;
  }

  private function getModifiedPosts() {
    // get the modification time of the last indexed post
    $last_post_modified = get_option('phsolr_last_post_modified',
        '1970-01-01T00:00:00Z'); // default it unix epoch

    // find newer posts
    $posts = get_posts(
        array(
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

    return $posts;
  }

  private function getModifiedPages() {
    // get the modification time of the last indexed page
    $last_page_modified = get_option('phsolr_last_page_modified',
        '1970-01-01T00:00:00Z'); // default it unix epoch

    // find newer pages
    $pages = get_pages(
        array(
          'post_status' => 'publish',
          'orderby' => 'modified',
          'order' => 'ASC',
          'posts_per_page' => $this->config['pages_per_index_update'],
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

    return $pages;
  }

  private function updateIndex(array $changedItems, array $deletedItems, $type) {
    // new update
    $update = $this->client->createUpdate();


    $set_fields = NULL;
    if ($type === 'post') {
      $set_fields = 'phsolr_set_post_fields';
    } else if ($type === 'page') {
      $set_fields = 'phsolr_set_page_fields';
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
          'posts_per_page' => 1000
        ));

    return $posts;
  }

  private function getDeletedPages() {
    $pages = get_pages(
        array(
          'post_status' => 'trash',
          'posts_per_page' => 1000
        ));

    return $pages;
  }

  public function updatePostIndex() {
    $this->updateIndex($this->getModifiedPosts(), $this->getDeletedPosts(),
        'post');
  }

  public function updatePageIndex() {
    $this->updateIndex($this->getModifiedPages(), $this->getDeletedPages(),
        'page');
  }

  public function resetPostIndex() {
    // reset the last modified time, so the index will be rebuilt
    update_option('phsolr_last_post_modified', '1970-01-01T00:00:00Z');
  }

  public function resetPageIndex() {
    // reset the last modified time, so the index will be rebuilt
    update_option('phsolr_last_page_modified', '1970-01-01T00:00:00Z');
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

  public function search($args) {
    if ($args === FALSE) {
      return array();
    }

    $select = $this->client->createSelect();

    $query = $args['text'];

    $select->setQuery($query);
    $select->setQueryDefaultOperator($this->config['default_query_operator']);

    $search_results = $this->client->select($select);

    return $search_results;
  }

  public function showResults($search_page_id, $search_args, $search_results) {
    // make parameters global
    global $phsolr_search_page_id;
    global $phsolr_search_args;
    global $phsolr_search_results;

    // if there is no result, there also was no query and so skip the rest
    if (!$search_results) {
      return;
    }

    $phsolr_search_page_id = $search_page_id;
    $phsolr_search_args = $search_args;
    $phsolr_search_results = $search_results;

    $template_file = 'search-results.tpl.php';

    $theme = wp_get_theme();
    $theme_dir = $theme->get_theme_root() . '/' . $theme->get_stylesheet();
    $include_path = $theme_dir . "/$template_file";

    if (!file_exists($include_path)) {
      $include_path = __DIR__ . "/templates/$template_file";
    }

    include $include_path;
  }
}
