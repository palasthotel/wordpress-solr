<?php
/**
 * This is an example configuration file. Change it according to your needs and
 * rename it to config.php.
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

$phsolr_config = array(
  // number of documents (posts, pages, comments) per index update
  'posts_per_index_update' => 50,
  'pages_per_index_update' => 50,
  'comments_per_index_update' => 50,

  // how often should the index be updated
  'update_interval' => 'hourly'
);
