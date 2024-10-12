<?php

namespace includes\Base;

class Validate
{
    /**
     * This validates the options before making any request to the S3 Bucket
     * @return bool
     */
    public static function validateOptions() : bool
    {
        return S3_BUCKET_NAME && S3_REGION && S3_ACCESS_KEY && S3_SECRET_KEY;
    }
}