<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once 'api-base.php'; // Include the base class

class Bros_Travel_API_Locations extends Bros_Travel_API_Base {

    // Method to get locations using the token
    public function get_locations() {
        if (!$this->validate_token()) {
            return;
        }

        // Prepare the request
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'jsonrpc' => '2.0',
            'method' => 'getLocations',
            'params' => null,
            'id' => 1,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}",
        ]);

        // Execute the request and handle the response
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL error during API request: ' . curl_error($ch) . '<br>';
            curl_close($ch);
            return;
        }
        curl_close($ch);

        $response_data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Invalid JSON response during API request: ' . json_last_error_msg() . '<br>';
            return;
        }

        if (isset($response_data['error'])) {
            echo 'API Error: ' . $response_data['error']['message'] . '<br>';
            return;
        }

        return $response_data;
    }
}

?>
