<?php

// Access token
$accessToken = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJDWFMtVFAiXSwiUGF5bG9hZCI6eyJjbGllbnRJZGVudGl0eSI6eyJjbGllbnRLZXkiOiJsN2NkMTVhNmVkZGY4YjRiMTNiODI3ZjEyZDNiN2U4YjUwIn0sImF1dGhlbnRpY2F0aW9uUmVhbG0iOiJDTUFDIiwiYWRkaXRpb25hbElkZW50aXR5Ijp7InRpbWVTdGFtcCI6IjE3LUp1bC0yMDI0IDA4OjM2OjA3IEVTVCIsImdyYW50X3R5cGUiOiJjbGllbnRfY3JlZGVudGlhbHMiLCJhcGltb2RlIjoiU2FuZGJveCIsImN4c0lzcyI6Imh0dHBzOi8vY3hzYXV0aHNlcnZlci1zdGFnaW5nLmFwcC5wYWFzLmZlZGV4LmNvbS90b2tlbi9vYXV0aDIifSwicGVyc29uYVR5cGUiOiJEaXJlY3RJbnRlZ3JhdG9yX0IyQiJ9LCJleHAiOjE3MjEyMjY5NjcsImp0aSI6ImFhMTIyMGViLWE2Y2YtNDc0OS1iMjYwLWYyZTJkMmYzYWVjMSJ9.xu1qv7YVs_ncTecVVAz46CObGt_gZcx3nKgJhvCxPxHdV1lMH_vfbOpQGaAoqF4pf0HPEiLifSSNiayu_A8GfzRZiWDt6hXsbtKm85QC7JoBZK1i3KmvPzIE-_iV6DZ-cSaQAouwaNz9pdajjIMAFA-GK9x9_MuEsWjaJrztMzdgCfNoXrVMrBl_h2PccPkfmqf2HRV1PHO0revsJXLP9ziKY6Xpl7w-IarIfKV2qbWo0VmUbsXLvWznIYVm07ZrFLMUQd0YLNTXsL0QUTzJWID5OgyqvvzObaSO2ZUbHUitarzzXype7UFSx4FJt1Bvqess4Es5ZwxSpPTQbag5JZhsQ8JNM6F8zM5aU7lCfI6ZAlETJOXqe86l8jnV8GjEO5LG_iycrMGAJ2PEVZsrdAs6P6DLemrWCUtFNfJgrHbz43lqkS4HLtGkgoYK5S-9OmJGwsCke9uodvvF1BUKHDRe6ok2bMkdqSSRdlc4lyrsrAIq-GgZ1oTay_enDrD8_T9orSWv_PJ_kk1WzZvWL1eABAq6jWM5XGvvW5AQI7rjx-ETMK3j1JYJY5ylw6XrrKoTM9AtR_j75Ypd1EAkaVY0P5HPTfMu9IVdtaQ4FBMz6Q6XpC3iQBy2-ZoUZztV-FOtEUORw-N9jdEFqrjAp-_wVBU0Y_hbgmE38f5dc-0';

// JSON payload
$input = json_encode(array(
    "accountNumber" => array(
        "value" => "740561073"
    ),
    "rateRequestControlParameters" => array(
        "returnTransitTimes" => true
    ),
    "requestedShipment" => array(
        "shipper" => array(
            "address" => array(
                "postalCode" => "94105", // Example postal code
                "countryCode" => "US" // Example country code
            )
        ),
        "recipient" => array(
            "address" => array(
                "postalCode" => "10001", // Example postal code
                "countryCode" => "US" // Example country code
            )
        ),
        "pickupType" => "DROPOFF_AT_FEDEX_LOCATION",
        "rateRequestType" => array("ACCOUNT"), // Make sure this field is correctly included
        "requestedPackageLineItems" => array(
            array(
                "groupPackageCount" => 1,
                "weight" => array(
                    "units" => "LB",
                    "value" => 10
                ),
                "dimensions" => array(
                    "length" => 10,
                    "width" => 10,
                    "height" => 10,
                    "units" => "IN"
                )
            )
        )
    )
));

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://apis-sandbox.fedex.com/rate/v1/rates/quotes');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the output
curl_setopt($ch, CURLINFO_HEADER_OUT, true); // Track the request headers

$headers = array();
$headers[] = 'Authorization: Bearer ' . $accessToken;
$headers[] = 'X-locale: en_US';
$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Get the request headers
    $requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    echo "Request Headers:\n" . $requestHeaders . "\n\n";

    // Separate response headers and body
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Output headers and body
    echo "Response Headers:\n" . $header . "\n\n";

    // Decompress the body if it's gzip encoded
    if (substr($body, 0, 2) === "\x1f\x8b") {
        $body = gzdecode($body);
    }

    echo "Response Body:\n" . $body . "\n";

    // Parse the JSON response
    $data = json_decode($body, true);

    // Check for errors in the response
    if (isset($data['errors'])) {
        foreach ($data['errors'] as $error) {
            echo "Error: " . $error['message'] . "\n";
        }
    } else {
        // Define the desired service type
        $desiredServiceType = 'FEDEX_GROUND'; // Change this to the service type you want

        // Find and print the total cost for the desired service type
        if (isset($data['output']['rateReplyDetails'])) {
            foreach ($data['output']['rateReplyDetails'] as $rateDetail) {
                if ($rateDetail['serviceType'] === $desiredServiceType) {
                    $totalCost = $rateDetail['ratedShipmentDetails'][0]['totalNetCharge'];
                    echo "Total cost for $desiredServiceType: $totalCost\n";
                    break;
                }
            }
        } else {
            echo "No rateReplyDetails found in the response.\n";
        }
    }
}

curl_close($ch);
