<?php
/**
 * Plugin Name: Bros Travel API integration
 * Description: A plugin for integrating with the Bros Travel API
 * Version: 1.0.0
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



/**
 * Shortcode to display recommendations (properties)
 */
// function bros_travel_display_recommendations_shortcode() {
//     // Initialize the Recommendations API class
//     $recommendations_api = new Bros_Travel_API_Recommendations();

//     // Get the recommendations (this will return an array of recommendations or an error message)
//     $recommendations = $recommendations_api->get_recommendations();

//     // Check if we received a valid response and the result contains properties
//     if (isset($recommendations['result']['properties']) && is_array($recommendations['result']['properties'])) {
//         // Prepare the output to display recommendations
//         $output = '<div class="bros-recommendations">';
//         $output .= '<h3>Recommended Properties</h3>';
//         $output .= '<ul>';

//         // Loop through each property and display its name and rating
//         foreach ($recommendations['result']['properties'] as $property) {
//             // Assuming each property has a 'name' and 'rating'; adjust as necessary
//             $output .= '<li>';
//             $output .= '<strong>' . esc_html($property['name']) . '</strong><br>';
//             $output .= 'Rating: ' . esc_html($property['rating']) . '<br>';
//             $output .= '</li>';
//         }

//         $output .= '</ul>';
//         $output .= '</div>';

//         return $output;
//     } else {
//         // If no recommendations are available or there's an error fetching them, display a message
//         return '<div class="bros-recommendations-error">No recommendations found or there was an error fetching them.</div>';
//     }
// }

// function bros_travel_display_properties_shortcode() {
//     // Initialize the Recommendations API class
//     $properties_api = new Bros_Travel_API_Properties();

//     // Get the properties (this will return an array of properties or an error message)
//     $properties = $properties_api->get_properties();

//     // Check if we received a valid response and the result contains properties
//     if (isset($properties['result']) && is_array($properties['result'])) {
//         // Prepare the output to display properties
//         $output = '<div class="bros-properties">';
//         $output .= '<h3>Properties</h3>';
//         $output .= '<ul>';

//         // Loop through each property and display relevant information
//         foreach ($properties['result'] as $property) {
//             // Check if property has required fields
//             if (isset($property['name'], $property['rating'], $property['image'], $property['description'])) {
//                 $output .= '<li class="property-item">';
//                 $output .= '<img src="' . esc_url($property['image']) . '" alt="' . esc_attr($property['name']) . '" class="property-image"><br>';
//                 $output .= '<strong>' . esc_html($property['name']) . '</strong><br>';
//                 $output .= 'Rating: ' . esc_html($property['rating']) . '<br>';
//                 $output .= '<p>' . esc_html($property['description']) . '</p>';
//                 $output .= '</li>';
//             }
//         }

//         $output .= '</ul>';
//         $output .= '</div>';

//         return $output;
//     } else {
//         // If no properties are available or there's an error fetching them, display a message
//         return '<div class="bros-properties-error">No properties found or there was an error fetching them.</div>';
//     }
// }


// // Register the shortcode
// add_shortcode('bros_travel_recommendations', 'bros_travel_display_recommendations_shortcode');
// add_shortcode('bros_travel_properties', 'bros_travel_display_properties_shortcode');
