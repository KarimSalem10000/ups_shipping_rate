<?php

// Access token
$accessToken = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzY29wZSI6WyJDWFMtVFAiXSwiUGF5bG9hZCI6eyJjbGllbnRJZGVudGl0eSI6eyJjbGllbnRLZXkiOiJsN2NkMTVhNmVkZGY4YjRiMTNiODI3ZjEyZDNiN2U4YjUwIn0sImF1dGhlbnRpY2F0aW9uUmVhbG0iOiJDTUFDIiwiYWRkaXRpb25hbElkZW50aXR5Ijp7InRpbWVTdGFtcCI6IjE5LUp1bi0yMDI0IDE3OjM1OjUyIEVTVCIsImdyYW50X3R5cGUiOiJjbGllbnRfY3JlZGVudGlhbHMiLCJhcGltb2RlIjoiU2FuZGJveCIsImN4c0lzcyI6Imh0dHBzOi8vY3hzYXV0aHNlcnZlci1zdGFnaW5nLmFwcC5wYWFzLmZlZGV4LmNvbS90b2tlbi9vYXV0aDIifSwicGVyc29uYVR5cGUiOiJEaXJlY3RJbnRlZ3JhdG9yX0IyQiJ9LCJleHAiOjE3MTg4NDAxNTIsImp0aSI6ImUwYzU5OTRkLWYxNGItNGM2Yi1iNGNlLTU1NjM5MWI1ODMzNSJ9.xh_Q5XlTixikCMzYGf16AlRRJrxvLEDfNFYXFlu3HmnBS_c1KGG9Tj5HXC5M27WNPU6SSyIf3U1XnkOlk3RrQwU34WcTgdyif8ednNhKLk0QCnnvLjnRU-QD2bEYPhnln7o5cJtG8n20CtQ1Yjirc233qJmnfWpalJVvlo-h-UXR1R0pyziU6QDSEhlop2exx5ajVLcP05kbpKYjgsmzaiWj2_gJz55u8BiCg9afyUvxpzmmWxFlZ51d4Rvu_T91XpAJc2x7Q8IgZmDJBmU-JvReLoBJM6E1aVyQ84JRgkl4NXuD715o2HX8fquJML10_cv0N0d3THD-tfuunayv5pZAbPh4M6DTY-14KPL-Uqs2-7IBqd09wbqb4hAYDdM8XCLz9z7ZgJK0iP6T4YP4VB-5LjcenXUghp-FgwpP-3y_rRLP9C375b-8X3QQRe-Qmd4ejeKTlrRcK_s60L0_qBvrdbi_iL1-44dNJpXNXtmWbQ-tKIoP-175xB1nvTQeGQhKmeDSoExCXGFJPwmf7ArffWR2VxkOagoUhmf7_jWVV5_bKEQuPQlwOzgzDhEqVA3COOzr2N5puK8SSV6OAkXtLkrL9du76EQXx-npH-kwqAAeBh848ZL5zflTh9OXE-_KfmN_MHwF4o8NaLhwN8TCOZjnKG3RF10h4xMgvmw';

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
