<?php 
include "config/database.php";
$errorName = "";
$errorPassword = "";


// something is wrong with this.
function doesUserExist($email, $username, $conn) {
  $sql = 'SELECT username, email FROM users WHERE username = ? OR email = ?';

  // Prepare the statement
  $stmt = mysqli_prepare($conn, $sql);

  // Bind the parameters to the placeholders
  mysqli_stmt_bind_param($stmt, 'ss', $username, $email);

  // Execute the query
  mysqli_stmt_execute($stmt);

  // Store the result
  mysqli_stmt_store_result($stmt);

  // Check the number of rows that match the query
  if (mysqli_stmt_num_rows($stmt) > 0) {
    // A user with the given username or email exists
    return true;
  } else {
    // No user found with the given username or email
    return false;
  }
}
function registerUser($email, $username, $password, $conn) {
  $sql = "INSERT INTO users (username, email, `password`) VALUES ('$username', '$email', '$password')";
  if (mysqli_query($conn, $sql)) {
    // success
    header('Location: login.php');
    die();
  } else {
    // error
    echo 'Error: ' . mysqli_error($conn);
  }
}

if (isset($_POST["submit"])) {
  $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
  $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
  $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
  if (!ctype_alnum($username)) {
    $errorName = "Only alphanumeric characters allowed.";
  }

  if (str_contains($username, " ")) {
    $errorName .= "Can't contain whitespaces";
  }

  if (strlen($password) >= 50) {
    $errorPassword = "Password can't be longer then 50 characters.";
  } else {
    $password = password_hash($password, PASSWORD_DEFAULT);
  }
  if (empty($errorName) && empty($errorPassword)) {
    if (!doesUserExist($email, $username, $conn)) {
      registerUser($email, $username, $password, $conn);
    } else {
      echo "username or email already taken.";
    }
  }

  echo $errorName . $errorPassword;
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
<body class="h-dvh bg-zinc-900">

<div
      class="mx-auto h-full flex max-w-md flex-col justify-center items-center pl-6 pr-6 sm:pb-0"
    >
  <h1 class="text-gray-400 text-4xl tracking-wide font-semibold">Register</h1>
  <form class="w-full" method="post" action="">
    <div class="relative z-0 w-full mb-5 group">
        <input type="email" name="email" id="floating_email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
        <label for="floating_email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Email address</label>
    </div>
    <div class="relative z-0 w-full mb-5 group">
        <input type="text" name="username" id="floating_email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
        <label for="floating_email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Username</label>
    </div>
    <div class="relative z-0 w-full mb-5 group">
        <input type="password" name="password" id="floating_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
        <label for="floating_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Password</label>
    </div>
  
    <button type="submit"  name="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Sign Up</button>
  </form>
</div>

</body>
</html>