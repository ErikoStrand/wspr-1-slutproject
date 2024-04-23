<?php
$minutes = 0;
$hours = 0;
// Check if the script is being accessed via an HTTP request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the data sent from the client
    $data = (array)json_decode($_POST["data"]);
    $json = (array)json_decode(file_get_contents("../data/episodes_data.json"));
    
    // Process the data 
    foreach ($data as $key => $value) {
        //cleans string
        $key = cleanString($key);
        
        //cleans value
        if (!is_int($value)) {
            $value = 20;
        }

        if ($value <= 120) {
            if (array_key_exists($key, $json)) {
                $minutes += $value * $json[$key];
            }
        } else {
            $minutes += $value;
        }
    }
    $hours = $minutes/60;
    echo $minutes . " " . $hours;
} else {
    // If the script is not accessed via an HTTP request, return an error message
    echo "Error: This script can only be accessed via an HTTP request.";
}

function cleanString($string) { 
    return preg_replace("/[^a-zA-Z0-9_-]/", "", $string); // Removes special chars.
}
