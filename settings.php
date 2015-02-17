<?php
add_action('init', 'phsolr_rewrites_init');
function phsolr_rewrites_init() {
  add_rewrite_rule('phsolr/reindex',
    'index.php?pagename=search&action=reindex', 'top');
  add_rewrite_rule('phsolr/optimize',
    'index.php?pagename=search&action=optimize', 'top');
}

add_action('admin_menu', 'phsolr_add_settings_page');

function phsolr_add_settings_page() {
  add_plugins_page('WordPress Solr by PALASTHOTEL',
      'WordPress Solr by PALASTHOTEL', 'edit_plugins', 'phsolr',
      'phsolr_settings_page');
}

function phsolr_settings_page() {
  ?>
<div>
  <h2>WordPress Solr by PALASTHOTEL</h2>
  Rebuilding or optimizing the search index.

  <ol>
    <li><a href="<?php echo home_url('/?pagename=search&action=reindex'); ?>"><?php esc_attr_e('Rebuild index'); ?>.</a></li>
    <li><a href="<?php echo home_url('/?pagename=search&action=optimize'); ?>"><?php esc_attr_e('Optimize index'); ?>.</a></li>
  </ol>
</div>
<?php
}
