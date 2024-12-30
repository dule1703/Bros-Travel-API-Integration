<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the login API file to use the login class
require_once 'login-api.php';

// Base class for Bros Travel API classes
class Bros_Travel_API_Base {
    protected $api_url;
    protected $token = null;  // To store the API token
    protected $login;  // Instance of the Bros_Travel_API_Login class

    // Constructor to initialize the class
    public function __construct() {
        $this->api_url = get_option('bros_travel_api_url', '');

        // Debug log for API URL
        error_log('API URL in Base Class: ' . $this->api_url);
      // Start the session if it's not already started and headers are not sent
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }

    // Check if the token is cached in the session
    if (isset($_SESSION['bros_travel_token']) && !empty($_SESSION['bros_travel_token'])) {
        // If the token is cached, use it
        $this->token = $_SESSION['bros_travel_token'];
    } else {
        // If token is not cached, get a new token
        $this->login = new Bros_Travel_API_Login();
        $this->token = $this->login->get_token();

        // Cache the token in the session
        $_SESSION['bros_travel_token'] = $this->token;

        // Set the token expiration time (1 hour from now)
        $_SESSION['bros_travel_token_expiry'] = time() + 3600;  // 1 hour in the future
    }
    }

    // Method to validate if the token is still valid
    protected function validate_token() {
        // Check if there is a token
        if (!$this->token) {
            echo 'No valid token available for API request.<br>';
            return false;
        }

        // Check if the token has expired
        if (isset($_SESSION['bros_travel_token_expiry']) && time() > $_SESSION['bros_travel_token_expiry']) {
            // If the token has expired, get a new token
            $this->login = new Bros_Travel_API_Login();
            $this->token = $this->login->get_token();

            // Cache the new token and reset the expiration time
            $_SESSION['bros_travel_token'] = $this->token;
            $_SESSION['bros_travel_token_expiry'] = time() + 3600;  // 1 hour in the future
        }

        return true;  // The token is valid
    }
}
?>
