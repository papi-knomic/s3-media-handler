<?php

namespace includes\Base;

class Deactivate {
    /**
     * Deactivates the plugin
     * @return void
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}