<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bros_Travel_API_AJAX {
    
    private $login;
    private $recommendations;
    private $properties;
    private $locations;

    // Constructor: Initializes the required classes and hooks for AJAX
    public function __construct() {
        // Initialize the Bros_Travel_API_Login class
        $this->login = new Bros_Travel_API_Login();

        // Initialize the Bros_Travel_API_Recommendations class
        $this->recommendations = new Bros_Travel_API_Recommendations();

        // Initialize the Bros_Travel_API_Properties class
        $this->properties = new Bros_Travel_API_Properties();

        // Initialize the Bros_Travel_API_Locations class
        $this->locations = new Bros_Travel_API_Locations();

        // Hook into WordPress AJAX actions
        add_action('wp_ajax_bros_travel_token', array($this, 'bros_travel_token'));
        add_action('wp_ajax_nopriv_bros_travel_token', array($this, 'bros_travel_token'));

        add_action('wp_ajax_get_bros_travel_recommendations', array($this, 'get_bros_travel_recommendations'));
        add_action('wp_ajax_nopriv_get_bros_travel_recommendations', array($this, 'get_bros_travel_recommendations'));

        add_action('wp_ajax_get_bros_travel_properties', array($this, 'get_bros_travel_properties'));
        add_action('wp_ajax_nopriv_get_bros_travel_properties', array($this, 'get_bros_travel_properties'));

        add_action('wp_ajax_get_bros_travel_locations', array($this, 'get_bros_travel_locations'));
        add_action('wp_ajax_nopriv_get_bros_travel_locations', array($this, 'get_bros_travel_locations'));

        add_action('wp_ajax_filter_properties_and_locations', array($this, 'filter_properties_and_locations'));
        add_action('wp_ajax_nopriv_filter_properties_and_locations', array($this, 'filter_properties_and_locations'));

    }

    // Function to handle the AJAX request for token
    public function bros_travel_token() {
        // Log the incoming data
        error_log('Received AJAX request for token retrieval');

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }

        // Get the token using the login class
        $token = $this->login->get_token();
    
        // Check if token is available
        if ($token) {
            // Return the token in the response
            echo json_encode([
                'token' => $token,
            ]);
        } else {
            echo json_encode(['error' => 'Token retrieval failed']);
        }
    
        die();
    }

    // Function to handle AJAX request for recommendations
    public function get_bros_travel_recommendations() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }

        // Get recommendations from the API
        $recommendations_data = $this->recommendations->get_recommendations();

        if ($recommendations_data) {
            wp_send_json_success($recommendations_data);
        } else {
            wp_send_json_error('Failed to retrieve recommendations.');
        }
    }

    // Function to handle AJAX request for properties
    public function get_bros_travel_properties() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }

        // Get properties from the API
        $properties_data = $this->properties->get_properties();

        if ($properties_data) {
            wp_send_json_success($properties_data);
        } else {
            wp_send_json_error('Failed to retrieve properties.');
        }
    }

    // Function to handle AJAX request for locations
    public function get_bros_travel_locations() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }

        // Get locations from the API
        $locations_data = $this->locations->get_locations();

        if ($locations_data) {
            wp_send_json_success($locations_data);
        } else {
            wp_send_json_error('Failed to retrieve locations.');
        }
    }

    // Function to handle AJAX request for filtering properties and locations
public function filter_properties_and_locations() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
        echo json_encode(['error' => 'Invalid nonce']);
        die();
    }

    // Get the search string from POST request
    $search_string = isset($_POST['search_string']) ? sanitize_text_field($_POST['search_string']) : '';

    if (empty($search_string)) {
        wp_send_json_error('Search string is empty.');
        return;
    }

    // Get properties and locations from the API
    $properties_data = $this->properties->get_properties();
    $locations_data = $this->locations->get_locations();

    $result = [];

    // Check if properties and locations are valid arrays
    if (isset($properties_data['result']) && is_array($properties_data['result']) &&
        isset($locations_data['result']) && is_array($locations_data['result'])) {
        
        // Loop through properties to find matches with the search string
        foreach ($properties_data['result'] as $property) {
            if (stripos($property['name'], $search_string) !== false) {
                // Find the corresponding location for this property
                foreach ($locations_data['result'] as $location) {
                    if ($property['locationid'] == $location['locationid']) {
                        $result[] = [
                            'property_name' => $property['name'],
                            'location_name' => $location['name'],
                            'subregion' => $location['subregion'],
                            'region' => $location['region'],
                            'country' => $location['country']
                        ];
                        break; // Found the matching location, exit inner loop
                    }
                }
            }
        }

        // Loop through locations to find matches with the search string
        foreach ($locations_data['result'] as $location) {
            if (stripos($location['name'], $search_string) !== false ||
                stripos($location['subregion'], $search_string) !== false ||
                stripos($location['region'], $search_string) !== false ||
                stripos($location['country'], $search_string) !== false) {
                
                // Find all properties for this location
                foreach ($properties_data['result'] as $property) {
                    if ($property['locationid'] == $location['locationid']) {
                        $result[] = [
                            'property_name' => $property['name'],
                            'location_name' => $location['name'],
                            'subregion' => $location['subregion'],
                            'region' => $location['region'],
                            'country' => $location['country']
                        ];
                    }
                }
            }
        }
    }

    // Return the result or an appropriate message if no match is found
    if (!empty($result)) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error('No matching properties or locations found.');
    }
}


}

// Initialize the AJAX class (on plugin activation or initialization)
new Bros_Travel_API_AJAX();
