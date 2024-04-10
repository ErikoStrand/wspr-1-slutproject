<?php 
$error = "";

if (isset($_POST["submitIn"])) {
  $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
  $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

  if(tryLoggingIn($username, $password, $conn)) {
    //save user in session, so that you are "logged in".
    $_SESSION["username"] = $username;
    $_SESSION["userID"] = $conn->query("SELECT userID FROM users WHERE username = $username");
    header("Location: ./" . urlencode($username));
    die();
  } else {
    $error = "Incorrect password or username";
  }
}
function tryLoggingIn($username, $password, $conn) {
  $sql = 'SELECT * FROM users WHERE username = ?';

  // Prepare the statement
  $stmt = mysqli_prepare($conn, $sql);

  // Bind the username parameter to the placeholder
  mysqli_stmt_bind_param($stmt, 's', $username);

  // Execute the query
  mysqli_stmt_execute($stmt);

  // Get the result set from the prepared statement
  $result = mysqli_stmt_get_result($stmt);

  // Fetch the user data as an associative array
  $user = mysqli_fetch_assoc($result);
  if ($user) {
    $savedPassword = $user['password'];
    if (password_verify($password, $savedPassword)) {
      return true;
    } else {
      return false;
    }
  }
  }
  
echo $error;
?>

<dialog
  id="signIn"
  class="w-screen max-w-md appearance-none rounded-xl bg-zinc-800 p-4 shadow-md shadow-stone-800 backdrop:backdrop-blur-md animate-fade"
>
  <div class="flex flex-row justify-between pb-2">
    <h1 class="text-4xl font-normal font-archivo tracking-wide text-gray-400">Sign In</h1>
    <button
    id="signInClose"
    class="p-2 text-xl font-semibold text-stone-100"
  >
    X
  </button>
  </div>
  <form class="w-full" method="post" action="">
    <div class="relative z-0 w-full mb-5 group">
        <input type="text" name="username" id="floating_email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
        <label for="floating_email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Username</label>
    </div>
    <div class="relative z-0 w-full mb-5 group">
        <input type="password" name="password" id="floating_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
        <label for="floating_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Password</label>
    </div>
  
    <button type="submit" name="submitIn" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Sign In</button>
  </form>
</dialog>

