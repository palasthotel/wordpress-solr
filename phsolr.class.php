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
    $last_post_modified = '2014-10-23T08:28:49+0000';

    $posts = get_posts(
        array(
          'post_status' => 'publish',
          'orderby' => 'modified',
          'order' => 'ASC',
          'posts_per_page' => $this->config['posts_per_index_update'],
          'date_query' => array(
            'after' => $last_post_modified,
            'column' => 'post_modified_gmt',
            'inclusive' => TRUE
          )
        ));

    return $posts;
  }

  private function getModifiedPages() {
    $last_page_modified = '2014-10-23T08:28:49+0000';

    $pages = get_pages(
        array(
          'post_status' => 'publish',
          'orderby' => 'modified',
          'order' => 'ASC',
          'posts_per_page' => $this->config['pages_per_index_update'],
          'date_query' => array(
            'after' => $last_page_modified,
            'column' => 'post_modified_gmt',
            'inclusive' => TRUE
          )
        ));

    return $pages;
  }

  private function updateIndex(array $items, $type) {
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
      throw new Exception('unknown type');
    }

    // for each page, add a document to the update
    foreach ($items as $item) {
      var_dump($item);
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

      var_dump($result);
    } catch (Solarium\Exception\HTTPException $e) {
      die($e->getMessage());
    }
  }

  public function updatePostIndex() {
    $this->updateIndex($this->getModifiedPosts(), 'post');
  }

  public function updatePageIndex() {
    $this->updateIndex($this->getModifiedPages(), 'page');
  }

  public function resetPostIndex() {
  }

  public function resetPageIndex() {
  }

  public function optimizeIndex() {
  }

  public function search($query, $opt) {
  }
}
