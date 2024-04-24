<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Retrieve the data sent from the client
  if (isset($_POST["movies"])) {
    $movies = (array)json_decode($_POST["movies"]);
    $_SESSION["movies"] = $movies;
    echo "done movies";
  }
  if (isset($_POST["shows"])) {
    $shows = (array)json_decode($_POST["shows"]);
    $_SESSION["shows"] = $shows;
    echo "done shows";

  }
  if (isset($_POST["generalData"])) {
    $generalData = (array)json_decode($_POST["generalData"]);
    $_SESSION["generalData"] = $generalData;
    echo "done general";
  }

  // Process the data 
} else {
  // If the script is not accessed via an HTTP request, return an error message
  echo "Error: This script can only be accessed via an HTTP request.";
}
?>