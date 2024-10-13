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
        register_setting(PLUGIN_OPTION_GROUP, 's3mh_bucket_name');
        register_setting(PLUGIN_OPTION_GROUP, 's3mh_region');
        register_setting(PLUGIN_OPTION_GROUP, 's3mh_access_key');
        register_setting(PLUGIN_OPTION_GROUP, 's3mh_secret_key');

        add_settings_section(
            's3_options_section',
            __('S3 Options', 's3-media-handler'),
            array($this, 'optionsSectionCallback'),
            's3-media-handler-settings'
        );

        add_settings_field(
            's3_media_handler_activate_action',
            __('S3 Activation', 's3-media-handler'),
            array($this, 'activationCheckboxOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

		add_settings_field(
            's3_media_handler_bucket_name',
            __('S3 Bucket Name', 's3-media-handler'),
            array($this, 'bucketNameOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_region',
            __('S3 Region', 's3-media-handler'),
            array($this, 'regionOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_access_key',
            __('S3 Access Key', 's3-media-handler'),
            array($this, 'accessKeyOption'),
            's3-media-handler-settings',
            's3_options_section'
        );

        add_settings_field(
            's3_media_handler_secret_key',
            __('S3 Secret Key', 's3-media-handler'),
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
        echo __('Enter AWS S3 options below:', 's3-media-handler');
    }

    /**
     * Bucket name field
     * @return void
     */
    public function activationCheckboxOption() {
        $option = S3MH_BUCKET_NAME;
        echo '<input type="text" name="s3mh_bucket_name" value="' . esc_attr($option) . '" required/>';
    }

	/**
     * Bucket name field
     * @return void
     */
    public function bucketNameOption() {
        $option = S3MH_BUCKET_NAME;
        echo '<input type="text" name="s3mh_bucket_name" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Region field
     * @return void
     */
    public function regionOption() {
        $option = S3MH_REGION;
        echo '<input type="text" name="s3mh_region" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Access key field
     * @return void
     */
    public function accessKeyOption() {
        $option = S3MH_ACCESS_KEY;
        echo '<input type="password" name="s3mh_access_key" value="' . esc_attr($option) . '" required/>';
    }

    /**
     * Secret key field
     * @return void
     */
    public function secretKeyOption() {
        $option = S3MH_SECRET_KEY;
        echo '<input type="password" name="s3mh_secret_key" value="' . esc_attr($option) . '" required/>';
    }
}