<?php
/**
 * @package Fxsignals
 * @version 1.7.2
 */
/*
Plugin Name: FxSignals Sender
Plugin URI: https://scapemark.com
Description: This is a plugin to create signals and send them on your sight 
Author: Gemma 
Version: 1.0
Author URI: https://scapemark.com
*/

// Activation and deactivation hooks
function activate_fxsignals() {
    // Any activation code you might need
}
register_activation_hook(__FILE__, 'activate_fxsignals');

function deactivate_fxsignals() {
    // Any deactivation code you might need
}
register_deactivation_hook(__FILE__, 'deactivate_fxsignals');

// Display Custom Post Type Signals
function display_signals_loop() {
    $args = array(
        'post_type' => 'signal',
        'posts_per_page' => -1, // Display all signals
    );

    $signals = new WP_Query($args);

    if ($signals->have_posts()) {
        echo '<div class="custom-signals-loop">'; // Add a wrapper div
        while ($signals->have_posts()) {
            $signals->the_post();

            $symbol = get_field('symbol');
            $take_profit = get_field('take_profit');
            $stop_loss = get_field('stop_loss');
            $entry_price = get_field('entry_price');
            // Add more fields as needed

            // Display the signal information
            echo '<div class="signal-item">'; // Add a class to each signal item
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p><strong>Symbol:</strong> ' . $symbol . '</p>';
            echo '<p><strong>Take Profit:</strong> ' . $take_profit . '</p>';
            echo '<p><strong>Stop Loss:</strong> ' . $stop_loss . '</p>';
            echo '<p><strong>Entry Price:</strong> ' . $entry_price . '</p>';
            // Display more fields
            echo '</div>';
        }
        echo '</div>'; // Close the wrapper div
        wp_reset_postdata();
    }
}


// Shortcode Integration
function fxsignals_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/signals-template.php';
    return ob_get_clean();
}
add_shortcode('fxsignals', 'fxsignals_shortcode');

//Add Dashboard
function fxsignals_admin_page() {
    add_menu_page(
        'FxSignals Plugin',
        'Fx Signals',
        'manage_options',
        'fxsignals',
        'fxsignals_admin_content',
        'dashicons-list-view', // Icon
        30 // Menu position
    );
}
add_action('admin_menu', 'fxsignals_admin_page');

function fxsignals_admin_content() {
    ?>
    <div class="wrap custom-signals-admin-page">
        <h1>Add New Signal</h1>
        <form method="post" action="">
            <label for="symbol">Symbol:</label>
            <input type="text" name="symbol" id="symbol" required>

            <label for="take_profit">Take Profit:</label>
            <input type="text" name="take_profit" id="take_profit" required>

            <label for="stop_loss">Stop Loss:</label>
            <input type="text" name="stop_loss" id="stop_loss" required>

            <label for="entry_price">Entry Price:</label>
            <input type="text" name="entry_price" id="entry_price" required>

            <input type="submit" name="add_signal" value="Add Signal">
        </form>
    </div>
    <?php
 }
 

function fxsignals_save_signal() {
    if (isset($_POST['add_signal'])) {
        $symbol = sanitize_text_field($_POST['symbol']);
        $take_profit = sanitize_text_field($_POST['take_profit']);
        $stop_loss = sanitize_text_field($_POST['stop_loss']);
        $entry_price = sanitize_text_field($_POST['entry_price']);
        
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
            update_field('symbol', $symbol, $signal_id);
            update_field('take_profit', $take_profit, $signal_id);
            update_field('stop_loss', $stop_loss, $signal_id);
            update_field('entry_price', $entry_price, $signal_id);

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
add_action('admin_init', 'fxsignals_save_signal');

function fxsignals_admin_notice() {
    ?>
    <div class="notice notice-info custom-signals-notice is-dismissible">
        <p><strong>FxSignals Plugin Instructions:</strong> To display signals on a page, use the following shortcode: [fxsignals]</p>
        <hr>
        <p><strong>Signals Preview:</strong></p>
        <div class="custom-signals-preview">
            <?php
            // Display the shortcode output
            // echo do_shortcode('[fxsignals]');
            ?>
        </div>
    </div>
    <?php
}
add_action('admin_notices', 'fxsignals_admin_notice');

function fxsignals_admin_styles() {
    // Enqueue custom styles for the admin area
    wp_enqueue_style('fxsignals-admin-styles', plugin_dir_url(__FILE__) . 'admin-styles.css');
}
add_action('admin_enqueue_scripts', 'fxsignals_admin_styles');

function fxsignals_admin_content_styles() {
    // Enqueue custom styles for the admin content
    wp_enqueue_style('fxsignals-admin-content-styles', plugin_dir_url(__FILE__) . 'admin-content-styles.css');
}
add_action('admin_enqueue_scripts', 'fxsignals_admin_content_styles');

function fxsignals_frontend_styles() {
    // Enqueue custom styles for the frontend
    wp_enqueue_style('fxsignals-frontend-styles', plugin_dir_url(__FILE__) . 'frontend-styles.css');
}
add_action('wp_enqueue_scripts', 'fxsignals_frontend_styles');


