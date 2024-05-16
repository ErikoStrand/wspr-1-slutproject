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
// får bara göras om man är inne i singular post.
if (isset($_POST["commentPost"])) {
  $text = filter_input(INPUT_POST, "commentText", FILTER_SANITIZE_SPECIAL_CHARS);
  postSomething($loggedUserID, $conn, $text, $loggedUsername, $postID);
}

if (isset($_POST["likePost"])) {
  likePost($_POST["postID"], $conn);
}

if (isset($_POST["unlikePost"])) {
  unlikePost($_POST["postID"], $conn);
}
if (isset($_POST["deletePost"])) {
  deletePost($_POST["postID"], $conn);
}
function getPostLikes($postID, $conn) {
  $result = $conn->query("SELECT userID FROM postlikes WHERE postID = '$postID'")->num_rows;
  return $result;
}
function getNoofPostComments($postID, $conn) {
  $result = $conn->query("SELECT userID FROM posts WHERE parentID = '$postID'")->num_rows;
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
  return $conn->query("SELECT postID FROM posts WHERE userID = '$userID' AND parentID = 0")->num_rows;
}
function deletePost($postID, $conn) {
  $sql = "DELETE FROM posts WHERE postID = '$postID'";
  if($conn->query($sql)) {
    header("Refresh: 0");
    "sucess"; //maybe ad a pop up=?=!=!;
  }
}
function getUserPost($postID, $conn) {
  $sql = "SELECT `text`, postTime, username, userID, postID FROM posts WHERE postID = $postID";
  $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC)[0];
  return $result;
}
function getUserPosts($userID, $conn) {
  $sql = "SELECT `text`, postTime, username, postID, userID FROM posts WHERE userID = $userID AND parentID = 0 ORDER BY postID DESC";
  $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
  return $result;
}
function postSomething($userID, $conn, $text, $loggedUsername, $parentID = NULL) {
  $conn->query("INSERT INTO posts (userID, `text`, username, parentID) VALUES ('$userID', '$text', '$loggedUsername', '$parentID')");
  header("Refresh: 0");
}

function unFollowPerson($profileUserID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("DELETE FROM follow WHERE userID = '$userID' AND followID = '$profileUserID'");
  header("Refresh: 0");
}
//parentID is postID
function getPostComments($parentID, $conn) {
  $sql = "SELECT `text`, postTime, username, postID, userID FROM posts WHERE parentID = $parentID ORDER BY postID DESC";
  $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
  return $result;
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
function convertUnixToFormattedDate($timestamp) {
  return date('H:i · M j, Y', $timestamp);
}
function formatText($text) {
  //deocdes html entities to cor characters then replaces them with new line.
  return str_replace("\r\n", "<br>", html_entity_decode($text));
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
  <link href="/output.css" rel="stylesheet" />
  <script
      defer
      src="https://kit.fontawesome.com/0a145d8537.js"
      crossorigin="anonymous"
    ></script>
  <title>Document</title>
  <script defer src="/dialog.js"></script>
  <script defer src="/lib/scripts/keepscroll.js"></script>
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
        <!--handles post input.-->
        <?php if ($profileUsername == $loggedUsername):?>
          <form action="" method="post" class="flex flex-col gap-4">
            <textarea id="text" name="text" class="resize-none overflow-hidden bg-transparent border-[1px] border-zinc-800 placeholder:text-zinc-400 rounded-md p-2 appearance-none text-zinc-400" oninput="autoResize(this)" placeholder="Type your thoughts here..."></textarea>
            <button id="post" type="submit" name="post" class="font-archivo font-medium self-end bg-yellow-300 rounded-md border-2 border-yellow-400 text-zinc-800 px-3 py-1 w-48">Post a Thought</button>
          </form>
        <?php endif; ?>
      <!--all user posts-->
      <?php include "include/posts.php";?>

    <!--singular post-->
    <?php elseif($thoughtActivated && doesPostExist($postID, $conn, $profileUserID)): ?>
      <?php $singlePost = getUserPost($postID, $conn);?>
      <div id="mainsingularcontent" class="flex flex-col gap-2 font-archivo border-b-[1px] border-zinc-800">
        <nav class="flex flex-row gap-4 font-archivo font-bold text-2xl items-center relative -left-2 mb-4">
          <a href="javascript:history.back()" class="rounded-full hover:bg-zinc-800 duration-200 ease-in-out p-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="28px" height="28px" class="fill-stone-200"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/></svg>
          </a>
          <h1>Thought</h1>
        </nav>
        <header id="profilenameandpossibleaprofilepictureinthefuture." class="flex flex-row gap-2 items-center mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="38px" height="38px" class="fill-stone-200"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>
          <a class="font-bold hover:underline text-xl" href="/<?php echo $singlePost["username"]?>"><?php echo $singlePost["username"];?></a>
        </header>
        <p id="thoughttext" class="font-light break-words"><?php echo formatText($singlePost["text"]);?></p>
        <h3><?php echo convertUnixToFormattedDate($singlePost["postTime"]);?></h3>
        <!--likes and todo comments-->
        <?php $navPost = $singlePost;?>
        <?php include "include/bottomPostNav.php";?>
        <?php if ($logged):?>
          <div class="py-2">
            <h2 class="">Replying to <span class="text-blue-400"><?php echo "@". $singlePost["username"];?></span></h2>
            <form action="" method="post" class="flex flex-col gap-2">
              <textarea id="text" name="commentText" class="resize-none overflow-hidden h-6 bg-transparent placeholder:text-zinc-400 text-lg rounded-md outline-none text-zinc-400" oninput="autoResize(this)" placeholder="Type your reply here"></textarea>
              <button id="commentPost" type="submit" name="commentPost" class="font-archivo font-medium self-end bg-blue-500 rounded-full border-2 border-blue-400 text-stone-200 px-3 py-1 w-24">Reply</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
      <div id="postsContainer" class="mt-6 flex flex-col gap-2">
        <?php $postComments = getPostComments($postID, $conn)?>
        <div id="thePosts" class="flex flex-col">
          <?php foreach($postComments as $postComment):?>
            <?php include "include/childPost.php"?>
          <?php endforeach; ?>
        </div>
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
