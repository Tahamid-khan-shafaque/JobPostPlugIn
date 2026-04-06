<?php
if (!defined('ABSPATH'))
    exit;

class Job_Post_Shortcode
{
    public static function init()
    {
        add_shortcode('job_board', [__CLASS__, 'render_shortcode']);
        add_action('wp_ajax_filter_jobs', [__CLASS__, 'ajax_filter_jobs']);
        add_action('wp_ajax_nopriv_filter_jobs', [__CLASS__, 'ajax_filter_jobs']);
    }

    public static function render_shortcode($atts)
    {
        if (isset($_GET['job_id']) && intval($_GET['job_id']) > 0) {
            return self::render_single_job(intval($_GET['job_id']));
        }

        $base_url = get_permalink();
        if (!$base_url) {
            global $wp;
            $base_url = home_url(add_query_arg(array(), $wp->request));
        }

        ob_start();
        ?>
        <div class="jpm-job-board-container alignwide" id="jpm-job-board">
            <div class="jpm-sidebar">
                <form id="jpm-filter-form">
                    <h3>Filter Jobs</h3>
                    
                    <div class="jpm-filter-group">
                        <label>Search Keyword</label>
                        <input type="text" name="jpm_search" id="jpm_search" placeholder="e.g. Developer">
                    </div>

                    <div class="jpm-filter-group">
                        <label>Category</label>
                        <?php
                        $categories = get_terms(['taxonomy' => 'job_category', 'hide_empty' => false]);
                        if (!is_wp_error($categories) && !empty($categories)) {
                            echo '<select name="jpm_category" id="jpm_category"><option value="">All Categories</option>';
                            foreach ($categories as $cat) {
                                echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                            }
                            echo '</select>';
                        }
                        ?>
                    </div>

                    <div class="jpm-filter-group">
                        <label>Type</label>
                        <?php
                        $types = get_terms(['taxonomy' => 'job_type', 'hide_empty' => false]);
                        if (!is_wp_error($types) && !empty($types)) {
                            echo '<select name="jpm_type" id="jpm_type"><option value="">All Types</option>';
                            foreach ($types as $type) {
                                echo '<option value="' . esc_attr($type->term_id) . '">' . esc_html($type->name) . '</option>';
                            }
                            echo '</select>';
                        }
                        ?>
                    </div>

                    <input type="hidden" name="action" value="filter_jobs">
                    <input type="hidden" id="jpm_security" name="jpm_security"
                        value="<?php echo esc_attr(wp_create_nonce('jpm_filter_nonce')); ?>">
                    <button type="submit" class="jpm-btn jpm-btn-primary">Filter</button>
                    <button type="button" id="jpm-reset-btn" class="jpm-btn jpm-btn-primary" style="margin-top: 10px; background-color: #6b7280; color: white;">Reset</button>
                    <div class="jpm-loading" style="display:none; font-size: 14px; margin-top:10px; color:#666;">Loading...
                    </div>
                </form>
            </div>

            <div class="jpm-main-content">
                <div id="jpm-job-list" data-baseurl="<?php echo esc_url($base_url); ?>">
                    <?php self::display_jobs(1, '', '', '', $base_url); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_single_job($job_id)
    {
        $post = get_post($job_id);
        if (!$post || $post->post_type !== 'job_posting' || $post->post_status !== 'publish') {
            return '<p>Job not found or is no longer available.</p>';
        }

        ob_start();
        ?>
        <div class="jpm-single-job-container alignwide">
            <a href="javascript:history.back()" class="jpm-back-btn">&larr; Back to Jobs</a>
            
                    <div class="jpm-single-job-header">
                        <h2 class="jpm-single-job-title"><?php echo esc_html($post->post_title); ?></h2>
                        <div class="jpm-job-meta jpm-single-meta">
                            <?php
                            $types = wp_get_post_terms($post->ID, 'job_type', ['fields' => 'names']);
                            if (!empty($types)) {
                                echo '<span class="jpm-badge jpm-type">' . esc_html($types[0]) . '</span> ';
                            }
                            $cats = wp_get_post_terms($post->ID, 'job_category', ['fields' => 'names']);
                            if (!empty($cats)) {
                                echo '<span class="jpm-badge jpm-cat">' . esc_html($cats[0]) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
            
                    <div class="jpm-single-job-content">
                <?php echo apply_filters( 'the_content', $post->post_content ); ?>
            </div>
            
            <hr style="margin: 40px 0; border: 0; border-top: 1px solid #e5e7eb;">
            
            <?php echo Job_Post_Application::render_form( $post->ID ); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function display_jobs($paged = 1, $search = '', $category = '', $type = '', $base_url = '')
    {
        $args = [
            'post_type' => 'job_posting',
            'posts_per_page' => 5,
            'paged' => $paged,
            'post_status' => 'publish'
        ];

        if (!empty($search)) {
            $args['s'] = sanitize_text_field($search);
        }

        $tax_query = [];
        if (!empty($category)) {
            $tax_query[] = [
                'taxonomy' => 'job_category',
                'field' => 'term_id',
                'terms' => intval($category)
            ];
        }
        if (!empty($type)) {
            $tax_query[] = [
                'taxonomy' => 'job_type',
                'field' => 'term_id',
                'terms' => intval($type)
            ];
        }

        if (count($tax_query) > 0) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            echo '<div class="jpm-job-grid">';
            while ($query->have_posts()) {
                $query->the_post();

                $job_url = add_query_arg('job_id', get_the_ID(), $base_url);
                ?>
                                <div class="jpm-job-card" onclick="window.location.href='<?php echo esc_url($job_url); ?>'">
                                    <h4 class="jpm-job-title"><?php the_title(); ?></h4>
                                    <div class="jpm-job-meta">
                                        <?php
                                        $types = wp_get_post_terms(get_the_ID(), 'job_type', ['fields' => 'names']);
                                        if (!empty($types)) {
                                            echo '<span class="jpm-badge jpm-type">' . esc_html($types[0]) . '</span> ';
                                        }
                                        $cats = wp_get_post_terms(get_the_ID(), 'job_category', ['fields' => 'names']);
                                        if (!empty($cats)) {
                                            echo '<span class="jpm-badge jpm-cat">' . esc_html($cats[0]) . '</span>';
                                        }
                                        ?>
                                    </div>
                                    <div class="jpm-job-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                                    </div>
                                    <a href="<?php echo esc_url($job_url); ?>" class="jpm-read-more">Read More &rarr;</a>
                                </div>
                                <?php
            }
            echo '</div>';

            // Pagination
            $total_pages = $query->max_num_pages;
            if ($total_pages > 1) {
                echo '<div class="jpm-pagination">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = ($i == $paged) ? 'jpm-active' : '';
                    echo '<button class="jpm-page-btn ' . esc_attr($active) . '" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</button>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>No jobs found matching your criteria.</p>';
        }

        wp_reset_postdata();
    }

    public static function ajax_filter_jobs()
    {
        check_ajax_referer('jpm_filter_nonce', 'jpm_security');

        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : '';
        $type = isset($_POST['type']) ? intval($_POST['type']) : '';
        $base_url = isset($_POST['base_url']) ? esc_url_raw($_POST['base_url']) : '';

        self::display_jobs($paged, $search, $category, $type, $base_url);
        wp_die();
    }
}
