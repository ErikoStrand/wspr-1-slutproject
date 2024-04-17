<?php 
if (isset($_GET["following"]) && $_GET["following"] === "true") {
  $action = "following";
  $usernames = showFollowing($conn, $profileUserID);
} elseif (isset($_GET["followers"]) && $_GET["followers"] === "true") {
  $action = "followers";
  $usernames = showFollowers($conn, $profileUserID);
}  


?>

<dialog
  id="emptyDialog"
  class="w-screen max-w-md appearance-none rounded-xl bg-zinc-800 shadow-md shadow-stone-800 backdrop:backdrop-blur-md animate-fade"
>
  <div class="flex sticky top-0 p-3 flex-row justify-between pb-2 bg-zinc-800">
    <h1 class="text-4xl font-normal font-archivo tracking-wide text-gray-400"><?php echo $action;?></h1>
    <button onclick="closeModal('emptyDialog')" class="p-2 text-xl font-semibold text-stone-100">X</button>
  </div>

  <div class="h-96">
    <?php if(isset($usernames)): ?>
      <div class="flex flex-col gap-2">
        <?php foreach($usernames as $username):?>
          <a class="text-stone-200 px-3 py-2 w-full h-full font-archivo font-semibold text-base hover:bg-gray-400/50 duration-200 ease-in" href="<?php echo $username[1]?>"><?php echo $username[1];?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</dialog>

<?php 

function showFollowers($conn, $profileUserID) {
  $followersArray = array();
  $result = $conn->query("SELECT userID FROM follow WHERE followID = '$profileUserID'");
  while ($row = $result->fetch_assoc()) {
    $userID = $row["userID"];
    $username = mysqli_fetch_assoc($conn->query("SELECT username FROM users WHERE userID = '$userID'"))["username"];
    $followersArray[] = array($userID, $username);
  }
  return $followersArray;
}
function showFollowing($conn, $profileUserID) {
  $followingArray = array();
  $result = $conn->query("SELECT followID FROM follow WHERE userID = '$profileUserID'");
  while ($row = $result->fetch_assoc()) {
    $userID = $row["followID"];
    $username = mysqli_fetch_assoc($conn->query("SELECT username FROM users WHERE userID = '$userID'"))["username"];
    $followingArray[] = array($userID, $username);
    
  }
  return $followingArray;
}
?>