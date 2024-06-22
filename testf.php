<?php

// Initialize a cURL session
$ch = curl_init();

// Set the URL for the POST request
curl_setopt($ch, CURLOPT_URL, 'https://apis-sandbox.fedex.com/oauth/token');

// Indicate that this is a POST request
curl_setopt($ch, CURLOPT_POST, 1);

// Set the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
    'grant_type' => 'client_credentials',
    'client_id' => 'l7cd15a6eddf8b4b13b827f12d3b7e8b50',
    'client_secret' => '45069dacbfa94424a5ad592b8f4ecbb8'
)));

// Return the transfer as a string instead of outputting it directly
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Set the headers
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the cURL session and store the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Output the response
    echo $response;
}

// Close the cURL session
curl_close($ch);
