<?php

namespace includes\Base;

class Settings
{

    public function register() : void
    {
        add_filter( 'wp_generate_attachment_metadata', [$this, 'redirectToS3'], 10, 2 );
        add_filter( 'wp_get_attachment_url', [$this, 'replaceUrl'], 10, 2 );
    }

    /**
     * This catches an upload and reroutes to the s3bucket
     * @param $metadata
     * @param int $attachmentID
     * @param bool $sync
     * @return bool
     */
    public function redirectToS3( $metadata, int $attachmentID, bool $sync = false ) : bool
    {

        // Check and rename attachment if necessary
        $attachmentID = $this->checkAndRenameAttachment( $attachmentID );
        $mimeType = get_post_mime_type( $attachmentID );

        // Get attachment file path
        $filePath = get_attached_file( $attachmentID );

        // Generate unique S3 key based on attachment date and file name
        $attachmentDate = get_post_field( 'post_date', $attachmentID );
        $s3Key = date( 'Y/m', strtotime( $attachmentDate ) ) . '/' . basename( $filePath );

        // Upload original file to S3
        $upload = $this->s3HandleUpload( $attachmentID, $filePath, $s3Key );
        $uploadDir = wp_get_upload_dir();
        $uploadPath = $uploadDir['basedir'];

        if ( $upload ) {
            update_post_meta( $attachmentID, 's3_media_handler_has_file', true );
        }

        if ( $metadata && str_starts_with( $mimeType, 'image/' ) ) {
            // Loop through resized versions of the image and upload each one to S3
            foreach ( $metadata['sizes'] as $sizeInfo ) {
                if ( !is_array( $sizeInfo ) || empty( $sizeInfo ) ) {
                    continue;
                }
                $sizeFilePath = $sizeInfo['file'];
                $sizeS3Key = dirname( $s3Key ) . '/' . $sizeFilePath;
                $path = $uploadPath ."/" . $sizeS3Key;
                $this->s3HandleUpload( $attachmentID, $path, $sizeS3Key );
            }
        }

        if ( $sync ) {
            return $upload;
        }

        // Redirect to the attachment URL on S3
        $bucketName = S3_BUCKET_NAME;
        $s3Url = "https://$bucketName.s3.amazonaws.com/$s3Key";
        wp_redirect( $s3Url );
        exit;
    }

    /**
     * Uploads the file to the bucket and deletes the file in the upload
     * @param int $attachmentID
     * @param string $filePath
     * @param string $s3Key
     * @return bool
     */
    public function s3HandleUpload( int $attachmentID, string $filePath, string $s3Key ) : bool
    {
        $upload = Upload::uploadFile( $filePath, $s3Key );

        if (  empty( $upload )  || isset( $upload['status'] ) && !$upload['status'] ) {
            return false;
        }

        // Update attachment URL in database
        if ( $s3Key === basename( $filePath ) ) {
            // If this is the original file, update the 'guid' field in the database
            $guid = $upload;
            wp_update_attachment_metadata( $attachmentID, ['file' => $guid] );
        } else {
            // If this is a resized version of the image, add the S3 URL to the 'sizes' array in the metadata
            $metadata = wp_get_attachment_metadata( $attachmentID );
            $metadata['sizes'][ $s3Key ] = $upload;
            wp_update_attachment_metadata( $attachmentID, $metadata );
        }

        // Delete the WordPress file
        unlink( $filePath );
        return true;
    }

    /**
     * Replaces the home_url() with the s3bucket url
     * @param $url
     * @param $attachmentID
     * @return string
     */
    public function replaceUrl( $url, $attachmentID ) : string
    {
        $hasUploaded = get_post_meta( $attachmentID, 's3_media_handler_has_file', true);
        $hasUploaded = (bool)$hasUploaded;
        // if plugin options are not available return WordPress url or file has not been uploaded to S3
        if ( ! Validate::validateOptions() || !$hasUploaded ){
            return $url;
        }

        if ( str_contains( $url, site_url() ) ) {
            $s3Url = $this->getS3AttachmentUrl( $attachmentID );

            // Replace the WordPress URL with the S3 bucket object URL
            if ( $s3Url ) {
                $url = $s3Url;
            }
        }

        return $url;
    }

    /**
     * Get the S3 bucket object URL for an attachment.
     *
     * @param int $attachmentID The ID of the attachment.
     * @return string|false The S3 bucket object URL, or false on failure.
     */
    private function getS3AttachmentUrl( int $attachmentID ): bool|string
    {
        $metadata = wp_get_attachment_metadata( $attachmentID );
        $bucketName = S3_BUCKET_NAME;

        if ( isset( $metadata['file'] ) ) {
            // Get the S3 bucket URL
            $s3BucketUrl = "https://$bucketName.s3.amazonaws.com/";

            // Retrieve the attachment file path relative to the uploads directory
            $attachment_path = dirname( $metadata['file'] ) . '/' . wp_basename( $metadata['file'] );

            // Return the S3 bucket object URL
            return $s3BucketUrl . $attachment_path;
        }

        return false;
    }

    public function checkAndRenameAttachment( int $attachmentID ): int
    {
        $attachment = get_post( $attachmentID );
        $attachment_title = $attachment->post_title;
        $mimeType = get_post_mime_type( $attachmentID );

        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';

        $similar_attachments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title, post_mime_type
            FROM $table_name
            WHERE post_title LIKE %s
            AND post_mime_type = %s
            AND ID != %d",  // Exclude the current attachment ID
                $attachment_title . '%',
                $mimeType,
                $attachmentID
            )
        );

        if ( count( $similar_attachments ) ) {

            $suffix = count( $similar_attachments ) + 1;

            // Rename the new attachment
            $new_title = $attachment_title . '_' . $suffix;
            $wpdb->update(
                $table_name,
                ['post_title' => $new_title],
                ['ID' => $attachmentID]
            );
            $metadata = wp_get_attachment_metadata( $attachmentID );

            // Rename the file if it's an image
            if ( str_starts_with( $mimeType, 'image/' ) && !empty( $metadata ) ) {
                $metadata = wp_get_attachment_metadata( $attachmentID );
                foreach ( $metadata['sizes'] as $sizeInfo ) {
                    if ( !is_array( $sizeInfo ) || empty( $sizeInfo ) ) {
                        continue;
                    }
                    $sizeFile = $sizeInfo['file'];
                    $newSizeFile = $this->generateResizedImageName( $sizeFile );
                    $sizeInfo['file'] = $newSizeFile;
                }
                wp_update_attachment_metadata( $attachmentID, $metadata );
            }
        }

        return $attachmentID;
    }

    private function generateResizedImageName( string $sizeFile ): string
    {
        $info = pathinfo( $sizeFile );
        return $info['filename'] . '.' . $info['extension'];
    }
}