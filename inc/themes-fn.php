<?php

function cdwps_install_and_delete_unused_theme()
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
add_action('admin_init', 'cdwps_install_and_delete_unused_theme');