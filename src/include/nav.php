<?php session_start(); ?>
<?php 
include("config/database.php");
include("include/login.php");
?>
<nav
      class="flex h-12 flex-row items-center justify-between border-b-[1px] border-b-zinc-800 drop-shadow-xl"
    >
      <div>
        <a
          class="block px-4 font-heebo text-lg font-bold leading-10 text-stone-50"
          href="index.php"
          >Bloob</a
        >
      </div>
      <ul
        class="flex flex-row gap-4 px-6 font-heebo text-base font-semibold tracking-wide text-stone-50"
      >
        <li
          class="self-center rounded-md duration-200 ease-in-out hover:bg-zinc-700/50"
        >
          <a href="#" class="block p-2 leading-10" title="Help & FAQ"
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 fill-stone-50"
              viewBox="0 0 512 512"
            >
              <path
                d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"
              />
            </svg>
          </a>
        </li>
        <li
          class="rounded-md px-2 duration-200 ease-in-out hover:bg-zinc-700/50"
        >
          <a href="index.php" class="block leading-10" title="Home">Home</a>
        </li>
        <?php if (!isset($_SESSION["username"])):?>
        <li
          class="rounded-md px-2 duration-200 ease-in-out hover:bg-zinc-700/50"
        >
          <button id="signInButton" class="block leading-10" title="Profile">Sign In</button>
        </li>
        <?php else: ?>
          <li
          class="rounded-md px-2 duration-200 ease-in-out hover:bg-zinc-700/50"
        >
          <a href="<?php echo $_SESSION["username"]?>" id="profile" class="block leading-10" title="Profile"><?php echo $_SESSION["username"]?></a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>