<?php 
if (isset($_GET["following"]) && $_GET["following"] === "true") {
  $action = "following";
  $usernames = showFollowing($conn, $profileUserID);
} 

?>

<dialog
  id="emptyDialog"
  class="w-screen max-w-md appearance-none rounded-xl bg-zinc-800 shadow-md shadow-stone-800 backdrop:backdrop-blur-md animate-fade"
>
  <div class="flex sticky top-0 p-4 flex-row justify-between pb-2 bg-zinc-800">
    <h1 class="text-4xl font-normal font-archivo tracking-wide text-gray-400"><?php echo $action;?></h1>
    <button onclick="closeModal('emptyDialog')" class="p-2 text-xl font-semibold text-stone-100">X</button>
  </div>

  <div class="h-96">
    <?php foreach($usernames as $username):?>
      <div class="py-2 px-3">
        <h1 class="text-stone-200 font-archivo font-semibold text-base"><?php echo $username?></h1>
      </div>
    <?php endforeach; ?>
  </div>
</dialog>

<?php 

function showFollowers($conn, $profileUserID) {
  $followersArray = array();
  $result = $conn->query("SELECT userID FROM follow WHERE followID = '$profileUserID'");
  while ($row = $result->fetch_assoc()) {
    $followersArray[] = $row["userID"];
  }

  $match = implode(",", $followersArray);
  $result2 = $conn->query("SELECT username FROM users WHERE followID IN ($match)");
  if ($result2) {
    $usernames = array();
    while ($row = $result2->fetch_assoc()) {
      $usernames[] = $row['username'];
    }
    return $usernames;
  } else {
    //did not get result or no following.
  }
  
}
function showFollowing($conn, $profileUserID) {
  $followingArray = array();
  $result = $conn->query("SELECT followID FROM follow WHERE userID = '$profileUserID'");
  while ($row = $result->fetch_assoc()) {
    $followingArray[] = $row["followID"];
  }

  $match = implode(",", $followingArray);
  $result2 = $conn->query("SELECT username FROM users WHERE userID IN ($match)");
  if ($result2) {
    $usernames = array();
    while ($row = $result2->fetch_assoc()) {
      $usernames[] = $row['username'];
    }
    return $usernames;
  } else {
    //did not get result or no following.
  }
  
}
?>