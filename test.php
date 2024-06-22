<?php

/**
 * Requires libcurl
 */
// Set your application credentials
$clientID = 'IPfM41sqLVodA9NfSUHZAAJ8AoGXR2XgShpgXjrsLqAwGJju';
$clientSecret = '81o4lr2eDS2ytW0QushIXmID2CxEn9PPf5i0oH5P3S6tL2G8kafbUG9ll5C6ItxF';
$curl = curl_init();


$payload = "grant_type=client_credentials";

curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
        "Authorization: Basic " . base64_encode($clientID . ":" . $clientSecret)
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_URL => "https://wwwcie.ups.com/security/v1/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
]);

$response = curl_exec($curl);
$error = curl_error($curl);
$responseData = json_decode($response, true);


curl_close($curl);

if ($error) {
    echo "cURL Error #:" . $error;
} else {
    // Use this access token to make API Calls.
    echo "<h1>Use this access token to make API Calls</h1><br>";
    echo $responseData['access_token'];
    echo "<hr>";
    // Use this refresh Token to get a new access token when the access token expires.
    echo "<h1>Use this refresh Token to get a new access token when the access token expires.</h1><br>";
    echo $responseData['refresh_token'];
    echo "<hr>";
    echo "by default I am saving these tokens in a txt file in the \"./tokens\" folder";

    // Determine the folder path (assuming the "tokens" folder is in the same directory as this PHP script)
    $folderPath = __DIR__ . '/tokens';

    // Create the folder if it doesn't exist
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // Determine the file path within the "tokens" folder
    $filePath = $folderPath . '/usersTokens.json';

    // Write the variable to the file
    file_put_contents($filePath, json_encode($responseData));

    echo "Variable saved to file: $filePath";
}
