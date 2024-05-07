<?php 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "config/database.php";


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
if (isset($_POST["follow"])) {
  followPerson($profileUserID, $conn);
}
if (isset($_POST["unfollow"])) {
  unFollowPerson($profileUserID, $conn);
}
function unFollowPerson($profileUserID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("DELETE FROM follow WHERE userID = '$userID' AND followID = '$profileUserID'");
  header("Refresh: 0");
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
  <div class="max-w-screen-xl text-stone-200 px-4 mx-auto">
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
          
        <?php endif; ?>
        
      </div>
      <div id="profileStats" class="flex flex-row gap-2">
        <form action="" method="get"><button onclick="openModal('emptyDialog')" value=true class="flex flex-col items-center" name="following"><span class="text-2xl font-mono font-bold"><?php echo $profileNoofFollowing?></span><span class="text-xs font-archivo">FOLLOWING</span></button></form>
        <form action="" method="get"><button onclick="openModal('emptyDialog')" value=true class="flex flex-col items-center" name="followers"><span class="text-2xl font-mono font-bold"><?php echo $profileNoofFollowers?></span><span class="text-xs font-archivo">FOLLOWERS</span></button></form>
      </div>
    </div>
      
    </div>
  </div

<?php else: ?>
  <div class="text-stone-200 text-2xl text-center">
    <h1>This account does unfortunately not exist.</h1>
    <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
  </div>
<?php endif; ?>

</body>
</html>
