<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Job_Post_Settings {
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts' ] );
    }

    public static function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=job_posting',
            'Application Form Fields',
            'Form Fields',
            'manage_options',
            'jpm-form-fields',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'jpm_settings_group', 'jpm_dynamic_fields' );
    }

    public static function enqueue_admin_scripts( $hook ) {
        if ( $hook !== 'job_posting_page_jpm-form-fields' ) {
            return;
        }
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_style( 'dashicons' );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $fields = get_option( 'jpm_dynamic_fields', [] );
        if ( ! is_array( $fields ) ) {
            $fields = [];
        }

        // Initialize Core System Variables dynamically bridging legacy options directly into sequential sorting maps safely
        $has_core = false;
        foreach ($fields as $f) {
            if (isset($f['system_key']) && $f['system_key'] === 'core_name') $has_core = true;
        }
        
        if (!$has_core) {
            $core_top = [
                ['label' => 'Full Name', 'type' => 'text', 'required' => 1, 'system_key' => 'core_name'],
                ['label' => 'Email Address', 'type' => 'email', 'required' => 1, 'system_key' => 'core_email'],
                ['label' => 'Phone Number', 'type' => 'text', 'required' => 0, 'system_key' => 'core_phone'],
            ];
            $core_bottom = [
                ['label' => 'Upload CV/Resume', 'type' => 'file', 'required' => 1, 'system_key' => 'core_cv'],
                ['label' => 'Cover Letter / Message', 'type' => 'textarea', 'required' => 1, 'system_key' => 'core_message'],
            ];
            
            // Standardize array completely structurally
            $fields = array_merge($core_top, $fields, $core_bottom);
            update_option('jpm_dynamic_fields', $fields);
        }
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Dynamic Form Builder</h1>
            <p>Instantly define secure custom dynamic fields that applicants must fill out when applying for a job.</p>
            <form method="post" action="options.php" id="jpm-fields-form" style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; max-width: 900px; margin-top:20px;">
                <?php settings_fields( 'jpm_settings_group' ); ?>
                
                <table class="form-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th style="font-weight:bold;">Field Title</th>
                            <th style="font-weight:bold;">Field Type</th>
                            <th style="font-weight:bold;">Options</th>
                            <th></th>
                        </tr>
                        <!-- Unified Master Layout Fields -->
                        <?php foreach ( $fields as $index => $field ) : ?>
                            <tr class="jpm-field-row" style="background:#ffffff; border-bottom:1px solid #ddd; cursor:move;">
                                <td style="text-align:center; color:#9ca3af;"><span class="dashicons dashicons-menu"></span></td>
                                <td style="padding: 10px;">
                                    <?php if (!empty($field['system_key'])) : ?>
                                        <input type="hidden" name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][system_key]" value="<?php echo esc_attr($field['system_key']); ?>">
                                    <?php endif; ?>
                                    <input type="text" name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" required style="width: 100%; max-width: 300px;">
                                </td>
                                <td>
                                    <?php if (!empty($field['system_key']) && in_array($field['system_key'], ['core_email', 'core_cv'])) : ?>
                                        <input type="hidden" name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][type]" value="<?php echo esc_attr($field['type']); ?>">
                                        <em><?php echo $field['type'] === 'file' ? 'File Upload' : 'Email Engine'; ?> (System Locked)</em>
                                    <?php else : ?>
                                        <select name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][type]" style="max-width: 150px;">
                                            <option value="text" <?php selected( $field['type'], 'text' ); ?>>Short Text (Input)</option>
                                            <option value="textarea" <?php selected( $field['type'], 'textarea' ); ?>>Paragraph (Textarea)</option>
                                        </select>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($field['system_key']) && $field['system_key'] !== 'core_phone') : ?>
                                        <input type="hidden" name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][required]" value="1">
                                        <em>Required (System Locked)</em>
                                    <?php else : ?>
                                        <label><input type="checkbox" name="jpm_dynamic_fields[<?php echo esc_attr($index); ?>][required]" value="1" <?php checked( isset( $field['required'] ) ? $field['required'] : 0, 1 ); ?>> Required</label>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($field['system_key'])) : ?>
                                        <em style="color:#64748b;">Core Locked Object</em>
                                    <?php else : ?>
                                        <button type="button" class="button jpm-remove-field" style="color:#d63638; border-color:#d63638;">Remove</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <button type="button" class="button button-secondary" id="jpm-add-field">+ Add New Input Field</button>
                <hr style="margin:20px 0;">
                <?php submit_button( 'Save Dynamic Fields', 'primary', 'submit', false ); ?>
            </form>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Engage WP-native jQuery sortable interactions enabling drag-and-drop arrays
                $('#jpm-fields-container').sortable({
                    axis: 'y',
                    cursor: 'move',
                    opacity: 0.8,
                    handle: '.dashicons-menu', // Only allow dragging from the icon!
                    update: function(event, ui) {
                        $('#jpm-fields-container .jpm-field-row').each(function(index) {
                            $(this).find('input[name*="[system_key]"]').attr('name', 'jpm_dynamic_fields[' + index + '][system_key]');
                            $(this).find('input[name*="[label]"]').attr('name', 'jpm_dynamic_fields[' + index + '][label]');
                            $(this).find('select[name*="[type]"], input[type="hidden"][name*="[type]"]').attr('name', 'jpm_dynamic_fields[' + index + '][type]');
                            $(this).find('input[type="checkbox"][name*="[required]"], input[type="hidden"][name*="[required]"]').attr('name', 'jpm_dynamic_fields[' + index + '][required]');
                        });
                    }
                });

                var fieldCount = <?php echo count( $fields ); ?>;
                $('#jpm-add-field').on('click', function() {
                    var html = '<tr class="jpm-field-row" style="background:#ffffff; border-bottom:1px solid #ddd; cursor:move;">' +
                        '<td style="text-align:center; color:#9ca3af;"><span class="dashicons dashicons-menu"></span></td>' +
                        '<td style="padding: 10px;">' +
                            '<input type="text" name="jpm_dynamic_fields[' + fieldCount + '][label]" placeholder="e.g. GitHub Link" required style="width: 100%; max-width: 300px;">' +
                        '</td>' +
                        '<td>' +
                            '<select name="jpm_dynamic_fields[' + fieldCount + '][type]">' +
                                '<option value="text">Short Text (Input)</option>' +
                                '<option value="textarea">Paragraph (Textarea)</option>' +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<label style="display:inline-block; margin-bottom:6px;"><input type="checkbox" name="jpm_dynamic_fields[' + fieldCount + '][required]" value="1"> Required</label><br>' +
                        '</td>' +
                        '<td>' +
                            '<button type="button" class="button jpm-remove-field" style="color:#d63638; border-color:#d63638;">Remove</button>' +
                        '</td>' +
                    '</tr>';
                    $('#jpm-fields-container').append(html);
                    
                    // Force refresh generic indexes just to be universally perfectly safe preventing duplicates sequentially
                    $('#jpm-fields-container').trigger('sortupdate');
                    fieldCount++;
                });

                $(document).on('click', '.jpm-remove-field', function() {
                    $(this).closest('tr').remove();
                    $('#jpm-fields-container').trigger('sortupdate');
                });
            });
        </script>
        <?php
    }
}
