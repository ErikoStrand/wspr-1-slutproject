<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
function insertData() {
  
}
function cleanData($array) {
  $cleanArray = array();
  foreach($array as $key => $value) {
    if (!$value instanceof stdClass) {
      $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
      $key = filter_var($key, FILTER_SANITIZE_SPECIAL_CHARS);
      $cleanArray[$key] = $value;
    } elseif($value instanceof stdClass) {
      //looping thru nested arrays.
      foreach($value as $nestedKey => $nestedValue) {
        $nestedValue = filter_var($nestedValue, FILTER_SANITIZE_SPECIAL_CHARS);
        $nestedKey = filter_var($nestedKey, FILTER_SANITIZE_SPECIAL_CHARS);
        $cleanArray[$key][$nestedKey] = $nestedValue;
      }
    }
  }
  return $cleanArray;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Retrieve the data sent from the client
  if (isset($_POST["movies"])) {
    $movies = cleanData((array)json_decode($_POST["movies"]));
    $_SESSION["movies"] = $movies;
    echo "done movies";
  }
  if (isset($_POST["shows"])) {
    $shows = cleanData((array)json_decode($_POST["shows"]));
    $_SESSION["shows"] = $shows;
    echo "done shows";

  }
  if (isset($_POST["generalData"])) {
    $generalData = cleanData((array)json_decode($_POST["generalData"]));
    $_SESSION["generalData"] = $generalData;
    print_r($generalData);
    echo "done general";
  }

  // Process the data 
} else {
  // If the script is not accessed via an HTTP request, return an error message
  echo "Error: This script can only be accessed via an HTTP request.";
}
?>