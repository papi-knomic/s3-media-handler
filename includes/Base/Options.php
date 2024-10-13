<?php

namespace includes\Base;

class Options {
    /**
     * @return void
     */
    public function register() {
        add_action('admin_init', array($this, 'registerSettings'));
    }

    /**
     * @return void
     */
    public function registerSettings() {
        register_setting(PLUGIN_OPTION_GROUP, 's3_bucket_name');
        register_setting(PLUGIN_OPTION_GROUP, 's3_region');
        register_setting(PLUGIN_OPTION_GROUP, 's3_access_key');
        register_setting(PLUGIN_OPTION_GROUP, 's3_secret_key');

        add_settings_section(
            's3_options_section',
            'S3 Options',
            array($this, 'optionsSectionCallback'),
            's3-media-handler-settings'
        );

        add_settings_field(
            's3_media_handler_bucket_name',
            'S3 Bucket Name',
            array($this, 'bucketNameOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_region',
            'S3 Region',
            array($this, 'regionOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_access_key',
            'S3 Access Key',
            array($this, 'accessKeyOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_secret_key',
            'S3 Secret Key',
            array($this, 'secretKeyOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

    }

    /**
     * The Options section callback
     * @return void
     */
    public function optionsSectionCallback() {
        echo 'Enter AWS S3 options below:';
    }

    /**
     * Bucket name field
     * @return void
     */
    public function bucketNameOption() {
        $option = S3_BUCKET_NAME;
        echo '<input type="text" name="s3_bucket_name" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Region field
     * @return void
     */
    public function regionOption() {
        $option = S3_REGION;
        echo '<input type="text" name="s3_region" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Access key field
     * @return void
     */
    public function accessKeyOption() {
        $option = S3_ACCESS_KEY;
        echo '<input type="password" name="s3_access_key" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Secret key field
     * @return void
     */
    public function secretKeyOption() {
        $option = S3_SECRET_KEY;
        echo '<input type="password" name="s3_secret_key" value="' . esc_attr($option) . '" required/>';
    }
}