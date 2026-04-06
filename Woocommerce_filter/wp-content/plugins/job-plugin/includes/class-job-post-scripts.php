<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Job_Post_Scripts {
    public static function init() {
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function enqueue_assets() {
        wp_enqueue_style( 'jpm-style', JOB_POST_MANAGER_URL . 'assets/style.css', [], JOB_POST_MANAGER_VERSION );
        wp_enqueue_script( 'jpm-script', JOB_POST_MANAGER_URL . 'assets/script.js', ['jquery'], JOB_POST_MANAGER_VERSION, true );
        
        wp_localize_script( 'jpm-script', 'jpm_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ] );
    }
}
