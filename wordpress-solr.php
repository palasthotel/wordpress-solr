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
    $_phsolr = new PhSolr(new Solarium\Client($solarium_config), $phsolr_config,
        phsolr_get_search_args());
  }

  return $_phsolr;
}

// function phsolr_test() {
//   $phsolr = phsolr_get_instance();
//
//   // $phsolr->updatePostIndex();
// }
//
// add_action('init', 'phsolr_test');

register_activation_hook(__FILE__, 'phsolr_activate');
register_deactivation_hook(__FILE__, 'phsolr_deactivate');

function phsolr_create_search_result_page() {
  // search for a page titled 'Search Results'
  $page = get_page_by_title('Search Results', 'OBJECT', 'page');

  if (!$page) {
    // if it doesn't exist, create a new page
    $page = array(
      'post_type' => 'page',
      'post_title' => 'Search Results',
      'post_content' => '[phsolr_search_results]',
      'post_status' => 'publish',
      'post_author' => 1,
      'post_name' => 'Search Results',
      'comment_status' => 'closed'
    );

    $page_id = wp_insert_post($page);
  } else if ($page->post_status != 'publish') {
    // if the page is unpublished, publish it
    $page->post_status = 'publish';

    wp_update_post($page);
  } else {
    // otherwise remember its ID
    $page_id = $page->ID;
  }
}

function phsolr_activate() {
  $phsolr = phsolr_get_instance();
  $config = $phsolr->getConfiguration();

  phsolr_create_search_result_page();

  // schedule index updates
  wp_schedule_event(time(), $config['posts_update_interval'],
      'phsolr_posts_update_index');
  wp_schedule_event(time() + 60 * 20, $config['comments_update_interval'],
      'phsolr_comments_update_index');

  // optimize index
  if ($config['optimization_interval'] !== 'never') {
    wp_schedule_event(time() + 60 * 30, $config['optimization_interval'],
        'phsolr_optimize_index');
  }
}

function phsolr_deactivate() {
  wp_clear_scheduled_hook('phsolr_update_post_index');
  wp_clear_scheduled_hook('phsolr_update_comment_index');
}

function phsolr_update_post_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->updatePostIndex();
}

function phsolr_update_comment_index() {
  // does nothing right now
}

function phsolr_optimize_index() {
  $phsolr = phsolr_get_instance();

  $phsolr->optimizeIndex();
}

function phsolr_print_search_form() {
  echo phsolr_search_form();
}

function phsolr_search_form($form) {
  $phsolr =
  $search_args = phsolr_get_search_args();
  $search_page_id = phsolr_get_search_page_id();
  ob_start();
  ?>
<form role="search" method="get" class="search-form"
  action="<?php echo home_url('/') ?>">
  <input type="hidden" name="page_id" value="<?php echo $search_page_id; ?>" />
  <div>
    <label> <span class="screen-reader-text">Search for:</span> <input
      type="search" class="search-field"
      placeholder="<?php echo __('Search …') ?>"
      value="<?php echo $search_args['text']; ?>" name="search"
      title="Search for:" />
    </label> <input type="submit" class="search-submit" value="Search" />
  </div>
</form>
<?php
  $form = ob_get_clean();
  return $form;
}
add_filter('get_search_form', 'phsolr_search_form');

function phsolr_get_search_page_id() {
  $page = get_page_by_title('Search Results');
  return $page->ID;
}

/**
 * Returns the search arguments as an associative array or FALSE if there was no
 * search.
 *
 * @return array
 */
function phsolr_get_search_args() {
  $args = array();
  if (isset($_GET['search'])) {
    $args['text'] = $_GET['search'];
  } else {
    return FALSE;
  }

  $facet_args = array();
  foreach ($_GET as $key => $value) {
    if (strpos($key, 'facet-') === 0) {
      $facet_args[substr($key, 6)] = $value === 'on';
    }
  }

  $args['facets'] = $facet_args;

  return $args;
}

// add shortcodes for use in pages
add_shortcode('phsolr_search_form', 'phsolr_print_search_form');
add_shortcode('phsolr_search_results', 'phsolr_print_search_results');

function phsolr_print_search_results() {
  $search_page_id = phsolr_get_search_page_id();

  $phsolr = phsolr_get_instance();
  $search_results = $phsolr->search();
  $phsolr->showResults($search_page_id, $search_results);
}
