<?php

namespace includes\Base;

class Deactivate
{
    /**
     * Deactivates the plugin
     * @return void
     */
    public static function deactivate() : void
    {
        flush_rewrite_rules();
    }
}