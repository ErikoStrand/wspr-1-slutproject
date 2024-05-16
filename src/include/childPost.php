<div id="postContainer" class="font-archivo hover:bg-zinc-800/50 border-b-[1px] border-zinc-800" onclick="navigateToPost('<?php echo $postComment['username'];?>', <?php echo $postComment['postID'];?>)">
  <div class="flex flex-row gap-1">
    <a class="font-bold hover:underline" href="/<?php echo $postComment["username"]?>"><?php echo $postComment["username"];?></a>
    <span class="text-xs self-center">-</span>
    <div class="text-zinc-400 font-light self-end"><?php echo getRelativeTime($postComment["postTime"]);?></div>
  </div>
  <p class="font-light break-words"><?php echo formatText($postComment["text"]);?></p>
  <?php $navPost = $postComment;?>
  <?php include "include/bottomPostNav.php";?>
</div>