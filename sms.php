<?php

function sendSms($recipientPhones, $message) {
   
// Configs from env for the SMS API
	// These values should be set in your environment variables or configuration file
    $url = "";
    $username = "";
    $apiKey = "";
    $senderId = "";


    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "apiKey: " . $apiKey
    ];

    $payload = [
        "username"    => $username,
        "phoneNumbers" => $recipientPhones,
        "message"     => $message,
        "senderId"    => $senderId
    ];

  

    try {

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => implode("\r\n", $headers) . "\r\n",
                "content" => json_encode($payload),
                "ignore_errors" => true 
            ]
        ];

        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception('Error occurred while making the request.');
        }

        echo $response;
    } catch (Exception $ex) {
        error_log("An error occurred: " . $ex->getMessage());
        throw $ex;
    }
}

// Helper function to format phone numbers
/**
 * Format phone numbers to the desired format.
 *
 * @param string $phoneNumber The phone number to format.
 * @return string The formatted phone number.
 */
function formatPhoneNumber($phoneNumber) {

    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    if (str_starts_with($phoneNumber, '0')) {
        return preg_replace('/^0/', '+254', $phoneNumber, 1);
    }

    if (str_starts_with($phoneNumber, '254')) {
        return preg_replace('/^254/', '+254', $phoneNumber, 1);
    }

    if (str_starts_with($phoneNumber, '+')) {
        return $phoneNumber;
    }

    return '+254' . $phoneNumber;
}



?>
