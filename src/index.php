<!doctype html>
<html lang="en" class="m-0 p-0">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home Page</title>
    <link href="./output.css" rel="stylesheet" />

    <script
      defer
      src="https://kit.fontawesome.com/0a145d8537.js"
      crossorigin="anonymous"
    ></script>
    <script defer src="dialog.js"></script>
  </head>

  <body
    class="m-0 h-dvh w-full bg-zinc-900 p-0"
  >
    <?php include("include/nav.php") ?>
    <?php include("include/register.php")?>
    

    <div class="text-stone-200 max-w-screen-md h-full mx-auto text-4xl font-archivo font-semibold flex flex-col gap-2 text-center justify-center items-center">
      <h1>Upload your thoughts.</h1>
      <h1>& find the inner you.</h1>
      <button onclick="openModal('signUp')" class="text center mt-4 bg-blue-500 px-4 py-2 rounded-md font-archivo text-lg font-bold leading-6 tracking-wide text-stone-200">
        Get Started, it's Mindbending.
      </button>
    </div>

  </body>
</html>