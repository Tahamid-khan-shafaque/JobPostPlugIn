<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Job_Post_Application {

    public static function init() {
        add_action( 'wp_ajax_submit_job_application', [ __CLASS__, 'handle_submission' ] );
        add_action( 'wp_ajax_nopriv_submit_job_application', [ __CLASS__, 'handle_submission' ] );
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_box' ] );
        add_filter( 'manage_job_application_posts_columns', [ __CLASS__, 'set_custom_columns' ] );
        add_action( 'manage_job_application_posts_custom_column', [ __CLASS__, 'custom_column_data' ], 10, 2 );
    }

    public static function add_meta_box() {
        add_meta_box( 'job_app_details', 'Application Details', [ __CLASS__, 'render_meta_box' ], 'job_application', 'normal', 'high' );
    }

    public static function render_meta_box( $post ) {
        $email = get_post_meta( $post->ID, '_jpm_email', true );
        $phone = get_post_meta( $post->ID, '_jpm_phone', true );
        $message = get_post_meta( $post->ID, '_jpm_message', true );
        $job_id = get_post_meta( $post->ID, '_jpm_job_id', true );
        $cv_url = get_post_meta( $post->ID, '_jpm_cv_url', true );

        echo '<table class="form-table">';
        
        if ( $job_id ) {
            echo '<tr><th scope="row">Applied For</th><td><a href="' . esc_url( get_edit_post_link( $job_id ) ) . '"><strong>' . esc_html( get_the_title( $job_id ) ) . '</strong></a></td></tr>';
        }
        
        echo '<tr><th scope="row">Email Address</th><td><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></td></tr>';
        
        echo '<tr><th scope="row">Phone Number</th><td>' . esc_html( $phone ) . '</td></tr>';

        $custom_data = get_post_meta( $post->ID, '_jpm_custom_fields', true );
        if ( is_array( $custom_data ) && ! empty( $custom_data ) ) {
            foreach ( $custom_data as $label => $val ) {
                echo '<tr><th scope="row">' . esc_html($label) . '</th><td><div style="background: #f9f9f9; padding: 10px; border: 1px solid #ddd; max-width: 600px;">' . nl2br(esc_html($val)) . '</div></td></tr>';
            }
        }

        if ( $cv_url ) {
            echo '<tr><th scope="row">Resume / CV</th><td><a href="' . esc_url( $cv_url ) . '" target="_blank" class="button button-primary">Download Attached File</a></td></tr>';
        }

        echo '<tr><th scope="row">Cover Letter / Message</th><td><div class="jpm-admin-message-box" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; display: inline-block; max-width: 600px;">' . nl2br( esc_html( $message ) ) . '</div></td></tr>';
        
        echo '</table>';
    }

    public static function set_custom_columns( $columns ) {
        $columns['job_applied'] = 'Job Applied For';
        $columns['app_email'] = 'Email';
        $columns['app_cv'] = 'Resume/CV';
        $columns['app_date'] = 'Date Applied';
        unset( $columns['date'] );
        return $columns;
    }

    public static function custom_column_data( $column, $post_id ) {
        switch ( $column ) {
            case 'job_applied':
                $job_id = get_post_meta( $post_id, '_jpm_job_id', true );
                if ( $job_id ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $job_id ) ) . '"><strong>' . esc_html( get_the_title( $job_id ) ) . '</strong></a>';
                } else {
                    echo 'N/A';
                }
                break;
            case 'app_email':
                $email = get_post_meta( $post_id, '_jpm_email', true );
                echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
                break;
            case 'app_cv':
                $cv_url = get_post_meta( $post_id, '_jpm_cv_url', true );
                if ( $cv_url ) {
                    echo '<a href="' . esc_url( $cv_url ) . '" target="_blank">View File</a>';
                } else {
                    echo 'None';
                }
                break;
            case 'app_date':
                echo get_the_date( '', $post_id );
                break;
        }
    }

    public static function handle_submission() {
        check_ajax_referer( 'jpm_apply_nonce', 'security' );

        $job_id  = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
        $name    = isset( $_POST['app_name'] ) ? sanitize_text_field( wp_unslash( $_POST['app_name'] ) ) : '';
        $email   = isset( $_POST['app_email'] ) ? sanitize_email( wp_unslash( $_POST['app_email'] ) ) : '';
        $phone   = isset( $_POST['app_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['app_phone'] ) ) : '';
        $message = isset( $_POST['app_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['app_message'] ) ) : '';

        if ( ! $job_id || empty( $name ) || empty( $email ) ) {
            wp_send_json_error( 'Please fill out all required fields.' );
        }

        if ( ! is_email( $email ) ) {
            wp_send_json_error( 'Please enter a valid email address.' );
        }

        // Handle File Upload securely if exists
        $cv_upload_url = '';
        if ( ! empty( $_FILES['app_cv']['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            $uploaded_file = $_FILES['app_cv'];
            
            $file_type = wp_check_filetype( basename( $uploaded_file['name'] ), [ 
                'pdf'  => 'application/pdf', 
                'doc'  => 'application/msword', 
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' 
            ] );
            if ( empty( $file_type['ext'] ) ) {
                wp_send_json_error( 'Only PDF, DOC, or DOCX files are permitted for CV/Resumes.' );
            }

            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

            if ( $movefile && ! isset( $movefile['error'] ) ) {
                $cv_upload_url = $movefile['url'];
            } else {
                wp_send_json_error( 'Failed to upload CV file: ' . esc_html( $movefile['error'] ) );
            }
        } else {
            wp_send_json_error( 'Please attach your CV/Resume file.' );
        }

        $job_title = get_the_title( $job_id );

        // Create the application post securely
        $post_id = wp_insert_post( [
            'post_title'   => sanitize_text_field( sprintf( '%s (Applied for %s)', $name, $job_title ) ),
            'post_type'    => 'job_application',
            'post_status'  => 'publish',
        ] );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( 'There was an error submitting your application. Please try again later.' );
        }

        // Save cleanly sanitized meta data
        update_post_meta( $post_id, '_jpm_job_id', $job_id );
        update_post_meta( $post_id, '_jpm_name', $name );
        update_post_meta( $post_id, '_jpm_email', $email );
        update_post_meta( $post_id, '_jpm_phone', $phone );

        $dynamic_fields = get_option('jpm_dynamic_fields', []);
        $custom_data = [];
        if (is_array($dynamic_fields)) {
            foreach ($dynamic_fields as $field) {
                $safe_label = esc_html($field['label']);
                $safe_name = 'custom_' . md5($safe_label);
                if (isset($_POST[$safe_name])) {
                    if (isset($field['type']) && $field['type'] === 'textarea') {
                        $custom_data[$safe_label] = sanitize_textarea_field($_POST[$safe_name]);
                    } else {
                        $custom_data[$safe_label] = sanitize_text_field($_POST[$safe_name]);
                    }
                }
            }
        }
        update_post_meta( $post_id, '_jpm_custom_fields', $custom_data );
        update_post_meta( $post_id, '_jpm_message', $message );
        if ( $cv_upload_url ) {
            update_post_meta( $post_id, '_jpm_cv_url', esc_url_raw( $cv_upload_url ) );
        }

        wp_send_json_success( 'Your application and CV have been safely received! We will be in touch.' );
    }


    public static function render_form( $job_id ) {
        ob_start();
        ?>
        <div class="jpm-application-wrapper">
            <h3>Want to Apply?</h3>
            <p>Please fill out the form below to submit your application for this position.</p>
            <form id="jpm-application-form" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?php echo intval( $job_id ); ?>">
                <input type="hidden" name="action" value="submit_job_application">
                <input type="hidden" id="jpm_apply_security" name="security" value="<?php echo esc_attr( wp_create_nonce( 'jpm_apply_nonce' ) ); ?>">

                <?php 
                $dynamic_fields = get_option('jpm_dynamic_fields', []);
                if (is_array($dynamic_fields) && !empty($dynamic_fields)) :
                    $count = 0;
                    echo '<div class="jpm-form-row">';
                    
                    foreach ($dynamic_fields as $field) :
                        $system_key = isset($field['system_key']) ? $field['system_key'] : '';
                        $label = esc_html($field['label']);
                        $req_star = !empty($field['required']) ? '<span class="jpm-required">*</span>' : '';
                        $req_attr = !empty($field['required']) ? 'required' : '';
                        
                        // Handle full-width locked components
                        if ($system_key === 'core_cv') {
                            if ($count % 2 !== 0) {
                                echo '<div class="jpm-filter-group jpm-app-group jpm-desktop-spacer"></div></div><div class="jpm-form-row">';
                                $count++;
                            }
                            ?>
                            <div class="jpm-filter-group jpm-app-group jpm-file-group">
                                <label><?php echo $label; ?> <span class="jpm-required">*</span></label>
                                <div class="jpm-custom-file-upload">
                                    <input type="file" name="app_cv" id="jpm_app_cv" accept=".pdf,.doc,.docx" required>
                                    <label for="jpm_app_cv" class="jpm-file-label">
                                        <span class="jpm-file-text"><strong>Click to browse</strong> or drag and drop a file</span>
                                        <span class="jpm-file-hint" style="display:block; font-size:0.85rem; color:#94a3b8; margin-top:8px;">Maximum file size: 10MB (PDF, DOC, DOCX)</span>
                                    </label>
                                </div>
                            </div>
                            <?php
                            $count += 2;
                            continue;
                        }

                        if ($system_key === 'core_message') {
                            if ($count % 2 !== 0) {
                                echo '<div class="jpm-filter-group jpm-app-group jpm-desktop-spacer"></div></div><div class="jpm-form-row">';
                                $count++;
                            }
                            ?>
                            <div class="jpm-filter-group jpm-app-group">
                                <label><?php echo $label; ?> <span class="jpm-required">*</span></label>
                                <textarea name="app_message" rows="5" required></textarea>
                            </div>
                            <?php
                            $count += 2;
                            continue;
                        }

                        // Handle standard 50% grid items dynamically sequentially
                        if ($count > 0 && $count % 2 == 0) {
                            echo '</div><div class="jpm-form-row">';
                        }

                        $name_attr = '';
                        if ($system_key === 'core_name') $name_attr = 'name="app_name"';
                        elseif ($system_key === 'core_email') $name_attr = 'name="app_email"';
                        elseif ($system_key === 'core_phone') $name_attr = 'name="app_phone"';
                        else $name_attr = 'name="custom_' . md5($label) . '"';
                        ?>
                        <div class="jpm-filter-group jpm-app-group">
                            <label><?php echo $label . ' ' . $req_star; ?></label>
                            <?php if (isset($field['type']) && $field['type'] === 'textarea') : ?>
                                <textarea <?php echo $name_attr; ?> <?php echo $req_attr; ?> rows="3"></textarea>
                            <?php elseif (isset($field['type']) && $field['type'] === 'email') : ?>
                                <input type="email" <?php echo $name_attr; ?> <?php echo $req_attr; ?>>
                            <?php else : ?>
                                <input type="text" <?php echo $name_attr; ?> <?php echo $req_attr; ?>>
                            <?php endif; ?>
                        </div>
                        <?php
                        $count++;
                    endforeach;
                    
                    if ($count % 2 !== 0) {
                        echo '<div class="jpm-filter-group jpm-app-group jpm-desktop-spacer"></div>';
                    }
                    echo '</div>';
                endif; 
                ?>

                <button type="submit" class="jpm-btn jpm-btn-primary">Submit Application</button>
                <div class="jpm-app-response" style="display:none;"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
