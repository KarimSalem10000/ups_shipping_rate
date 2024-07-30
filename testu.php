<?php

// Function to get OAuth Token
function getOAuthToken($consumerKey, $consumerSecret)
{
    $tokenUrl = "https://api.usps.com/oauth2/v3/token";
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

// Function to get shipping rates
function getShippingRates($accessToken, $userId, $packageDetails)
{
    $url = "http://production.shippingapis.com/ShippingAPI.dll?API=RateV4";

    $xmlRequest = "
    <RateV4Request USERID='{$userId}'>
        <Revision>2</Revision>
        <Package ID='0'>
            <Service>PRIORITY</Service>
            <ZipOrigination>{$packageDetails['ZipOrigination']}</ZipOrigination>
            <ZipDestination>{$packageDetails['ZipDestination']}</ZipDestination>
            <Pounds>{$packageDetails['Pounds']}</Pounds>
            <Ounces>{$packageDetails['Ounces']}</Ounces>
            <Container></Container>
            <Machinable>TRUE</Machinable>
        </Package>
    </RateV4Request>";

    $url .= '&xml=' . urlencode($xmlRequest);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
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

    $responseData = simplexml_load_string($response);
    return $responseData;
}

// Main script to use the functions
$consumerKey = 'jFkNW03sWReefGWG0kjyVoM3uBATHh3G';
$consumerSecret = '3GeuEDfBikhGpsXK';
$userId = '826ADISY3274'; // Replace with your actual USPS Web Tools User ID

// Get OAuth Token
$accessToken = getOAuthToken($consumerKey, $consumerSecret);
if (!$accessToken) {
    echo json_encode(["error" => "Failed to get OAuth token."]);
    exit;
}

// Define the package details for rate calculation
$packageDetails = [
    'ZipOrigination' => '07305',
    'ZipDestination' => '26301',
    'Pounds' => 8,
    'Ounces' => 2
];

// Get shipping rates
$shippingRates = getShippingRates($accessToken, $userId, $packageDetails);

if (!$shippingRates) {
    echo json_encode(["error" => "Failed to get shipping rates.", "token" => $accessToken]);
} else {
    echo json_encode(["token" => $accessToken, "shippingRates" => $shippingRates], JSON_PRETTY_PRINT);
}
