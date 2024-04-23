<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_POST['movies'])) {
    $movies = $_POST['movies'];
    $_SESSION["movies"] = $movies;
    // You can now use the email in your PHP script
}
if (isset($_POST['shows'])) {
  $shows = $_POST['shows'];
  $_SESSION["shows"] = $shows;
  // You can now use the email in your PHP script
}
if (isset($_POST['generalData'])) {
  $generalData = $_POST['generalData'];
  $_SESSION["generalData"] = $generalData;
  // You can now use the email in your PHP script
}
?>