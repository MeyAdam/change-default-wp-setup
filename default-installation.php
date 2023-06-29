<?php

/**
 * @package DefaultInstallation
 * 
 * Plugin Name:       Default Installation
 * Plugin URI:        https://github.com/MeyAdam/default-installation-wp-plugin
 * Description:       Delete Hello World Post. Change date format to d/m/Y. Change homepage displays to "Sample Page" for easy stylesheet setup. Change Permalink structure to Post name. Delete Akismet and Hello Dolly plugins. Install Hello Elementor theme, activate it and delete other deactivated themes. Install Elementor Website Builder & Wordfence Security plugin.
 * Version:           1.0.0
 * Author:            Mey Adam
 * Author URI:        https://meyadam.com/
 * License:           GPL v2 or later
 * Text Domain:       default-installation
 */

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

class DefaultInstallation
{
  function __construct()
  {
    add_action('admin_init', array($this, 'delete_default_post_plugins'));
    add_action('admin_init', array($this, 'change_default_settings'));
    add_action('admin_init', array($this, 'install_and_delete_unused_theme'));
    add_action('admin_init', array($this, 'install_additional_plugins'));
  }

  function activate()
  {
    $this->delete_default_post_plugins();
    $this->change_default_settings();
    $this->install_and_delete_unused_theme();
    $this->install_additional_plugins();
  }

  // Delete Hello World post and default installed plugins
  function delete_default_post_plugins()
  {
    // delete Hello World post
    wp_delete_post(1, true); // delete post=1 which is Hello World post.

    // delete Akismet plugin
    deactivate_plugins('akismet/akismet.php');
    delete_plugins(array('akismet/akismet.php'));

    // delete Hello Dolly plugin
    deactivate_plugins('hello.php');
    delete_plugins(array('hello.php'));
  }

  function change_default_settings()
  {
    // change Date Format to d/m/Y.
    update_option('date_format', 'd/m/Y');

    // change homepage displays from latest posts to Sample Page.
    $homepage = get_page_by_path('sample-page');
    update_option('page_on_front', $homepage->ID);
    update_option('show_on_front', 'page');

    // change permalink structure to post name
    update_option('permalink_structure', '\/%postname%\/');
  }

  // install Hello Elementor theme and activate it and delete unused theme
  function install_and_delete_unused_theme()
  {
    WP_Filesystem();
    global $wp_filesystem;

    $hello_elementor = wp_get_theme('hello-elementor');
    if (!$hello_elementor->exists()) {
      $hello_elementor_url = wp_remote_get('https://downloads.wordpress.org/theme/hello-elementor.2.7.1.zip');
      $destination = get_theme_root() . '/hello-elementor.zip';
      $wp_filesystem->put_contents($destination, $hello_elementor_url['body']);
      $zip = new ZipArchive;
      $res = $zip->open($destination);
      if ($res === true) {
        $zip->extractTo(get_theme_root());
        $zip->close();
        unlink($destination);
      }
    }
    $theme_name = 'hello-elementor'; // Theme name
    switch_theme($theme_name);

    // Delete unused theme
    $themes = wp_get_themes(); // Get all installed themes
    $active_theme = wp_get_theme(); // Get the active theme
    foreach ($themes as $theme) {
      if ($theme->get_stylesheet() !== $active_theme->get_stylesheet()) {
        // Delete the theme directory if it's not active
        $theme_dir = get_theme_root() . '/' . $theme->get_stylesheet();
        if (is_dir($theme_dir)) {
          $result = $wp_filesystem->delete($theme_dir, true);
          if (!$result) {
            error_log('Failed to delete theme directory: ' . $theme_dir);
          }
        }
      }
    }
  }

  // install Elementor Website Builder, Wordfence Security plugin
  function install_additional_plugins()
  {
    WP_Filesystem('direct');
    global $wp_filesystem;

    if (!file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php')) {
      $elementor_url = wp_remote_get('https://downloads.wordpress.org/plugin/elementor.3.14.1.zip');
      $wordfence_url = wp_remote_get('https://downloads.wordpress.org/plugin/wordfence.7.10.0.zip');

      $plugins_path = WP_CONTENT_DIR . '/plugins';

      $destination_elementor = $plugins_path . '/elementor.3.14.0.zip';
      $destination_wordfence = $plugins_path . '/wordfence.7.10.0.zip';

      $wp_filesystem->put_contents($destination_elementor, $elementor_url['body']);
      $wp_filesystem->put_contents($destination_wordfence, $wordfence_url['body']);

      $zip = new ZipArchive;
      $res = $zip->open($destination_elementor);
      if ($res === true) {
        $zip->extractTo($plugins_path);
        $zip->close();
        unlink($destination_elementor);
      }

      $zip = new ZipArchive;
      $res = $zip->open($destination_wordfence);
      if ($res === true) {
        $zip->extractTo($plugins_path);
        $zip->close();
        unlink($destination_wordfence);
      }
    }
  }
}

if (class_exists('DefaultInstallation')) {
  $defaultInstallation = new DefaultInstallation();
}

// what happen if plugin activate
register_activation_hook(__FILE__, array($defaultInstallation, 'activate'));
