<?php
class PhSolr {

  private $client;

  private $config;

  public function __construct(Solarium\Client $client, array $config) {
    $this->client = $client;
    $this->config = $config;
  }

  private function getModifiedPosts() {
    $last_post_modified = '2014-10-23T08:28:49+0000';

    $posts = get_posts(
        array(
            'post_status' => 'publish',
            'orderby' => 'modified',
            'order' => 'ASC',
            'posts_per_page' => $phsolr_config['posts_per_index_update'],
            'date_query' => array(
                'after' => $last_post_modified,
                'column' => 'post_modified_gmt',
                'inclusive' => TRUE
            )
        ));

    return $posts;
  }

  public function updateIndexPosts() {
    $posts = $this->getModifiedPosts();

    var_dump($posts);
  }

  private function getModifiedPages() {
    $last_page_modified = '2014-10-23T08:28:49+0000';

    $pages = get_pages(
        array(
          'post_status' => 'publish',
          'orderby' => 'modified',
          'order' => 'ASC',
          'posts_per_page' => $phsolr_config['pages_per_index_update'],
          'date_query' => array(
            'after' => $last_page_modified,
            'column' => 'post_modified_gmt',
            'inclusive' => TRUE
          )
        ));

    return $pages;
  }

  public function updateIndexPages() {
    $pages = $this->getModifiedPages();

    var_dump($pages);
  }

  public function updateIndexComments() {
  }

  public function search($query, $opt) {
  }
}
