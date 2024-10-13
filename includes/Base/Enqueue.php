<?php

namespace includes\Base;

class Enqueue extends BaseController {
    /**
     * @return void
     */
    public function register() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    /**
     * Enqueue admin scripts
     * @return void
     */
    public function enqueue() {
        if (is_admin()) {
            wp_enqueue_script('sync-s3', $this->plugin_url . 'assets/js/sync.js');
        }
    }
}