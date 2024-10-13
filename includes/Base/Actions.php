<?php

namespace includes\Base;

class Actions {
    /**
     * @return void
     */
    public function register() {
        add_action( 'wp_ajax_sync_old_media', array($this, 'syncOldUploads'));
        add_action( 'init', array($this, 'scheduleSyncOldUploads'));
    }

    public function scheduleSyncOldUploads() {
        // Schedule the cron event to run every 20 minutes
        if (!wp_next_scheduled('sync_old_uploads_cron')) {
            wp_schedule_event(time(), 'every_20_minutes', 'sync_old_uploads_cron', array('cron' => true));
        }

        // Hook the cron event to the syncOldUploads function
        add_action('sync_old_uploads_cron', array($this, 'syncOldUploads'));
    }

    /**
     * This handles the AJAX function for syncing old uploads
     * @param array $args
     * @return void
     */
    public function syncOldUploads($args) {
        // Retrieve the cron argument
        $cron = isset($args['cron']) ? $args['cron'] : false;

        if (!$cron && ! isset($_POST['nonce'] ) || ! wp_verify_nonce($_POST['nonce'], 'sync_old_media_nonce' )) {
            wp_send_json_error( 'Invalid nonce');
        }

        if (!Validate::validateOptions()) {
            if ($cron) {
                return;
            } else {
                wp_send_json_error(__('Please enter options for the s3 bucket', 's3-media-handler'));
            }
        }

        if (!$cron) {
            wp_send_json_success(__("Sync has started and will be done in batches", 's3-media-handler'));
        }

        // Check if there is any stored progress
        $progress = get_option('s3_media_handler_sync_old_uploads_progress', array());
        $batchSize = 20;

        // Retrieve all the old uploads from the database
        $oldAttachments = get_posts(array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => -1,
        ));

        // Calculate the total number of attachments
        $totalAttachments = count($oldAttachments);

        // Check if there is any progress stored
        if (isset($progress['offset'])) {
            $offset = $progress['offset'];
        } else {
            $offset = 0;
            $progress['offset'] = 0;
            update_option('sync_old_uploads_progress', $progress);
        }

        // Get the batch of attachments to process based on the offset
        $attachmentsToProcess = array_slice($oldAttachments, $offset, $batchSize);

        // Loop through each attachment and upload it to the S3 bucket
        foreach ($attachmentsToProcess as $oldAttachment) {
            $metadata = wp_get_attachment_metadata($oldAttachment->ID);
            $hasUploaded = get_post_meta($oldAttachment->ID, 's3_media_handler_has_file', true);
            $hasUploaded = (bool)$hasUploaded;

            if ($hasUploaded) {
                continue;
            }

            (new Settings())->redirectToS3($metadata, $oldAttachment->ID, true);
        }

        // Update the progress and reschedule if there are more attachments
        $offset += $batchSize;
        $progress['offset'] = $offset;
        update_option('s3_media_handler_sync_old_uploads_progress', $progress);

        if ($offset < $totalAttachments) {
            wp_schedule_single_event(time() + 1200, 'sync_old_uploads_cron', ['cron' => true ]);
        } else {
            // If all attachments have been processed, remove the progress
            delete_option('s3_media_handler_sync_old_uploads_progress');
        }
    }
}