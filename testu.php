<?php

// Function to get OAuth Token
function getOAuthToken($consumerKey, $consumerSecret)
{
    $tokenUrl = "https://api.usps.com/oauth2/v3/token"; // Correct URL for token generation
    $postData = [
        'grant_type' => 'client_credentials',
        'client_id' => $consumerKey,
        'client_secret' => $consumerSecret
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl Error:' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatusCode !== 200) {
        echo 'HTTP Error Code: ' . $httpStatusCode . "\n";
        echo 'Response: ' . $response . "\n";
        return null;
    }

    $responseData = json_decode($response, true);
    return $responseData['access_token'] ?? null;
}

// Function to get shipping options and prices
function getShippingOptions($accessToken, $originZIPCode, $destinationCountryCode, $foreignPostalCode, $packageDescription, $pricingOptions)
{
    $shippingUrl = "https://api.usps.com/shipments/v3/options/search";
    $postData = [
        'originZIPCode' => $originZIPCode,
        'destinationCountryCode' => $destinationCountryCode,
        'foreignPostalCode' => $foreignPostalCode,
        'packageDescription' => $packageDescription,
        'pricingOptions' => $pricingOptions
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $shippingUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl Error:' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatusCode !== 200) {
        echo 'HTTP Error Code: ' . $httpStatusCode . "\n";
        echo 'Response: ' . $response . "\n";
        return null;
    }

    $responseData = json_decode($response, true);
    return $responseData;
}

// Main script to use the functions
$consumerKey = 'MkyH00KPTG1MVLm9YfGtmYBfUxx2fwkT';
$consumerSecret = '7KXv7Cws3Lx8nKKq';

// Get OAuth Token
$accessToken = getOAuthToken($consumerKey, $consumerSecret);
if (!$accessToken) {
    echo json_encode(["error" => "Failed to get OAuth token."]);
    exit;
}

// Define the shipping request parameters
$originZIPCode = '90210'; // Example origin ZIP code
$destinationCountryCode = 'US'; // Example destination country code
$foreignPostalCode = '10001'; // Example foreign postal code
$packageDescription = [
    'weight' => 10.5, // in pounds
    'length' => 12, // in inches
    'height' => 8, // in inches
    'width' => 6, // in inches
    'girth' => 15, // in inches, updated to be greater than 0
    'mailClass' => 'PRIORITY_MAIL_INTERNATIONAL', // Example mail class for international shipping
    'extraServices' => [],
    'packageValue' => 100, // in USD
    'mailingDate' => '2024-07-01' // Example mailing date
];
$pricingOptions = [
    [
        'priceType' => 'COMMERCIAL',
        'paymentAccount' => [
            'accountType' => 'EPS',
            'accountNumber' => '123456789' // Example account number
        ]
    ]
];

// Get shipping options and prices
$shippingOptions = getShippingOptions($accessToken, $originZIPCode, $destinationCountryCode, $foreignPostalCode, $packageDescription, $pricingOptions);

if (!$shippingOptions) {
    echo json_encode(["error" => "Failed to get shipping options.", "token" => $accessToken]);
} else {
    echo json_encode(["token" => $accessToken, "shippingOptions" => $shippingOptions], JSON_PRETTY_PRINT);
}
