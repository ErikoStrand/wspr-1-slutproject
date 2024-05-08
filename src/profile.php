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
function getUserPosts($userID, $conn) {
  $sql = "SELECT `text`, postTime, username FROM posts WHERE userID = $userID ORDER BY postID DESC";
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

</head>

<body class="m-0 h-dvh w-full bg-zinc-900 p-0">
<?php include("include/nav.php") ?>
<?php include("include/dialog.php") ?>
<?php if (doesUserExist($profileUsername, $conn)): ?>
  <div class="max-w-screen-sm text-stone-200 px-4 mx-auto">
    <div class="flex flex-row justify-between items-center h-24">
      <div id="profile" class="flex flex-row items-center gap-4">
        <i class="fa-solid fa-user fa-2xl"></i>
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
    <div class="p">
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
