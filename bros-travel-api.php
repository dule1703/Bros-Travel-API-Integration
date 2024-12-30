<?php
/**
 * Plugin Name: Bros Travel API integration
 * Description: A plugin for integrating with the Bros Travel API
 * Version: 1.0.1
 * Author: Duško Drljača
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Define the plugin directory path
define('BROS_TRAVEL_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include required classes
// Include the initialization file
require_once BROS_TRAVEL_PLUGIN_DIR . 'includes/init.php';

/**
 * Enqueue the necessary scripts for the plugin
 */
function bros_travel_enqueue_scripts() {
    // Load Vue.js from CDN as a module
    wp_enqueue_script('vue-js', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), null, true);
    
    // Enqueue the main JavaScript file as a module
    wp_enqueue_script('main-bros', plugins_url('assets/js/main-bros.js', __FILE__), array('jquery'), '1.0.0', true);

        // Create nonce for AJAX security
        $nonce = wp_create_nonce('bros-travel-nonce');

        // Localize the script with the AJAX URL and nonce
        wp_localize_script('main-bros', 'brosTravelData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => $nonce
        ));

}

add_action('wp_enqueue_scripts', 'bros_travel_enqueue_scripts');

/**
 * Enqueue the necessary styles for the plugin
 */
function bros_travel_enqueue_styles() {
    // Load plugin-specific CSS
    wp_enqueue_style('bros-style', plugins_url('assets/css/bros-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'bros_travel_enqueue_styles');

/**
 * Add nonce for security
 */
function bros_travel_nonce() {
    $nonce = wp_create_nonce('bros-travel-nonce');
    wp_add_inline_script('main-bros', "var brosTravelNonce = '" . esc_js($nonce) . "';", 'before');
}

add_action('wp_enqueue_scripts', 'bros_travel_nonce');

/**
 * Shortcode to display search form
 */
function bros_travel_search_shortcode() {
    ob_start();
    include(plugin_dir_path(__FILE__) . 'includes/bros-search-form.php');
    return ob_get_clean();
}
add_shortcode('bros_travel_search', 'bros_travel_search_shortcode');



// Add Bros Travel API settings page to the admin menu
function bros_travel_add_admin_menu() {
    add_menu_page(
        'Bros Travel API Settings', // Page title
        'Bros Travel API', // Menu title
        'manage_options', // Capability
        'bros-travel-api', // Menu slug
        'bros_travel_render_settings_page', // Callback function
        'dashicons-admin-generic', // Icon (optional)
        100 // Position in the menu (optional)
    );
}
add_action('admin_menu', 'bros_travel_add_admin_menu');

// Render the settings page
function bros_travel_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Bros Travel API Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('bros_travel_api_settings'); // Settings group name
            do_settings_sections('bros-travel-api'); // Page slug
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings and fields
function bros_travel_register_settings() {
    // Register the settings group
    register_setting('bros_travel_api_settings', 'bros_travel_api_url');
    register_setting('bros_travel_api_settings', 'bros_travel_api_username');
    register_setting('bros_travel_api_settings', 'bros_travel_api_password');

    // Add the settings section
    add_settings_section(
        'bros_travel_api_section', // Section ID
        'API Credentials', // Section title
        null, // Callback function (optional)
        'bros-travel-api' // Page slug
    );

    // Add settings fields
    add_settings_field(
        'bros_travel_api_url', // Field ID
        'API URL', // Field title
        'bros_travel_render_input_field', // Callback function
        'bros-travel-api', // Page slug
        'bros_travel_api_section', // Section ID
        ['label_for' => 'bros_travel_api_url', 'type' => 'text'] // Custom args
    );

    add_settings_field(
        'bros_travel_api_username',
        'Username',
        'bros_travel_render_input_field',
        'bros-travel-api',
        'bros_travel_api_section',
        ['label_for' => 'bros_travel_api_username', 'type' => 'text']
    );

    add_settings_field(
        'bros_travel_api_password',
        'Password',
        'bros_travel_render_input_field',
        'bros-travel-api',
        'bros_travel_api_section',
        ['label_for' => 'bros_travel_api_password', 'type' => 'password']
    );
}
add_action('admin_init', 'bros_travel_register_settings');

// Callback to render input fields
function bros_travel_render_input_field($args) {
    $option = get_option($args['label_for']);
    $type = $args['type'] ?? 'text'; // Default to text input
    ?>
    <input
        type="<?php echo esc_attr($type); ?>"
        id="<?php echo esc_attr($args['label_for']); ?>"
        name="<?php echo esc_attr($args['label_for']); ?>"
        value="<?php echo esc_attr($option); ?>"
        class="regular-text"
    />
    <?php
}


