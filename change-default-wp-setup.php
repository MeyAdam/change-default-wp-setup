<?php
/*
 * Plugin Name: Change Default WP Setup
 * Plugin URI: https://github.com/MeyAdam/change-default-wp-setup
 * Description: Delete Hello World post. Delete Akismet and Hello Dolly plugin. Change Date Format to d/m/Y. Change homepage displays to a static page Sample Page. Change Permalink structure to Post name. Install Hello Elementor theme, activate it and delete unused themes. Install Elementor Website Builder plugin.
 * Version: 1.0.0
 * Author: Mey Adam
 * Author URI: https://meyadam.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/MeyAdam/change-default-wp-setup
 * Text Domain: change-default-wp-setup
 */

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

if (!class_exists('ChangeDefaultWPSetup')) {
  class ChangeDefaultWPSetup
  {
    private $completed;
    function __construct()
    {
      $this->completed = false;
      add_action('admin_init', array($this, 'delete_default_post_and_plugins'), 10);
      add_action('admin_init', array($this, 'change_default_settings'), 20);
      add_action('admin_init', array($this, 'install_and_delete_unused_theme'), 30);
      add_action('init', array($this, 'install_plugins'), 40);
    }

    function activate()
    {
      if ($this->completed) {
        return; // Stop executing if already completed
      }

      $this->delete_default_post_and_plugins();
      $this->change_default_settings();
      $this->install_and_delete_unused_theme();
      $this->install_plugins();

      $this->completed = true; // Set the completion flag
    }

    function delete_default_post_and_plugins()
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

    function change_default_settings()
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

    function install_and_delete_unused_theme()
    {
      $theme_slug = "hello-elementor";
      $theme = wp_get_theme($theme_slug);

      if (!$theme->exists()) {
        $theme_api_url = "https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=hello-elementor";

        $response = wp_remote_get($theme_api_url);
        $body = wp_remote_retrieve_body($response);
        $theme_info = json_decode($body, true);

        if ($theme_info && isset($theme_info['download_link'])) {
          $download_link = $theme_info['download_link'];

          $zip_path = get_theme_root() . '/' . basename($download_link);
          $zip_file = file_get_contents($download_link);
          file_put_contents($zip_path, $zip_file);

          $zip = new ZipArchive;
          if ($zip->open($zip_path) === true) {
            $zip->extractTo(get_theme_root());
            $zip->close();
            unlink($zip_path);
          }
        }
      }

      // if activated theme is not Hello Elementor, change to Hello Elementor
      if (get_stylesheet() !== $theme_slug) {
        switch_theme($theme_slug); // activate Hello Elementor theme
      }

      $active_theme = wp_get_theme();
      $themes = wp_get_themes();
      foreach ($themes as $theme) {
        if ($theme->get_stylesheet() !== $active_theme->get_stylesheet()) {
          $theme_dir = get_theme_root() . '/' . $theme->get_stylesheet();
          if (is_dir($theme_dir)) {
            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->delete($theme_dir, true);
          }
        }
      }
    }

    function install_plugins()
    {
      $elementor_api_url = "https://api.wordpress.org/plugins/info/1.0/elementor.json";

      $response = wp_remote_get($elementor_api_url);
      $body = wp_remote_retrieve_body($response);
      $elementor_info = json_decode($body, true);

      if ($elementor_info && isset($elementor_info['download_link'])) {
        $download_link = $elementor_info['download_link'];
        $file_name = basename($download_link);
        $zip_path = plugin_dir_path(__FILE__) . $file_name;

        $elementor_dir = plugin_dir_path(__FILE__) . "elementor";

        if (!is_dir($elementor_dir)) {
          $zip_file = file_get_contents($download_link);
          file_put_contents($zip_path, $zip_file);

          $zip = new ZipArchive;
          if ($zip->open($zip_path) === true) {
            $zip->extractTo(plugin_dir_path(__FILE__));
            $zip->close();
            unlink($zip_path);
          }
        }
      }
    }
  }

  $changeDefaultWPSetup = new ChangeDefaultWPSetup();

  register_activation_hook(__FILE__, array($changeDefaultWPSetup, 'activate'));
}