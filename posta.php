<?php
/**
 * @package Posta
 * @version 1.7.2
 */
/*
Plugin Name: Posta
Plugin URI: https://scapemark.com
Description: This is a plugin to create signals and send them on your sight 
Author: Gemma 
Version: 1.0
Author URI: https://scapemark.com
*/
/****************************************************
 * Activation and deactivation hooks
 ****************************************************/
function activate_posta() {
    // Any activation code you might need
}
register_activation_hook(__FILE__, 'activate_posta');

function deactivate_posta() {
    // Any deactivation code you might need
}
register_deactivation_hook(__FILE__, 'deactivate_posta');

/****************************************************
 * Custom Post Type and Taxonomy Registration
 ****************************************************/
function posta_register_custom_post_type() {
    register_post_type('signal', array(
        'labels' => array(
            'name' => 'Signals',
            'singular_name' => 'Signal',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'posta_register_custom_post_type');

/****************************************************
 * Enqueue Styles and Scripts
 ****************************************************/
function posta_admin_styles() {
    // Enqueue custom styles for the admin area
    wp_enqueue_style('posta-admin-styles', plugin_dir_url(__FILE__) . 'admin-styles.css');
}
add_action('admin_enqueue_scripts', 'posta_admin_styles');

function posta_admin_content_styles() {
    // Enqueue custom styles for the admin content
    wp_enqueue_style('posta-admin-content-styles', plugin_dir_url(__FILE__) . 'admin-content-styles.css');
}
add_action('admin_enqueue_scripts', 'posta_admin_content_styles');

/****************************************************
 * Admin-related Functions
 ****************************************************/
//Add Dashboard
function posta_admin_page() {
    add_menu_page(
        'Posta Plugin',
        'Posta',
        'manage_options',
        'posta',
        'posta_admin_content',
        'dashicons-list-view', // Icon
        30 // Menu position
    );
}
add_action('admin_menu', 'posta_admin_page');

function posta_admin_content() {
    ?>
    <div class="wrap custom-signals-admin-page">
        <h1>Add New Signal</h1>
        <form method="post" action="">
            <label for="symbol">Symbol:</label>
            <input type="text" name="symbol" id="symbol" required>
            
            <label for="entry_price">Entry Price:</label>
            <input type="text" name="entry_price" id="entry_price" required>
            
            <label for="stop_loss">Stop Loss:</label>
            <input type="text" name="stop_loss" id="stop_loss" required>
            
            <label for="take_profit_1">Take Profit 1:</label>
            <input type="text" name="take_profit_1" id="take_profit_1" required>
            
            <label for="take_profit_2">Take Profit 2:</label>
            <input type="text" name="take_profit_2" id="take_profit_2" required>
            
            <label for="take_profit_3">Take Profit 3:</label>
            <input type="text" name="take_profit_3" id="take_profit_3" required>
            
            <label for="take_profit_4">Take Profit 4:</label>
            <input type="text" name="take_profit_4" id="take_profit_4" required>
            
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="closed">Closed</option>
                <option value="active">Active</option>
                <option value="cancelled">Cancelled</option>
            </select>
            
            <label for="signal_image">Image:</label>
            <input type="file" name="signal_image" id="signal_image">
            
            <input type="submit" name="add_signal" value="Add Signal">
        </form>
        
        <hr>
        <p><strong>Signals Preview:</strong></p>
        <div class="custom-signals-preview">
            <?php
            //Display the shortcode output
            echo do_shortcode('[posta]');
            ?>
        </div>
    </div>
    <?php
}

function posta_save_signal() {
    if (isset($_POST['add_signal'])) {
        $symbol = sanitize_text_field($_POST['symbol']);
        $entry_price = sanitize_text_field($_POST['entry_price']);
        $stop_loss = sanitize_text_field($_POST['stop_loss']);
        $take_profit_1 = sanitize_text_field($_POST['take_profit_1']);
        $take_profit_2 = sanitize_text_field($_POST['take_profit_2']);
        $take_profit_3 = sanitize_text_field($_POST['take_profit_3']);
        $take_profit_4 = sanitize_text_field($_POST['take_profit_4']);
        $status = sanitize_text_field($_POST['status']);
        
        // Create a new post of the 'signals' custom post type
        $new_signal = array(
            'post_title'   => $symbol, // You can customize this
            'post_type'    => 'signal',
            'post_status'  => 'publish',
        );
        
        // Insert the post into the database
        $signal_id = wp_insert_post($new_signal);
        
        // Save custom field values
        if ($signal_id) {
            update_post_meta($signal_id, 'symbol', $symbol);
            update_post_meta($signal_id, 'entry_price', $entry_price);
            update_post_meta($signal_id, 'stop_loss', $stop_loss);
            update_post_meta($signal_id, 'take_profit_1', $take_profit_1);
            update_post_meta($signal_id, 'take_profit_2', $take_profit_2);
            update_post_meta($signal_id, 'take_profit_3', $take_profit_3);
            update_post_meta($signal_id, 'take_profit_4', $take_profit_4);
            update_post_meta($signal_id, 'status', $status);

            // Handle image upload and save
            if (!empty($_FILES['signal_image']['name'])) {
                $image_id = posta_handle_image_upload($signal_id);
                if ($image_id) {
                    set_post_thumbnail($signal_id, $image_id);
                }
            }
        
            // Display a success message
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Signal added successfully!</p></div>';
            });
        } else {
            // Display an error message
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Error adding signal.</p></div>';
            });
        }
    }
}
add_action('admin_init', 'posta_save_signal');

function posta_admin_notice() {
    ?>
    <div class="notice notice-info custom-signals-notice is-dismissible">
        <p><strong>Posta Plugin Instructions:</strong> To display signals on a page, use the following shortcode: [posta]</p>
        <hr>
        
    </div>
    <?php
}
add_action('admin_notices', 'posta_admin_notice');

/****************************************************
 * Frontend-related Functions
 ****************************************************/
// Shortcode Integration
function posta_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/signals-template.php';
    return ob_get_clean();
}
add_shortcode('posta', 'posta_shortcode');

// Display Custom Post Type Signals
function display_signals_loop() {
    $args = array(
        'post_type' => 'signal', // Change to your custom post type slug
        'posts_per_page' => 12, // Display all signals
    );

    $signals = new WP_Query($args);

    if ($signals->have_posts()) {
    ?>
    <section id="signals-list">
        <div class="row"> 
            <?php
                while ($signals->have_posts()) {
                    $signals->the_post();

                    $symbol = get_post_meta(get_the_ID(), 'symbol', true);
                    $entry_price = get_post_meta(get_the_ID(), 'entry_price', true);
                    $stop_loss = get_post_meta(get_the_ID(), 'stop_loss', true);
                    $take_profit_1 = get_post_meta(get_the_ID(), 'take_profit_1', true);
                    $take_profit_2 = get_post_meta(get_the_ID(), 'take_profit_2', true);
                    $take_profit_3 = get_post_meta(get_the_ID(), 'take_profit_3', true);
                    $take_profit_4 = get_post_meta(get_the_ID(), 'take_profit_4', true);
                    $status = ucfirst(strtolower(get_post_meta(get_the_ID(), 'status', true)));
                    $image_url = 'http://localhost/mychargeback/wp-content/uploads/2023/08/7xm.xyz_483993-scaled.jpg';//get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); // Get the featured image URL
                    $post_time = human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; // Get human-readable post time
            ?>
                <div class="col-md-4 gy-5">
                    <div class="card"> 
                        <?php
                            if ($image_url) {
                                echo '<div class="featured-image bg-accent text-center">';                            
                                echo '<img src="' . esc_url($image_url) . '" alt="Featured Image">';
                                echo '</div>';
                            }
                        ?>                        
                        <div class="card-header bg-main text-accent text-center">
                            <h3 class="symbol"><strong><?php echo esc_html($symbol); ?></strong></h3>
                        </div>
                        <div class="card-body bg-second text-light"> 
                            <div class="row border-bottom">
                                <div class="col-6 entry-price">
                                    <span><strong>Entry Price:</strong></span> 
                                    <span class="price"><?php echo esc_html($entry_price); ?></span>
                                </div>
                                <div class="col-6 stop-loss">
                                    <span><strong>Stop Loss:</strong></span> 
                                    <span class="price"><?php echo esc_html($stop_loss); ?></span>                                
                                </div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 take-profit-1">
                                    <span><strong>Take Profit 1:</strong></span> 
                                    <span class="price"><?php echo esc_html($take_profit_1); ?></span>                                
                                </div>
                                <div class="col-6 take-profit-2">
                                    <span><strong>Take Profit 2:</strong></span> 
                                    <span class="price"><?php echo esc_html($take_profit_2); ?></span>                                
                                </div>                           
                            </div>
                            <div class="row">
                            <div class="col-6 take-profit-3">
                                    <span><strong>Take Profit 3:</strong></span> 
                                    <span class="price"><?php echo esc_html($take_profit_3); ?></span>                                
                                </div>  
                                <div class="col-6 take-profit-4">
                                    <span><strong>Take Profit 4:</strong></span> 
                                    <span class="price"><?php echo esc_html($take_profit_4); ?></span>                                
                                </div>  
                            </div>                        
                        </div>
                        <div class="card-footer bg-main text-accent d-flex justify-content-between">                        
                            <span class="status"><strong>Status:</strong> <?php echo esc_html($status); ?></span> 
                            <span class="time-posted"><strong>Posted:</strong> <?php echo esc_html($post_time); ?></span>                      
                        </div>
                    </div>
                </div>
            <?php
                }
                wp_reset_postdata();
            ?>
        </div>
    </section>   
    <?php
    }
}

function posta_frontend_styles() {
    // Enqueue custom styles for the frontend
    wp_enqueue_style('posta-frontend-styles', plugin_dir_url(__FILE__) . 'frontend-styles.css');
}
add_action('wp_enqueue_scripts', 'posta_frontend_styles');

function posta_add_custom_fields() {
    add_meta_box('custom_signal_fields', 'Signal Fields', 'posta_custom_fields_callback', 'signal', 'normal', 'high');
}
add_action('add_meta_boxes', 'posta_add_custom_fields');

function posta_custom_fields_callback($post) {
    // Retrieve existing values if they exist
    $symbol = get_post_meta($post->ID, 'symbol', true);
    $entry_price = get_post_meta($post->ID, 'entry_price', true);
    $stop_loss = get_post_meta($post->ID, 'stop_loss', true);
    $take_profit_1 = get_post_meta($post->ID, 'take_profit_1', true);
    $take_profit_2 = get_post_meta($post->ID, 'take_profit_2', true);
    $take_profit_3 = get_post_meta($post->ID, 'take_profit_3', true);
    $take_profit_4 = get_post_meta($post->ID, 'take_profit_4', true);
    $status = get_post_meta($post->ID, 'status', true);

    // Get the featured image ID and URL
    $image_id = get_post_thumbnail_id($post->ID);
    $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
    $current_image_url = $image_url ? $image_url[0] : '';

    ?>

    <label for="symbol">Symbol:</label>
    <input type="text" name="symbol" value="<?php echo esc_attr($symbol); ?>"><br>

    <label for="entry_price">Entry Price:</label>
    <input type="text" name="entry_price" value="<?php echo esc_attr($entry_price); ?>"><br>

    <label for="stop_loss">Stop Loss:</label>
    <input type="text" name="stop_loss" value="<?php echo esc_attr($stop_loss); ?>"><br>

    <label for="take_profit_1">Take Profit 1:</label>
    <input type="text" name="take_profit_1" value="<?php echo esc_attr($take_profit_1); ?>"><br>

    <label for="take_profit_2">Take Profit 2:</label>
    <input type="text" name="take_profit_2" value="<?php echo esc_attr($take_profit_2); ?>"><br>

    <label for="take_profit_3">Take Profit 3:</label>
    <input type="text" name="take_profit_3" value="<?php echo esc_attr($take_profit_3); ?>"><br>

    <label for="take_profit_4">Take Profit 4:</label>
    <input type="text" name="take_profit_4" value="<?php echo esc_attr($take_profit_4); ?>"><br>

    <label for="status">Status:</label>
    <input type="text" name="status" value="<?php echo esc_attr($status); ?>"><br>

    <label for="image">Image:</label>
    <input type="text" name="signal_image" value="<?php echo esc_attr($current_image_url); ?>"><br>
    <input type="button" id="upload_image_button" class="button" value="Upload Image">

    <script>
        jQuery(document).ready(function($) {
            $('#upload_image_button').click(function() {
                var customUploader = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });

                customUploader.on('select', function() {
                    var attachment = customUploader.state().get('selection').first().toJSON();
                    $('input[name="signal_image"]').val(attachment.url);
                });

                customUploader.open();
            });
        });
    </script>

    <?php
}

function posta_save_custom_fields($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save custom field data
    update_post_meta($post_id, 'symbol', sanitize_text_field($_POST['symbol']));
    update_post_meta($post_id, 'entry_price', sanitize_text_field($_POST['entry_price']));
    update_post_meta($post_id, 'stop_loss', sanitize_text_field($_POST['stop_loss']));
    update_post_meta($post_id, 'take_profit_1', sanitize_text_field($_POST['take_profit_1']));
    update_post_meta($post_id, 'take_profit_2', sanitize_text_field($_POST['take_profit_2']));
    update_post_meta($post_id, 'take_profit_3', sanitize_text_field($_POST['take_profit_3']));
    update_post_meta($post_id, 'take_profit_4', sanitize_text_field($_POST['take_profit_4']));
    update_post_meta($post_id, 'status', sanitize_text_field($_POST['status']));

    // Handle the featured image upload
    if (!empty($_FILES['signal_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('signal_image', $post_id);

        if (is_wp_error($attachment_id)) {
            // Handle error if image upload fails
        } else {
            // Set the uploaded image as the featured image
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
}
add_action('save_post', 'posta_save_custom_fields');



function posta_handle_image_upload($post_id) {
    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    
    $uploaded_image = $_FILES['image'];
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploaded_image, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $file_type = wp_check_filetype(basename($movefile['file']), null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
            'post_content' => '',
            'post_status' => 'inherit',
        );
        $attachment_id = wp_insert_attachment($attachment, $movefile['file'], $post_id);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        return $attachment_id;
    } else {
        return false;
    }
}

function posta_scripts() {
    // Enqueue your JavaScript file
    wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js' );
    wp_enqueue_script('posta-scripts', plugin_dir_url(__FILE__) . '/assets/dist/main.bundle.js', [], '1.0.0', true);

    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' );
    wp_enqueue_style('posta-styles', plugin_dir_url(__FILE__) . '/assets/dist/main.css', [], '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'posta_scripts');


