<?php

function cdwps_delete_default_post_and_plugins()
{
  $hello_world_post = get_post(1); // Delete the default "Hello World" post

  // Check if the post exists.
  if ($hello_world_post) {
    // Delete the post permanently.
    wp_delete_post(1, true);
  }

  $pluginsToDelete = array(
    'akismet/akismet.php',
    // Akismet plugin
    'hello.php' // Hello Dolly plugin
  );

  foreach ($pluginsToDelete as $plugin) {
    if (is_plugin_active($plugin) || is_plugin_inactive($plugin)) {
      deactivate_plugins($plugin);
      delete_plugins(array($plugin));
    }
  }
}
add_action('admin_init', 'cdwps_delete_default_post_and_plugins');

function cdwps_change_default_settings()
{
  // Change Date Format to d/m/Y.
  update_option('date_format', 'd/m/Y');

  // Change homepage displays from latest posts to Sample Page.
  $homepage = get_page_by_path('sample-page');
  if ($homepage) {
    update_option('page_on_front', $homepage->ID);
    update_option('show_on_front', 'page');
  }

  // Change permalink structure to post name
  global $wp_rewrite;
  $wp_rewrite->set_permalink_structure('\/%postname%\/');
  $wp_rewrite->flush_rules();
}
add_action('admin_init', 'cdwps_change_default_settings');