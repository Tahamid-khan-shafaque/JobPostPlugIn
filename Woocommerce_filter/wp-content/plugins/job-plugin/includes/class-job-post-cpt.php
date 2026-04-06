<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Job_Post_CPT {
    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'init', [ __CLASS__, 'register_taxonomies' ] );
    }

    public static function register_post_type() {
        $args = [
            'labels'      => [
                'name'               => 'Job Posts',
                'singular_name'      => 'Job Post',
                'add_new'            => 'Add New Job',
                'add_new_item'       => 'Add New Job Post',
                'edit_item'          => 'Edit Job Post',
                'new_item'           => 'New Job Post',
                'view_item'          => 'View Job Post',
                'search_items'       => 'Search Job Posts',
                'not_found'          => 'No job posts found',
            ],
            'public'      => true,
            'has_archive' => true,
            'supports'    => [ 'title', 'editor' ],
            'menu_icon'   => 'dashicons-businessman',
            'rewrite'     => [ 'slug' => 'jobs' ],
        ];
        register_post_type( 'job_posting', $args );

        // Applicant Form Submission CPT
        $app_args = [
            'labels'      => [
                'name'               => 'Applications',
                'singular_name'      => 'Application',
                'view_item'          => 'View Application',
                'search_items'       => 'Search Applications',
                'not_found'          => 'No applications found',
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=job_posting',
            'supports'           => [ 'title' ],
            'capabilities'       => [
                'create_posts' => 'do_not_allow', // Prevents Admins from manually creating apps
            ],
            'map_meta_cap'       => true,
        ];
        register_post_type( 'job_application', $app_args );
    }

    public static function register_taxonomies() {
        register_taxonomy( 'job_category', 'job_posting', [
            'labels'       => [
                'name'          => 'Job Categories',
                'singular_name' => 'Job Category',
            ],
            'hierarchical' => true,
            'show_admin_column' => true,
        ] );

        register_taxonomy( 'job_type', 'job_posting', [
            'labels'       => [
                'name'          => 'Job Types',
                'singular_name' => 'Job Type',
            ],
            'hierarchical' => true,
            'show_admin_column' => true,
        ] );
    }
}
