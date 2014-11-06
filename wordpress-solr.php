<?php

/**
 * Plugin Name: WordPress Solr by PALASTHOTEL
 * Description: Use the Apache Solr search engine in WordPress.
 * Version: 0.0.1
 * Author: Palasthotel GmbH Author
 * URI: http://palasthotel.de/
 * Plugin URI: https://github.com/palasthotel/wordpress-solr
 */
require_once __DIR__ . '/phsolr.class.php';

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

$phsolr = null;

function phsolr_init() {
  global $phsolr;

  // autoload dependencies
  require_once __DIR__ . '/vendor/autoload.php';

  // load configuration
  if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
  } else {
    die(
        'Configuration file missing. Please add authentication information to' .
             ' "config.sample.php" and rename it to "config.php".');
  }

  // instantiate PhSolr
  $phsolr = new PhSolr(new Solarium\Client($solarium_config), $phsolr_config);
}

add_action('init', 'phsolr_init');
