<?php

namespace includes\Base;

class Admin extends BaseController {
    private $pluginOptionGroup = 's3MediaHandlerOptions';

    /**
     * @return void
     */
    public function register() {
        add_action('admin_menu', array($this, 'optionsPage'));
    }

    /**
     * @return void
     */
    public function optionsPage() {
        add_menu_page('S3 Media Handler', 'S3 Media Handler', 'manage_options', 's3-media-handler-settings', [$this,'optionsPageContent'], [], 110 );
    }

    /**
     * @return void
     */
    public function optionsPageContent() {
        if (isset($_POST['submit'])) {
            // Handle form submission here
            $accessKey = sanitize_text_field($_POST['s3_access_key']);
            $secretKey = sanitize_text_field($_POST['s3_secret_key']);
            $bucketName = sanitize_text_field($_POST['s3_bucket_name']);
            $region = sanitize_text_field($_POST['s3_region']);


            update_option('s3_access_key', $accessKey);
            update_option('s3_secret_key', $secretKey);
            update_option('s3_bucket_name', $bucketName);
            update_option('s3_region', $region);

            echo '<div class="notice notice-success is-dismissible"><p>Options saved.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Sync Old Media</h1>
            <form method="post" action="" id="sync-form">
                <?php wp_nonce_field('sync_old_media_nonce', 'sync_old_media_nonce'); ?>
                <?php submit_button('Sync'); ?>
            </form>
        </div>
        <div class="wrap">
            <h1>S3 Media Handler Options</h1>
            <form method="post" action="">
                <?php settings_fields($this->pluginOptionGroup); ?>
                <?php do_settings_sections('s3-media-handler-settings'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}