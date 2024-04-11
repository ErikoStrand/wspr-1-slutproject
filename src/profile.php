<?php 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "config/database.php";
if (isset($_SESSION["username"])) {
  $loggedUser = $_SESSION["username"];
  $logged = true;
} else {
  $logged = false;
}
$loggedUser = "";
$profileUsername = filter_input(INPUT_GET, "user", FILTER_SANITIZE_SPECIAL_CHARS);
$profileUserID = mysqli_fetch_row($conn->query("SELECT userID FROM users WHERE username = '$profileUsername'"))[0];

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
function followPerson($profileUserID, $conn) {
  $userID = $_SESSION["userID"];
  $conn->query("INSERT INTO follow (userID, followID) VALUES ('$userID', '$profileUserID')");
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

<?php if (doesUserExist($profileUsername, $conn)): ?>

  <div class="max-w-screen-xl text-stone-200 px-4 mx-auto">
    <div class="flex flex-row justify-between items-center h-24">
      <div id="profile" class="flex flex-row items-center gap-4">
        <i class="fa-solid fa-user fa-2xl"></i>
        <h1 class="text-2xl font-archivo font-semibold"><?php echo $profileUsername?></h1>
        <?php if(!$logged):?>
        <button onclick="openModal();" class="font-archivo font-medium bg-blue-500 rounded-xl border-2 border-blue-400 px-3 py-1">Follow</button>
        <?php elseif($profileUsername != $loggedUser): ?>
        <form action="" method="post"><button id="follow" type="submit" name="follow" class="font-archivo font-medium bg-blue-500 rounded-xl border-2 border-blue-400 px-3 py-1">Follow</button></form>
        <?php endif; ?>
      </div>
      <div id="profileStats" class="flex flex-row gap-2">
        <h4 class="flex flex-col text-center"><span class="text-2xl font-mono font-bold">0</span><span class="text-xs font-archivo">FOLLOWING</span></h4>
        <h4 class="flex flex-col text-center"><span class="text-2xl font-mono font-bold">0</span><span class="text-xs font-archivo">FOLLOWERS</span></h4>
      </div>
    </div>
  </div

<?php else: ?>
  <div class="text-stone-200 text-2xl text-center">
    <h1><?php echo $profileUsername;?></h1>
    <h1>This account does unfortunately not exist.</h1>
    <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
  </div>
<?php endif; ?>
</body>
</html>
