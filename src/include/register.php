<?php 
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
  } else {
    // error
    echo 'Error: ' . mysqli_error($conn);
  }
}

if (isset($_POST["submitUp"])) {
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
      //echo "username or email already taken.";
    }
  }
}
?>

<dialog
  id="signUp"
  class="w-screen max-w-md appearance-none rounded-xl bg-zinc-800 p-4 shadow-md shadow-stone-800 backdrop:backdrop-blur-md animate-fade"
>
  <div class="flex flex-row justify-between">
    <h1 class="text-4xl font-normal font-archivo tracking-wide text-gray-400">Join Bloob</h1>
    <button
    id="signUpClose"
    class="p-2 text-xl font-semibold text-stone-100"
  >
    X
  </button>
  </div>
  <form class="w-full py-2" method="post" action="">
    <div class="group relative z-0 mb-5 w-full">
      <input
        type="email"
        name="email"
        id="floating_email"
        class="peer block w-full appearance-none border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2.5 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
        placeholder=" "
        required
      />
      <label
        for="floating_email"
        class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-600 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-500"
        >Email address</label
      >
    </div>
    <div class="group relative z-0 mb-5 w-full">
      <input
        type="text"
        name="username"
        id="floating_email"
        class="peer block w-full appearance-none border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2.5 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
        placeholder=" "
        required
      />
      <label
        for="floating_email"
        class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-600 rtl:peer-focus:left-auto rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-500"
        >Username</label
      >
    </div>
    <div class="group relative z-0 mb-5 w-full">
      <input
        type="password"
        name="password"
        id="floating_password"
        class="peer block w-full appearance-none border-0 border-b-2 border-gray-300 bg-transparent px-0 py-2.5 text-sm text-gray-900 focus:border-blue-600 focus:outline-none focus:ring-0 dark:border-gray-600 dark:text-white dark:focus:border-blue-500"
        placeholder=" "
        required
      />
      <label
        for="floating_password"
        class="absolute top-3 -z-10 origin-[0] -translate-y-6 scale-75 transform text-sm text-gray-500 duration-300 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:start-0 peer-focus:-translate-y-6 peer-focus:scale-75 peer-focus:font-medium peer-focus:text-blue-600 rtl:peer-focus:translate-x-1/4 dark:text-gray-400 peer-focus:dark:text-blue-500"
        >Password</label
      >
    </div>

    <button
      type="submit"
      name="submitUp"
      class="rounded-lg bg-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
    >
      Sign Up
    </button>
  </form>

</dialog>
