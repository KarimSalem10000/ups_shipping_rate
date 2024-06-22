/**
* Requires libcurl
*/
<?php


// Set your application credentials
$clientID = 'IPfM41sqLVodA9NfSUHZAAJ8AoGXR2XgShpgXjrsLqAwGJju';
$clientSecret = '81o4lr2eDS2ytW0QushIXmID2CxEn9PPf5i0oH5P3S6tL2G8kafbUG9ll5C6ItxF';


$curl = curl_init();

$payload = "grant_type=refresh_token&refresh_token=string";

curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
        "Authorization: Basic " . base64_encode($clientID . ":" . $clientSecret)
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_URL => "https://wwwcie.ups.com/security/v1/oauth/refresh",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
]);

$response = curl_exec($curl);
$error = curl_error($curl);

curl_close($curl);

if ($error) {
    echo "cURL Error #:" . $error;
} else {
    echo $response;
}
