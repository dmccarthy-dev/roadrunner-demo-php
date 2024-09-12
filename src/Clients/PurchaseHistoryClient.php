<?php

namespace Roadrunnerdemo\Php\Clients;

class PurchaseHistoryClient
{

    public function getPurchaseHistory(string $id)
    {

        $url = "http://localhost:8080/api/v2/purchase-history/" . $id;

        // Initialize a cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response instead of outputting it directly
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

        // Execute the GET request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return 'Error: ' . $error_msg;
        }

        // Close cURL session
        curl_close($ch);

        return json_decode($response);
    }
}