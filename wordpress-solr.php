<?php

/**
 * Plugin Name: WordPress Solr by PALASTHOTEL
 * Description: Use the Apache Solr search engine in WordPress.
 * Version: 0.0.1
 * Author: Palasthotel GmbH
 * URI: http://palasthotel.de/
 * Plugin URI: https://github.com/palasthotel/wordpress-solr
 */
require_once __DIR__ . '/phsolr.class.php';

$_phsolr = NULL;

/**
 * Returns an instance of PhSolr.
 *
 * @return PhSolr
 */
function phsolr_get_instance() {
  global $_phsolr;

  if ($_phsolr === NULL) {
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
    $_phsolr = new PhSolr(new Solarium\Client($solarium_config), $phsolr_config);
  }

  return $_phsolr;
}

register_activation_hook(__FILE__, 'phsolr_activation');
register_deactivation_hook(__FILE__, 'phsolr_deactivation');

function phsolr_activation() {
  $phsolr = phsolr_get_instance();
  $config = $phsolr->getConfiguration();

  // schedule index updates
  // TODO show errors if something goes wrong
  wp_schedule_event(time(), $config['posts_update_interval'],
      'phsolr_posts_update_index');
  wp_schedule_event(time() + 60 * 10, $config['pages_update_interval'],
      'phsolr_pages_update_index');
  wp_schedule_event(time() + 60 * 20, $config['comments_update_interval'],
      'phsolr_comments_update_index');
}

function phsolr_deactivation() {
  wp_clear_scheduled_hook('phsolr_update_post_index');
  wp_clear_scheduled_hook('phsolr_update_page_index');
  wp_clear_scheduled_hook('phsolr_update_comment_index');
}

function phsolr_update_post_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->updatePostIndex();
}

function phsolr_update_page_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->updatePageIndex();
}

function phsolr_update_comment_index() {
  // does nothing right now
}
