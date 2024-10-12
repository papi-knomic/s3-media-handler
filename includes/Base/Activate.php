<?php

namespace includes\Base;

class Activate
{
    /** Activates the plugin
     * @return void
     */
    public static function activate() : void
    {
        flush_rewrite_rules();
    }
}