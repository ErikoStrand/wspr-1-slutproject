<?php 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "config/database.php";

function logout () {
// Swipe via memory
if (ini_get("session.use_cookies")) {
    // Prepare and swipe cookies
    $params = session_get_cookie_params();
    // clear cookies and sessions
    setcookie(session_name(), '', time() - 1000000000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Just in case.. swipe these values too
ini_set('session.gc_max_lifetime', 0);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
// Completely destroy our server sessions..
session_destroy();
}

$loggedUsername = ""; 
$thoughtActivated = filter_var($_GET["thought"], FILTER_VALIDATE_BOOLEAN);
if ($thoughtActivated) {
  $postID = filter_input(INPUT_GET, "postID", FILTER_SANITIZE_SPECIAL_CHARS);
}

$profileUsername = filter_input(INPUT_GET, "user", FILTER_SANITIZE_SPECIAL_CHARS);
if (doesUserExist($profileUsername, $conn)) {
  if (isset($_SESSION["username"])) {
    $loggedUsername = $_SESSION["username"];
    $loggedUserID = $_SESSION["userID"];
    $logged = true;
  } else {
    $logged = false;
  }
  $profileUserID = mysqli_fetch_row($conn->query("SELECT userID FROM users WHERE username = '$profileUsername'"))[0];
  $userPosts =  getUserPosts($profileUserID, $conn);
  $_SESSION["profileUserID"] = $profileUserID;
  $profileNoofFollowers = $conn->query("SELECT followID FROM follow WHERE followID = '$profileUserID'")->num_rows;
  $profileNoofFollowing = $conn->query("SELECT userID FROM follow WHERE userID = '$profileUserID'")->num_rows;
  $isLoggedFollowing = $conn->query("SELECT userID FROM follow WHERE followID = '$profileUserID'")->num_rows;
}
function doesPostExist($postID, $conn, $profileUserID) {
  $result = $conn->query("SELECT * FROM posts WHERE postID = '$postID' AND userID = '$profileUserID'");
  if ($result->num_rows > 0) {
    return true;
  } else {return false;}
}

function doesUserExist($username, $conn) {
  $sql = 'SELECT username FROM users WHERE username = ?';
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 's', $username);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  if (mysqli_stmt_num_rows($stmt) > 0) {
    return true;
  } else {
    return false;
  }
}
if (isset($_POST["logout"])) {
  logout();
  header("Refresh: 0");
}
if (isset($_POST["follow"])) {
  followPerson($profileUserID, $conn);
}
if (isset($_POST["unfollow"])) {
  unFollowPerson($profileUserID, $conn);
}

if (isset($_POST["post"])) {
  $text = filter_input(INPUT_POST, "text", FILTER_SANITIZE_SPECIAL_CHARS);
  postSomething($loggedUserID, $conn, $text, $loggedUsername);
}
if (isset($_POST["likePost"])) {
  likePost($_POST["postID"], $conn);
}
if (isset($_POST["unlikePost"])) {
  unlikePost($_POST["postID"], $conn);
}
function getPostLikes($postID, $conn) {
  $result = $conn->query("SELECT userID FROM postlikes WHERE postID = '$postID'")->num_rows;
  return $result;
}
function hasUserLikedPost($postID, $userID, $conn) {
  $result = $conn->query("SELECT userID FROM postlikes WHERE postID = '$postID' AND userID = '$userID'")->num_rows;
  if ($result > 0) {
    return true;
  } else {
    return false;
  }
}
function unlikePost($postID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("DELETE FROM postlikes WHERE userID = '$userID' AND postID = '$postID'");
  header("Refresh: 0");
}
function likePost($postID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("INSERT INTO postlikes (userID, postID) VALUES ('$userID', '$postID')");
  header("Refresh: 0");
}
function getNoofPosts($userID, $conn) {
  return $conn->query("SELECT postID FROM posts WHERE userID = '$userID'")->num_rows;
}
function getUserPosts($userID, $conn) {
  $sql = "SELECT `text`, postTime, username, postID FROM posts WHERE userID = $userID ORDER BY postID DESC";
  $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
  return $result;
}
function postSomething($userID, $conn, $text, $loggedUsername) {
  $conn->query("INSERT INTO posts (userID, `text`, username) VALUES ('$userID', '$text', '$loggedUsername')");
  header("Refresh: 0");
}

function unFollowPerson($profileUserID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("DELETE FROM follow WHERE userID = '$userID' AND followID = '$profileUserID'");
  header("Refresh: 0");
}
function getRelativeTime($timestamp) {
  $howLong = time() - $timestamp;
  if ($howLong < 60) {
    return $howLong . "s";
  } elseif ($howLong < 3600) {
    return floor($howLong / 60) . "m";
  } elseif ($howLong < 86400) {
    return floor($howLong / 3600) . "h";
  } elseif ($howLong < 86400 * 7) {
    return floor($howLong / 86400) . "d";
  } else {
    return date("F j", $timestamp);
  }
}
function followPerson($profileUserID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("INSERT INTO follow (userID, followID) VALUES ('$userID', '$profileUserID')");
  header("Refresh: 0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/output.css" rel="stylesheet" />
  <script
      defer
      src="https://kit.fontawesome.com/0a145d8537.js"
      crossorigin="anonymous"
    ></script>
  <title>Document</title>
  <script defer src="/src/dialog.js"></script>
  <script defer src="/src/lib/scripts/keepscroll.js"></script>
</head>

<body class="m-0 h-dvh w-full bg-zinc-900 p-0">
<?php include("include/nav.php") ?>
<?php include("include/dialog.php") ?>
<?php if (doesUserExist($profileUsername, $conn)): ?>
  <div class="max-w-screen-sm text-stone-200 px-4 mx-auto">
    
    <!-- user posts // if true a singular post is being viewed.-->
    <?php if(!$thoughtActivated): ?>
      <div class="flex flex-row justify-between items-center h-24">
        <div id="profile" class="flex flex-row items-center gap-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="34px" height="44px" class="fill-stone-200"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>
          <h1 class="text-2xl font-archivo font-semibold"><?php echo $profileUsername?></h1>
          <?php if(!$logged):?>
            <button onclick="openModal('signIn');" class="font-archivo font-medium bg-blue-500 rounded-xl border-2 border-blue-400 px-3 py-1">Follow</button>
          <?php elseif($profileUsername != $loggedUsername && !$isLoggedFollowing): ?>
            <form action="" method="post"><button id="follow" type="submit" name="follow" class="font-archivo font-medium bg-blue-500 rounded-xl border-2 border-blue-400 px-3 py-1">Follow</button></form>
          <?php elseif($profileUsername != $loggedUsername && $isLoggedFollowing): ?>
            <form action="" method="post"><button id="unfollow" type="submit" name="unfollow" class="font-archivo font-medium bg-red-500 rounded-xl border-2 border-red-400 px-3 py-1">Unfollow</button></form>
          <?php elseif($profileUsername == $loggedUsername):?>
            <form action="" method="post"><button id="logout" type="submit" name="logout" class="font-archivo font-medium bg-red-500 rounded-xl border-2 border-red-400 px-3 py-1">Log Out</button></form>
          <?php endif; ?>
          
        </div>
          <div id="profileStats" class="flex flex-row gap-2">
            <form action="" method="get"><button onclick="openModal('emptyDialog')" value=true class="flex flex-col items-center" name="following"><span class="text-2xl font-mono font-bold"><?php echo $profileNoofFollowing?></span><span class="text-xs font-archivo">FOLLOWING</span></button></form>
            <form action="" method="get"><button onclick="openModal('emptyDialog')" value=true class="flex flex-col items-center" name="followers"><span class="text-2xl font-mono font-bold"><?php echo $profileNoofFollowers?></span><span class="text-xs font-archivo">FOLLOWERS</span></button></form>
          </div>
      </div>
      <!--all user posts-->
      <?php include "include/posts.php";?>

    <!--singular post-->
    <?php elseif($thoughtActivated && doesPostExist($postID, $conn, $profileUserID)): ?>
      <div id="mainsingularcontent">
        <nav class="flex flex-row gap-4 font-archivo font-bold text-2xl items-center">
        <a href="/src/<?php echo $profileUsername?>" class="rounded-full hover:bg-zinc-800 duration-200 ease-in-out p-2">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="28px" height="28px" class="fill-stone-200"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/></svg>
        </a>
        <h1>Thought</h1>
        </nav>
      </div>

    <?php else: ?>
      <!--post or username and postid is incorrect-->
      <div class="text-stone-200 text-2xl text-center">
        <h1>The user did not have a post with the id <?php echo $postID;?></h1>
        <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
      </div>
    <?php endif; ?>

  </div>

<?php else: ?>
  <div class="text-stone-200 text-2xl text-center">
    <h1>This account does unfortunately not exist.</h1>
    <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
  </div>
<?php endif; ?>

</body>
</html>
