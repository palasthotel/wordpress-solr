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

    $docs = array();

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

    // for each page, add a document to the update
    foreach ($changedItems as $item) {
      // create a new document for the data
      $doc = $update->createDocument();

      $set_fields($doc, $item);
      $doc->type = $type;

      $docs[] = $doc;
    }

    // add docs and commit
    $update->addDocuments($docs);
    $update->addCommit();

    // execute the update
    try {
      $result = $this->client->update($update);
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
  }

  public function search($query, $opt) {
  }
}
