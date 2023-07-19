<?php
/*
 * Plugin Name: Change Default WP Setup
 * Plugin URI: https://github.com/MeyAdam/change-default-wp-setup
 * Description: Delete Hello World post. Delete Akismet and Hello Dolly plugin. Change Date Format to d/m/Y. Change homepage displays to a static page Sample Page. Change Permalink structure to Post name. Install Hello Elementor theme, activate it and delete unused themes. Install Elementor Website Builder plugin.
 * Version: 1.0.1
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
    function __construct()
    {
      define('PLUGIN_PATH', plugin_dir_path(__FILE__));
      define('PLUGIN_DIR', ABSPATH . PLUGINDIR);
    }

    function init()
    {
      require_once(PLUGIN_PATH . 'inc/change-settings.php');
      require_once(PLUGIN_PATH . 'inc/themes-fn.php');
      require_once(PLUGIN_PATH . 'inc/plugins-fn.php');
    }
  }

  $cdwps = new ChangeDefaultWPSetup();
  $cdwps->init();
}