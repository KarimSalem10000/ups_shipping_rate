<?php

// Access token
$accessToken = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJDWFMtVFAiXSwiUGF5bG9hZCI6eyJjbGllbnRJZGVudGl0eSI6eyJjbGllbnRLZXkiOiJsN2NkMTVhNmVkZGY4YjRiMTNiODI3ZjEyZDNiN2U4YjUwIn0sImF1dGhlbnRpY2F0aW9uUmVhbG0iOiJDTUFDIiwiYWRkaXRpb25hbElkZW50aXR5Ijp7InRpbWVTdGFtcCI6IjI0LUp1bC0yMDI0IDIxOjA1OjUzIEVTVCIsImdyYW50X3R5cGUiOiJjbGllbnRfY3JlZGVudGlhbHMiLCJhcGltb2RlIjoiU2FuZGJveCIsImN4c0lzcyI6Imh0dHBzOi8vY3hzYXV0aHNlcnZlci1zdGFnaW5nLmFwcC5wYWFzLmZlZGV4LmNvbS90b2tlbi9vYXV0aDIifSwicGVyc29uYVR5cGUiOiJEaXJlY3RJbnRlZ3JhdG9yX0IyQiJ9LCJleHAiOjE3MjE4NzY3NTMsImp0aSI6IjA5Y2I5ODM3LWQxNGEtNGQwZC04ODA5LTcyYzM5OGQzNjk0NSJ9.u0aody0NomvmokXKna5UiW05pxghTD-dlqdBwG4Q4nt2OP-58db9cS4zP5unycJbzN_POq0Zx8R6PvLHJ8X9aWRRXSYY7oY8r2CKZ-ZnmZTC7Wp2pekpFZnxqsPX04ke3hNy5_kZ8jxJTQZ8P6sgoilJysd0qewKTX0L3w6NL7Pm9NuDbUJqQ_u7__55tJiw3pBbI0lEHwe34ypshYX4adfI54eVgYmxTPvSaNuqVOg-_pgq4_YCXxVnQAc55zjciC5t07o5ttglrUVBQ_sjoydtADY5XPI5oKjnDIzJbLJ5NSOMZTAhd1g9kyMNCkfhG8fpwsiddNIy0L_OkxWf2dEWs4mL-QvtHZt8ggnQ-tijEDUSVvStQKyuXzXoMAbWQGUQ_zqFwgaGuaRtz1ItFHyrBHpYguP0k6qfc4_CJPi3sXKW6YIhKfCODo-BjTKvQWK1Z2bhttmmWX3sjHlivwUGNredAkXETb3-SZq8nAC75khIn_zrUvrIMDCpYcfAXSMNpMJsvk1BgKpyprSOMCZRDZoeXm1sRlzB1Zy1lZEYaM9O5A4XG5DnyDZyl5PX1eZ51NYiuwT950rG6N2CLmtbVcXrD4IKzTuI6-lfH0j2SLvT4bq9FL8Vap73BgdQvHPgAHNXWzjA7J6d6X1PC97OPKkdkMcMQqn6LTbplRM'; // Replace with the new access token

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
                "postalCode" => "12345", // Example postal code
                "countryCode" => "US" // Example country code
            )
        ),
        "recipient" => array(
            "address" => array(
                "postalCode" => "67890", // Example postal code
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
                    "value" => 2.0
                ),
                "dimensions" => array(
                    "length" => 10,
                    "width" => 10,
                    "height" => 10,
                    "units" => "IN"
                ),
                "packagingType" => "FEDEX_BOX" // Example packaging type
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

// Disable SSL verification (not recommended for production)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
        if (isset($data['rateReplyDetails'])) {
            foreach ($data['rateReplyDetails'] as $rateDetail) {
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





