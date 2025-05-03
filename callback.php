<?php

// Set error reporting to log errors
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors to the user
// Log errors to a file
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log'); 


require 'sms.php'; 

// OpenAI API key or any other model key to configure
// This should be set in your environment variables or a secure location
$openai_api_key = "YOUR_OPENAI";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read raw request body
    $rawData = file_get_contents('php://input');

    // Log the raw data before processing
    logReceivedData($rawData);

     // Parse the raw data
    parse_str($rawData, $parsedData);

    $from = isset($parsedData['from']) ? $parsedData['from'] : null;
    $message = isset($parsedData['text']) ? $parsedData['text'] : null;


    $chatgptResponse = queryChatGPT(str_replace("Test6", "",$message));
    

    try {
        sendSms([$from], $chatgptResponse);
        
        echo "Response sent successfully.";
    } catch (Exception $e) {
        echo "Error sending message: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
// Function to log received data
/**
 * Log received data to a JSON file.
 *
 * @param string $data The data to log.
 * @param string $logFile The name of the log file.
 */
function logReceivedData($data, $logFile = 'received_data.json') {
    try {
        $logFilePath = __DIR__ . '/' . $logFile;

        // Decode existing JSON file if it exists
        $existingData = [];
        if (file_exists($logFilePath)) {
            $existingData = json_decode(file_get_contents($logFilePath), true) ?? [];
        }

        // Add timestamp and new data
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        $existingData[] = $entry;

        // Save back to the file
        file_put_contents($logFilePath, json_encode($existingData, JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        error_log("Failed to log received data: " . $e->getMessage());
    }
}

// Function to process the query with OpenAI or any other model for this case
function queryChatGPT($query) {
	// Define the scope of the assistant, for example:
	// "Questions about mental health and well being.
    $scope = "Questions about mental health and well being.
              Queries about basic menstrual hygiene and menstrual health tips and general wellness
              Questions related to gender based violence, especially towards women and girls.";
    
    global $openai_api_key;

	// Define the system message
	// This message sets the context for the assistant, for example:
	// "You are a helpful assistant. You...
    $systemMessage = "You are a helpful assistant. You assist girls and women with queries related to".$scope."Based on the context of a user's query, analyse if it falls within the given areas then respond accordingly. Sometimes users may not be quite accurate with what
    they want. Use the query as a guide to which the topic falls in. If a user asks something outside these, inform them politely to ask queries related to menstrual healthy/hygine, mental health or gender based violence. 
    Respond with a max of 500 character response because you response will be shared to the user via SMS. Be as professionally detailed as possible";

    // API payload
    $data = [
        "model" => "gpt-4o-mini",// Use the appropriate model
        "messages" => [
            ["role" => "system", "content" => $systemMessage],
            ["role" => "user", "content" => $query]
        ]
    ];

    // API call to OpenAI
    $url = "";// OpenAI API endpoint
	// This should be set in your environment variables or a secure location or can be defined in the code
    $options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n" .
                        "Authorization: Bearer $openai_api_key\r\n",
            "method" => "POST",
            "content" => json_encode($data),
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return "Sorry, I encountered an error. Please try again.";
    }

    $responseData = json_decode($response, true);
    return $responseData['choices'][0]['message']['content'] ?? "No response received.";// return default message if no response or parsed message from model to the user
}


