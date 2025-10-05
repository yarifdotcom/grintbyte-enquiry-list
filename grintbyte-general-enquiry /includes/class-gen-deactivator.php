<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GEN_Deactivator {
    public static function deactivate() {
        // Reset rewrite rules
        flush_rewrite_rules();
    }
}
