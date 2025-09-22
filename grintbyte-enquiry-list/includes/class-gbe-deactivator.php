<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GBE_Deactivator {
    public static function deactivate() {
        // Reset rewrite rules
        flush_rewrite_rules();
    }
}
