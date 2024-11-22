<?php
if (!defined('ABSPATH')) {
    exit;
}

class Bros_Travel_API_Login {
    private $api_url = 'https://testservices.bros-travel.com';
    private $username = 'test_bros';
    private $password = 'brostest';
    private $token = null;

    public function __construct() {
        $this->login();
    }

    
    private function login() {
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'jsonrpc' => '2.0',
            'method' => 'login',
            'params' => [
                'username' => $this->username,
                'password' => $this->password,
            ],
            'id' => 1,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch) . '<br>';
            return;
        }

  
        curl_close($ch);

        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON decoding error: ' . json_last_error_msg() . '<br>';
            return;
        }

        if (isset($response_data['result']['token'])) {
            $this->token = $response_data['result']['token'];
        } else {
            echo 'Token not found in response.<br>';
        }
    }

    // Method to send an API request using the token
    public function api_request($method, $params = []) {
        // Check if token is available
        if (!$this->token) {
            echo 'No valid token available for API request.<br>';
            return;
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([ 
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1,
            'token' => $this->token, // Add the token in the request
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error during API request: ' . curl_error($ch) . '<br>';
            curl_close($ch);
            return;
        }

        curl_close($ch);

        // Decode the response
        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Invalid JSON response during API request: ' . json_last_error_msg() . '<br>';
            return;
        }

        // Check for API errors
        if (isset($response_data['error'])) {
            echo 'API Error: ' . $response_data['error']['message'] . '<br>';
            return;
        }

        // Return the response data if successful
        return $response_data;
    }

    // Method to get the token
    public function get_token() {
        return $this->token ? $this->token : 'No valid token available.';
    }
}

?>
