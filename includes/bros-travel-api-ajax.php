<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bros_Travel_API_AJAX {
    
    private $login;   
    private $properties;
    private $locations;
    private $sa_properties;

    // Constructor: Initializes the required classes and hooks for AJAX
    public function __construct() {
        // Initialize the Bros_Travel_API_Login class
        $this->login = new Bros_Travel_API_Login();
        

        // Initialize the Bros_Travel_API_Properties class
        $this->properties = new Bros_Travel_API_Properties();

        // Initialize the Bros_Travel_API_Locations class
        $this->locations = new Bros_Travel_API_Locations();
        
        // Initialize the Bros_Travel_API_SA_Properties class
        $this->sa_properties = new Bros_Travel_API_SA_Properties();
        // Hook into WordPress AJAX actions
        add_action('wp_ajax_bros_travel_token', array($this, 'bros_travel_token'));
        add_action('wp_ajax_nopriv_bros_travel_token', array($this, 'bros_travel_token'));
       

        add_action('wp_ajax_get_bros_travel_properties', array($this, 'get_bros_travel_properties'));
        add_action('wp_ajax_nopriv_get_bros_travel_properties', array($this, 'get_bros_travel_properties'));

        add_action('wp_ajax_get_bros_travel_locations', array($this, 'get_bros_travel_locations'));
        add_action('wp_ajax_nopriv_get_bros_travel_locations', array($this, 'get_bros_travel_locations'));

        add_action('wp_ajax_get_bros_travel_all_destinations', array($this, 'get_bros_travel_all_destinations'));
        add_action('wp_ajax_nopriv_get_bros_travel_all_destinations', array($this, 'get_bros_travel_all_destinations'));

        add_action('wp_ajax_get_bros_travel_sa_properties', array($this, 'get_bros_travel_sa_properties'));
        add_action('wp_ajax_nopriv_get_bros_travel_sa_properties', array($this, 'get_bros_travel_sa_properties'));
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
    public function get_bros_travel_all_destinations() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }
    
        // Get properties and locations from the API
        $properties_data = $this->properties->get_properties();
        $locations_data = $this->locations->get_locations();
    
        $result = [];
    
        // Check if properties and locations are valid arrays
        if (isset($properties_data['result']) && is_array($properties_data['result']) &&
            isset($locations_data['result']) && is_array($locations_data['result'])) {
            
            foreach ($locations_data['result'] as $location) {
                if (!empty($location)) {
                    // Prepare the destination strings
                    $destinationStrings = [
                        "{$location['region']} / {$location['country']}",
                        "{$location['subregion']} / {$location['region']} / {$location['country']}",
                        "{$location['name']} / {$location['subregion']} / {$location['region']} / {$location['country']}",
                    ];
    
                    // Merge destination strings into result
                    $result = array_merge($result, $destinationStrings);
    
                    // Iterate over properties and add destination strings based on location match
                    foreach ($properties_data['result'] as $property) {
                        if (isset($property['locationid']) && $property['locationid'] === $location['locationid']) {
                            $destinationString4 = "{$property['name']} / {$location['name']} / {$location['subregion']} / {$location['region']} / {$location['country']}";
                            $result[] = $destinationString4;
                        }
                    }
                }
            }
        }
    
         // Remove duplicates
        $result = array_unique($result);
        // Return the result or an appropriate message if no match is found
        if (!empty($result)) {
            wp_send_json_success(array_values($result));
        } else {
            wp_send_json_error('No matching properties or locations found.');
        }
    }
    

    public function get_bros_travel_sa_properties() {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bros-travel-nonce')) {
            echo json_encode(['error' => 'Invalid nonce']);
            die();
        }
    
        // Call get_sa_properties and pass the POST parameters dynamically
        $sa_properties_data = $this->sa_properties->get_sa_properties();
    
        if (isset($sa_properties_data['error'])) {
            wp_send_json_error($sa_properties_data['error']);
        } elseif ($sa_properties_data) {
            wp_send_json_success($sa_properties_data);
        } else {
            wp_send_json_error('Failed to retrieve properties.');
        }
    }
    


}

// Initialize the AJAX class (on plugin activation or initialization)
new Bros_Travel_API_AJAX();
