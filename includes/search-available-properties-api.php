<?php

if (!defined('ABSPATH')) {

    exit;

}



require_once 'api-base.php'; // Include the base class

class Bros_Travel_API_SA_Properties extends Bros_Travel_API_Base {

    // Method to get locations using the token
    public function get_sa_properties() {

        if (!$this->validate_token()) {

            return;

        }

        // Collect parameters from POST request

        $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : '';

        $checkinDate = isset($_POST['checkinDate']) ? sanitize_text_field($_POST['checkinDate']) : '';



if ($checkinDate) {

    // Pokušaj sa formatom DD-MM-YYYY

    $date = DateTime::createFromFormat('d-m-Y', $checkinDate);

    if ($date) {

        $checkinDate = $date->format('Y-m-d'); // Konverzija u željeni format

    } else {

        // Pokušaj sa formatom YYYY-MM-DD

        $date = DateTime::createFromFormat('Y-m-d', $checkinDate);
        $checkinDate = $date && $date->format('Y-m-d') === $checkinDate ? $checkinDate : '';

    }

}



// Uporedite sa trenutnim datumom

$currentDate = (new DateTime())->format('Y-m-d');

if ($checkinDate && $checkinDate < $currentDate) {

    $checkinDate = $currentDate; // Postavite na današnji datum ako je ranije

}

        $nights = isset($_POST['nights']) ? intval($_POST['nights']) : 0;
        $rooms = isset($_POST['rooms']) ? json_decode(stripslashes($_POST['rooms']), true) : [];

        // Validate parameters

        if (empty($region) || empty($checkinDate) || $nights <= 0 || empty($rooms)) {

            error_log('Missing or invalid parameters.');

            return ['error' => 'Missing or invalid parameters.'];

        }

        // Add 'date' parameter (if required, use the same value as 'checkinDate')

         //$date = date('Y-m-d');

         //$date = $checkinDate;

        // Log parameters for debugging

        error_log('Region: ' . $region);
        error_log('Date: ' . $currentDate);
        error_log('Check-in Date: ' . $checkinDate);     
        error_log('Nights: ' . $nights);
        error_log('Rooms: ' . json_encode($rooms));
  
        // Prepare API request

        $api_request = [

            'jsonrpc' => '2.0',

            'method' => 'searchAvailableProperties',

            'params' => [

                "partnerid" => 1714,

                "region" => $region,

                "date" => $currentDate,

                "checkinDate" => $checkinDate,

                "nights" => $nights,

                "rooms" => $rooms,

            ],

            'id' => 1,

        ];

    

        error_log('API Request: ' . json_encode($api_request));

    

        $ch = curl_init($this->api_url);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_request));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',

            "Authorization: Bearer {$this->token}",

        ]);

    

        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            error_log('cURL Error: ' . curl_error($ch));

            curl_close($ch);

            return;

        }

        curl_close($ch);

    

        error_log('API Response: ' . $response);

    

        $response_data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            error_log('JSON Decode Error: ' . json_last_error_msg());

            return;

        }

    

        return $response_data;

    }

    

    

}



?>

