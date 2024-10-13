<?php

namespace includes\Base;

class Activate {
    /** Activates the plugin
     * @return void
     */
    public static function activate() {
        flush_rewrite_rules();
    }
}