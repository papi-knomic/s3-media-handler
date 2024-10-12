<?php

namespace includes\Base;

class BaseController {

    public string $plugin_path;
    public string $plugin_url;
    public string $plugin;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
        $this->plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );
        $this->plugin = plugin_basename( dirname( __FILE__, 3 ) ) .  '/s3-media-handler.php';
    }

}