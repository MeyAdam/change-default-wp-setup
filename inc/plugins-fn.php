<?php
function cdwps_install_plugins()
{
  $elementor_api_url = "https://api.wordpress.org/plugins/info/1.0/elementor.json";

  $response = wp_remote_get($elementor_api_url);
  $body = wp_remote_retrieve_body($response);
  $elementor_info = json_decode($body, true);

  if ($elementor_info && isset($elementor_info['download_link'])) {
    $download_link = $elementor_info['download_link'];
    $file_name = basename($download_link);
    $zip_path = PLUGIN_DIR . '/' . $file_name;

    $elementor_dir = PLUGIN_DIR . "/elementor";

    if (!is_dir($elementor_dir)) {
      $zip_file = file_get_contents($download_link);
      file_put_contents($zip_path, $zip_file);

      $zip = new ZipArchive;
      if ($zip->open($zip_path) === true) {
        $zip->extractTo(PLUGIN_DIR);
        $zip->close();
        unlink($zip_path);
      }
    }
  }
}
add_action('admin_init', 'cdwps_install_plugins');