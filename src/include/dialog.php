<?php 
if (isset($_GET["following"]) && $_GET["following"] === "true") {
  $action = "FOLLOWING";
  $usernames = showFollowing($conn, $profileUserID);
} elseif (isset($_GET["followers"]) && $_GET["followers"] === "true") {
  $action = "FOLLOWERS";
  $usernames = showFollowers($conn, $profileUserID);
}  


?>

<dialog
  id="emptyDialog"
  class="w-screen max-w-md appearance-none rounded-xl bg-zinc-800 shadow-md shadow-stone-800 backdrop:backdrop-blur-md animate-fade"
>
  <div class="flex sticky top-0 p-3 flex-row justify-between pb-2 bg-zinc-800">
    <h1 class="text-4xl font-light font-archivo tracking-tight text-gray-400"><?php echo $action;?></h1>
    <button onclick="closeModal('emptyDialog')" class="p-2 text-xl font-semibold text-stone-100">X</button>
  </div>

  <div class="h-96">
    <?php if(isset($usernames)): ?>
      <div class="flex flex-col gap-2">
        <?php foreach($usernames as $username):?>
          <a class="text-stone-200 px-3 py-2 w-full h-full font-archivo font-semibold text-base hover:bg-gray-400/50 duration-200 ease-in" href="<?php echo $username["username"]?>"><?php echo $username["username"];?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</dialog>

<?php 

function showFollowers($conn, $profileUserID) {
  $result = $conn->query(
    "SELECT users.userID, users.username
    FROM users
    JOIN follow
    ON users.userID = follow.userID
    WHERE follow.followID = '$profileUserID'");
  $followers = $result->fetch_all(MYSQLI_ASSOC);
  return $followers;
}

function showFollowing($conn, $profileUserID) {
  $result = $conn->query(
    "SELECT users.userID, users.username
    FROM users
    JOIN follow
    ON users.userID = follow.followID
    WHERE follow.userID = '$profileUserID'");
  $following = $result->fetch_all(MYSQLI_ASSOC);
  return $following;
}
?>