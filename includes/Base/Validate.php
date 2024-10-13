<?php

namespace includes\Base;

class Validate {
    /**
     * This validates the options before making any request to the S3 Bucket
     * @return bool
     */
    public static function validateOptions() {
        return S3MH_BUCKET_NAME && S3MH_REGION && S3MH_ACCESS_KEY && S3MH_SECRET_KEY;
    }
}