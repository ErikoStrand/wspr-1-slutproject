<?php 
include "config/database.php";
$username = $_GET["user"];
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

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/output.css" rel="stylesheet" />
  <title>Document</title>
</head>

<body class="m-0 h-dvh w-full bg-zinc-900 p-0">
<?php include("include/nav.php") ?>

<?php if (doesUserExist($username, $conn)): ?>
  <h1 class=""><?php echo $username?></h1>






<?php else: ?>
  <div class="text-stone-200 text-2xl text-center">
    <h1>This account does unfortunately not exist.</h1>
    <a href="index.php" class="text-blue-500 underline" >Go back to Bloob</a>
  </div>
<?php endif; ?>
</body>
</html>
