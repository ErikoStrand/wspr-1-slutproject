<?php
include "config/database.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
function insertMovie($conn, $array) {
  if(isset($_SESSION["userID"])) {
    $id = $_SESSION["userID"];
    $sql = "INSERT INTO moviedata (userID, totalMedia, totalWatchtimeMinutes, totalWatchtimeHours, totalDirectors, averageRating, averageRatingIMDB)
    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss",
                    $id,
                    $array["totalMedia"],
                    $array["totalWatchtimeMinutes"],
                    $array["totalWatchtimeHours"],
                    $array["totalDirectors"],
                    $array["averageRating"],
                    $array["averageRatingIMDB"]);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    } else {
      echo "Record inserted successfully.";
    }

    $stmt->close();
  } else {
    return "errrr";
  }
}

function insertGeneral($conn, $array) {
  if(isset($_SESSION["userID"])) {
    $id = $_SESSION["userID"];
    $sql = "INSERT INTO generaldata (userID, highestStreak, currentStreak, totalMedia, totalAverageRating, averageMediaPerMonth, averageMediaPerWeek, weeksSinceStart)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss",
                    $id,
                    $array["streak"]["highestStreak"],
                    $array["streak"]["currentStreak"],
                    $array["totalMedia"],
                    $array["totalAverageRating"],
                    $array["averageMediaPerMonth"],
                    $array["averageMediaPerWeek"],
                    $array["weeksSinceStart"]);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    } else {
      echo "Record inserted successfully.";
    }

    $stmt->close();
  } else {
    return "errrr";
  }
}
function insertShow($conn, $array) {
  if(isset($_SESSION["userID"])) {
    $id = $_SESSION["userID"];
    $sql = "INSERT INTO showdata (userID, totalMedia, totalWatchtimeMinutes, totalWatchtimeHours, averageRating, averageRatingIMDB)
    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss",
                    $id,
                    $array["totalMedia"],
                    $array["totalWatchtimeMinutes"],
                    $array["totalWatchtimeHours"],
                    $array["averageRating"],
                    $array["averageRatingIMDB"]);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    } else {
      echo "Record inserted successfully.";
    }

    $stmt->close();
  } else {
    return "errrr";
  }
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
    insertMovie($conn, $movies);
    echo "done movies";
  }
  if (isset($_POST["shows"])) {
    $shows = cleanData((array)json_decode($_POST["shows"]));
    insertShow($conn, $shows);
    echo "done shows";

  }
  if (isset($_POST["generalData"])) {
    $generalData = cleanData((array)json_decode($_POST["generalData"]));
    insertGeneral($conn, $generalData);
    echo "done general";
  }

  // Process the data 
} else {
  // If the script is not accessed via an HTTP request, return an error message
  echo "Error: This script can only be accessed via an HTTP request.";
}
?>