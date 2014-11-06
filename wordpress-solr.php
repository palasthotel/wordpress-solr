<?php

/**
 * Plugin Name: WordPress Solr by PALASTHOTEL
 * Description: Use the Apache Solr search engine in WordPress.
 * Version: 0.0.1
 * Author: Palasthotel GmbH Author
 * URI: http://palasthotel.de/
 * Plugin URI: https://github.com/palasthotel/wordpress-solr
 */
require_once __DIR__ . '/init.php';


/**
 * Adds a set of documents to the solr index.
 *
 * @param array $posts
 */
function phsolr_add_documents(Solarium\Client $client, array $posts) {
  foreach ($post_ids as $post_id) {
    phsolr_add_document($post_id);
  }
}

/**
 * Adds a single post to the solr index.
 *
 * @param
 *          $post
 */
function phsolr_add_document(Solarium\Client $client, $post) {
}

function phsolr_optimize_index(Solarium\Client $client) {
}

function phsolr_test_something() {
  global $phsolr_config;

  $last_post_modified = '2014-10-23T08:28:49+0000';
  $last_page_modified = '2014-10-23T08:28:49+0000';

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

  var_dump($posts);
  var_dump($pages);
}

add_action('init', 'phsolr_test_something');

//exit();
