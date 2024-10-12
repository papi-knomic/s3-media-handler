<?php

/**
 * Plugin Name: S3 Media Handler
 * Plugin URI: https://github.com/papi-knomic
 * Description: Plugin for handling file uploads
 * Author: Samson Moses
 * Author URI: https://twitter.com/kn0mic
 * Version: 1.0
 * Text Domain: s3-media-handler
 */

//Stops file from being called directly
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

$bucket_name = get_option('s3_bucket_name');
$region = get_option('s3_region');
$access_key = get_option('s3_access_key');
$secret_key = get_option('s3_secret_key');

define('S3_MEDIA_HANDLER_VERSION', '1.0.0');
define('PLUGIN_OPTION_GROUP', 's3MediaHandlerOptions');
define('S3_BUCKET_NAME', $bucket_name);
define('S3_REGION', $region );
define('S3_ACCESS_KEY', $access_key);
define('S3_SECRET_KEY', $secret_key);



// Require once for the composer autoload
if (file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __FILE__) . '/vendor/autoload.php';
}

/*
 * Plugin activation
 */
function activate_s3_media_handler() {
    \includes\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_s3_media_handler');

/*
 * Plugin deactivation
 */
function deactivate_s3_media_handler() {
    \includes\Base\Deactivate::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_s3_media_handler');

if ( class_exists( 'includes\\init' ) ) {
    includes\Init::registerServices();
}
