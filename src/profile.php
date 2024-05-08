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
function hasUserUploadedData($userID, $conn) {
  $sql = "SELECT userID FROM generaldata WHERE userID = $userID UNION
          SELECT userID FROM moviedata WHERE userID = $userID UNION
          SELECT userID FROM showdata WHERE userID = $userID";
  $result = $conn->query($sql)->num_rows;
  if ($result > 0) {
    return true;
  } else {
    return false;
  }
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
  <script defer src="dialog.js"></script>
  <script src="lib/scripts/keepscroll.js"></script>


</head>

<body class="m-0 h-dvh w-full bg-zinc-900 p-0">
<?php include("include/nav.php") ?>
<?php include("include/dialog.php") ?>
<?php if (doesUserExist($profileUsername, $conn)): ?>
  <div class="max-w-screen-sm text-stone-200 px-4 mx-auto">
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
    <!-- user posts -->
    <div class="" id="containerforallthepostsevenwiththeinput">
      <?php if ($profileUsername == $loggedUsername):?>
        <form action="" method="post" class="flex flex-col gap-4">
          <textarea id="text" name="text" class="resize-none overflow-hidden bg-transparent border-[1px] border-zinc-700 placeholder:text-zinc-400 rounded-md p-2 appearance-none text-zinc-400" oninput="autoResize(this)" placeholder="Type your thoughts here..."></textarea>
          <button id="post" type="submit" name="post" class="font-archivo font-medium self-end bg-yellow-300 rounded-md border-2 border-yellow-400 text-zinc-800 px-3 py-1 w-48">Post a Thought</button>
        </form>
      <?php endif; ?>

      <div id="postsContainer" class="mt-6 flex flex-col gap-2 divide-y divide-zinc-700">
        <div id="menuforposts">
          <h2 class="font-archivo text-2xl font-semibold px-2">Posts</h2>
        </div>

        <div id="thePosts" class="divide-y flex flex-col divide-zinc-700 gap-1">
          <?php foreach($userPosts as $post):?>
            <div id="postContainer" class="p-2 font-archivo">
              <div class="flex flex-row gap-1">
                <a class="font-bold hover:underline" href="<?php echo $post["username"]?>"><?php echo $post["username"];?></a>
                <span class="text-xs self-center">-</span>
                <div class="text-zinc-400 font-light self-end"><?php echo getRelativeTime($post["postTime"]);?></div>
              </div>
              <div class="font-light"><?php echo $post["text"];?></div>
              <div id="postNavbar" class="flex flex-row justify-between font-mono">
                <!-- will control comments unsure how to do it, maybe new window?-->
                <form class="py-1"><button class="flex flex-row gap-1 items-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="15px" height="15px" class="fill-stone-200"><path d="M123.6 391.3c12.9-9.4 29.6-11.8 44.6-6.4c26.5 9.6 56.2 15.1 87.8 15.1c124.7 0 208-80.5 208-160s-83.3-160-208-160S48 160.5 48 240c0 32 12.4 62.8 35.7 89.2c8.6 9.7 12.8 22.5 11.8 35.5c-1.4 18.1-5.7 34.7-11.3 49.4c17-7.9 31.1-16.7 39.4-22.7zM21.2 431.9c1.8-2.7 3.5-5.4 5.1-8.1c10-16.6 19.5-38.4 21.4-62.9C17.7 326.8 0 285.1 0 240C0 125.1 114.6 32 256 32s256 93.1 256 208s-114.6 208-256 208c-37.1 0-72.3-6.4-104.1-17.9c-11.9 8.7-31.3 20.6-54.3 30.6c-15.1 6.6-32.3 12.6-50.1 16.1c-.8 .2-1.6 .3-2.4 .5c-4.4 .8-8.7 1.5-13.2 1.9c-.2 0-.5 .1-.7 .1c-5.1 .5-10.2 .8-15.3 .8c-6.5 0-12.3-3.9-14.8-9.9c-2.5-6-1.1-12.8 3.4-17.4c4.1-4.2 7.8-8.7 11.3-13.5c1.7-2.3 3.3-4.6 4.8-6.9c.1-.2 .2-.3 .3-.5z"/></svg><div><?php echo 0; ?></div></button></form>
                
                <!-- controls likes and unlikes-->
                <?php if ($logged):?>
                  <?php if (!hasUserLikedPost($post["postID"], $loggedUserID, $conn)):?>
                    <form class="py-1" method="post"><button class="flex flex-row gap-1 items-center"name="likePost" id="likePost" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="15px" height="15px" class="fill-stone-200"><path d="M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8v-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5v3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20c0 0-.1-.1-.1-.1c0 0 0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5v3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2v-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z"/></svg><div class=""><?php echo getPostLikes($post["postID"], $conn);?></div></button><input type="hidden" name="postID" value="<?php echo $post["postID"];?>"></form>
                  <?php else: ?>
                    <form class="py-1" method="post"><button class="flex flex-row gap-1 items-center" name="unlikePost" id="unlikePost" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="15px" height="15px" class="fill-red-500"><path d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/></svg><div class=""><?php echo getPostLikes($post["postID"], $conn);?></div></button><input type="hidden" name="postID" value="<?php echo $post["postID"];?>"></form>
                  <?php endif; ?>
                <!-- displays sign in popup if not logged in :)-->
                <?php else: ?>
                  <div class="py-1"><button onclick="openModal('signIn');" class="flex flex-row gap-1 items-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="15px" height="15px" class="fill-stone-200"><path d="M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8v-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5v3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20c0 0-.1-.1-.1-.1c0 0 0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5v3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2v-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z"/></svg><div class=""><?php echo getPostLikes($post["postID"], $conn);?></div></button><input type="hidden" name="postID" value="<?php echo $post["postID"];?>"></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <div class="text-stone-200 text-2xl text-center">
    <h1>This account does unfortunately not exist.</h1>
    <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
  </div>
<?php endif; ?>

</body>
</html>
