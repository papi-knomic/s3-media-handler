<?php

namespace includes\Base;

use Aws\S3\S3Client;
use Exception;

class Upload {
    /**
     * This uploads the file to s3Bucket
     * @param $filePath
     * @param $s3Key
     * @return mixed
     */
    public static function uploadFile($filePath, $s3Key) {
        if (!Validate::validateOptions()) {
            return array(
                'status' => false,
                'message' => __('Please set options for S3 Bucket', 's3-media-handler'),
            );
        }

        $s3 = new S3Client(array(
            'version' => 'latest',
            'region' => S3MH_REGION,
            'credentials' => array(
                'key' => S3MH_ACCESS_KEY,
                'secret' => S3MH_SECRET_KEY
            )
        ));

        if ($s3->doesObjectExist(S3MH_BUCKET_NAME, $s3Key)) {
            // The file already exists in the S3 bucket, skip the upload
            return array(
                'status' => false,
                'message' => __('Object already exists in S3 bucket', 's3-media-handler'),
            );
        }

    // Check if the file exists before attempting to upload to S3
        if (file_exists($filePath)) {
            try {
                $result = $s3->putObject(array(
                    'Bucket' => S3MH_BUCKET_NAME,
                    'Key' => $s3Key,
                    'SourceFile' => $filePath
                ));

                // Return the URL of the uploaded file
                return $result['ObjectURL'] ?: '';
            } catch (Exception $e) {
                // Handle the exception
	            $message = __('S3 upload failed:', 's3-media-handler') . ' ' . $e->getMessage();
                error_log($message);
                return array(
		            'status' => false,
		            'message' => $message
	            ); // Or you can return an error message or take appropriate action
            }
        } else {
            // File does not exist
	        $message = __('File not found:', 's3-media-handler') . ' ' . $filePath;
            error_log($message);
            return array(
				'status' => false,
	            'message' => $message
            ); // Or you can return an error message or take appropriate action
        }
    }
}