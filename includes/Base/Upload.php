<?php

namespace includes\Base;

use Aws\S3\S3Client;
use Exception;

class Upload
{
    /**
     * This uploads the file to s3Bucket
     * @param $filePath
     * @param $s3Key
     * @return mixed
     */
    public static function uploadFile( $filePath, $s3Key ): mixed
    {
        if ( !Validate::validateOptions() ) {
            return [
                'status' => false,
                'message' => 'Please set options for S3 Bucket',
            ];
        }

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => S3_REGION,
            'credentials' => [
                'key' => S3_ACCESS_KEY,
                'secret' => S3_SECRET_KEY
            ]
        ]);

        if ( $s3->doesObjectExist(S3_BUCKET_NAME, $s3Key ) ) {
            // The file already exists in the S3 bucket, skip the upload
            return [
                'status' => false,
                'message' => 'Object already exists',
            ];
        }

    // Check if the file exists before attempting to upload to S3
        if ( file_exists( $filePath ) ) {
            try {
                $result = $s3->putObject([
                    'Bucket' => S3_BUCKET_NAME,
                    'Key' => $s3Key,
                    'SourceFile' => $filePath
                ]);

                // Return the URL of the uploaded file
                return $result['ObjectURL'] ?? '';
            } catch ( Exception $e ) {
                // Handle the exception
                error_log('S3 upload failed: ' . $e->getMessage() );
                return ''; // Or you can return an error message or take appropriate action
            }
        } else {
            // File does not exist
            error_log('File not found: ' . $filePath );
            return ''; // Or you can return an error message or take appropriate action
        }
    }
}