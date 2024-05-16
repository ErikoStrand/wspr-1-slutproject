<!-- user posts -->
      <div id="postsContainer" class="mt-6 flex flex-col gap-2">
        <div id="menuforposts" class="flex flex-row items-center gap-2 border-b-[1px] border-zinc-800">
          <h2 class="font-archivo text-2xl font-semibold">Posts</h2>
          <span>-</span>
          <h3 class="font-mono font-bold text-xl self-end text-zinc-400"><?php echo getNoofPosts($profileUserID, $conn);?></h3>
        </div>
        <div id="thePosts" class="flex flex-col hover:cursor-pointer">
          <?php foreach($userPosts as $post):?>
            <!--href="<?php echo $post["username"];?>/thought/<?php echo $post["postID"];?>"-->
            <div id="postContainer" class="p-2 font-archivo hover:bg-zinc-800/50 border-b-[1px] border-zinc-800" onclick="navigateToPost('<?php echo $post['username'];?>', <?php echo $post['postID'];?>)">
              <div class="flex flex-row gap-1">
                <a class="font-bold hover:underline" href="/src/<?php echo $post["username"]?>"><?php echo $post["username"];?></a>
                <span class="text-xs self-center">-</span>
                <div class="text-zinc-400 font-light self-end"><?php echo getRelativeTime($post["postTime"]);?></div>
              </div>
              <p class="font-light break-words"><?php echo formatText($post["text"]);?></p>
              <?php $navPost = $post;?>
              <?php include "include/bottomPostNav.php"?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>