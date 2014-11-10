<?php
/**
 * This is an example configuration file. Change it according to your needs and
 * rename it to config.php.
 */

/**
 * Solarium connection information.
 */
$solarium_config = array(
  'endpoint' => array(
    array(
      'host' => '127.0.0.1',
      'port' => 8983,
      'path' => '/solr/'
    )
  )
);

/**
 * Configuration for this module.
 */
$phsolr_config = array(
  // number of posts per index update
  'posts_per_index_update' => 50,
  // number of posts per index update
  'pages_per_index_update' => 50,
  // number of comments per index update
  'comments_per_index_update' => 50,

  // how often should the index be updated?
  'posts_update_interval' => 'hourly',
  'pages_update_interval' => 'hourly',
  'comments_update_interval' => 'daily'
);

/**
 * Sets the fields from a WP_Post object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Post $post
 */
function phsolr_set_post_fields(
    Solarium\QueryType\Update\Query\Document\DocumentInterface $document,
    WP_Post $post) {
  // get the authors name
  $author_name = get_user_by('id', $post->post_author)->display_name;

  // please keep the schema 'post/:id', so IDs are unique for all db tables
  $document->id = 'post/' . $post->ID;

  $document->title = $post->post_title;
  $document->date = date('Y-m-d\TH:i:s\Z', strtotime($post->post_date_gmt));
  $document->modified = date('Y-m-d\TH:i:s\Z',
      strtotime($post->post_modified_gmt));
  $document->author = $author_name;
  $document->content = $post->post_content;
  $document->url = $post->guid;

  // aggregate field
  $document->aggregate = $post->post_title . ' ' . $post->post_content . ' ' .
       $post->post_date . ' ' . $author_name;

  var_dump($document->aggregate);

  $document->type = 'post';
}

/**
 * Sets the fields from a WP_Post object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Post $post
 */
function phsolr_set_page_fields(
    Solarium\QueryType\Update\Query\Document\DocumentInterface $document,
    WP_Post $page) {
}

/**
 * Sets the fields from a WP_Comment object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Comment $comment
 */
function phsolr_set_comment_fields(
    Solarium\QueryType\Update\Query\Document\DocumentInterface $document,
    WP_Comment $comment) {
}
