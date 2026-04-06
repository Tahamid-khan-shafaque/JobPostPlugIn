<?php
/**
 * Plugin Name: Job Post Manager
 * Description: Simple job posting plugin with shortcode, filtering, and pagination.
 * Version: 1.0
 * Author: Tahamid Khan
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'JOB_POST_MANAGER_VERSION', '1.0' );
define( 'JOB_POST_MANAGER_URL', plugin_dir_url( __FILE__ ) );
define( 'JOB_POST_MANAGER_PATH', plugin_dir_path( __FILE__ ) );

// Include main functionality classes/functions
require_once plugin_dir_path( __FILE__ ) . 'includes/class-job-post-cpt.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-job-post-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-job-post-application.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-job-post-scripts.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-job-post-settings.php';

// Initialize the plugin
function job_post_manager_init() {
    Job_Post_CPT::init();
    Job_Post_Shortcode::init();
    Job_Post_Application::init();
    Job_Post_Scripts::init();
    Job_Post_Settings::init();
}
add_action( 'plugins_loaded', 'job_post_manager_init' );
