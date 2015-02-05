<?php
/**
 * This is an example configuration file.
 * Change it according to your needs and
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
  // either 'any' or an array of the post types that shall be indexed
  'post_types' => 'any',

  // number of posts per index update
  'posts_per_index_update' => 50,
  // number of comments per index update
  'comments_per_index_update' => 50,

  // how often should the index be updated?
  'posts_update_interval' => 'hourly',
  'comments_update_interval' => 'daily',

  'optimization_interval' => 'weekly',

  // 'AND' or 'OR'
  'default_query_operator' => 'AND',

  // enable spellchecker
  'spellcheck' => TRUE,

  // maximum number of search results
  'query_limit' => 15,

  // list of fields to search and their corresponding weight
  'search_fields' => array(
    'title' => 4.0,
    'date' => 0.5,
    'author' => 0.5,
    'content' => 1.0
  ),

  // list of fields to return
  'result_fields' => array(
    'title',
    'date',
    'author'
  ),

  // list of fields that are intended to be highlighted
  'highlight_fields' => array(
    'title',
    'content'
  ),

  // facet definitions
  'facets' => array(
    'type' => array(
      'field' => 'type',
      'title' => 'Type'
    ),
    'date' => array(
      'field' => 'date',
      'title' => 'Date'
    )
  )
);

/**
 * Sets the fields from a WP_Post object to a Solarium Document, which will be
 * uploaded to Solr.
 *
 * Don't set the ID explicitly. It will be set for you to.
 *
 * @param Solarium\QueryType\Update\Query\Document\DocumentInterface $document
 * @param WP_Post $post
 */
function phsolr_set_post_fields(
    Solarium\QueryType\Update\Query\Document\DocumentInterface $document,
    WP_Post $post) {
  // get the authors name
  $author_name = get_user_by('id', $post->post_author)->display_name;

  $document->title = $post->post_title;
  $document->date = date('Y-m-d\TH:i:s\Z', strtotime($post->post_date_gmt));
  $document->modified = date('Y-m-d\TH:i:s\Z',
      strtotime($post->post_modified_gmt));
  $document->author = $author_name;
  $document->content = strip_tags($post->post_content);
  $document->url = $post->guid;

  $document->type = $post->type;
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
  $document->content = $comment['comment_content'];
  $document->author = $comment['comment_author'];
  $document->date = date('Y-m-d\TH:i:s\Z',
      strtotime($comment['comment_date_gmt']));

  $document->type = 'comment';
}
