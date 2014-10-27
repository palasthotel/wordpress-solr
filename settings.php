<?php

// set admin options page
add_action('admin_menu', 'ph_solr_add_menu');

function ph_solr_add_menu() {
  add_options_page('WordPress Solr Settings', 'Solr', 'manage_options',
      'ph_solr', 'ph_solr_settings_page');
}

function ph_solr_settings_page() {
  ?>
<div class="wrap">
  <h2>WordPress Solr by PALASTHOTEL</h2>
  <form method="post" action="options.php">
    <h3>Connection Options</h3>
<?php settings_fields('ph_solr_connection'); ?>
<?php do_settings_sections('ph_solr'); ?>
<?php submit_button(); ?>
  </form>
</div>
<?php
}

function ph_solr_register_settings() {
  register_setting('ph_solr', 'ph_solr_connection');

  // sections
  add_settings_section('ph_solr_connection', 'Solr Connection Settings',
      'ph_solr_connection_desc', 'ph_solr');

  // settings
  add_settings_field('ph_solr_host', 'Host', 'ph_solr_field', 'ph_solr',
      'ph_solr_connection',
      array(
        'group' => 'ph_solr_connection',
        'key' => 'host',
        'desc' => 'Solr host address'
      ));

  // add_settings_field('ph_solr_port', 'Port',
  // 'ph_solr_connection_settings_callback', 'general', 'ph_solr_connection',
  // array(
  // 'key' => 'port',
  // 'desc' => 'Solr port'
  // ));

  // add_settings_field('ph_solr_path', 'Path',
  // 'ph_solr_connection_settings_callback', 'general', 'ph_solr_connection',
  // array(
  // 'key' => 'path',
  // 'desc' => 'Path to Solr endpoint'
  // ));

  // add_settings_field('ph_solr_key', 'Key',
  // 'ph_solr_connection_settings_callback', 'general', 'ph_solr_connection',
  // array(
  // 'key' => 'key',
  // 'desc' => 'Authentication key token'
  // ));

  // add_settings_field('ph_solr_secret', 'Secret',
  // 'ph_solr_connection_settings_callback', 'general', 'ph_solr_connection',
  // array(
  // 'key' => 'secret',
  // 'desc' => 'Authentication secret'
  // ));
}

function ph_solr_connection_desc() {
  ?><p>Connection settings for Solr</p>
<?php
}

function ph_solr_field($args) {
  $group = $args['group'];
  $option = 'ph_solr_' . $args['key'];

  print_r($args);

  // generate markup
  ?><input type="text" name="<?php echo $group.'['.$option.']'; ?>"
  value="<?php echo esc_attr(get_option($option)); ?>" /><?php
  if (isset($args['desc'])) {
    ?><p class="description"><?php echo esc_html($args['desc']); ?></p><?php
  }
}
