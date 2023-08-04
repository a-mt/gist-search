<!DOCTYPE html>
<html>
  <head>
     <meta charset="UTF-8">
     <title>Search amongst your Gists</title>
     <link rel="stylesheet" href="static/octicons.css">
     <link rel="stylesheet" href="static/style.css">
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  </head>
  <body>
    <h1>
      <div class="container"><strong>Gist</strong> search</div>
    </h1>

    <div class="container">
      <div id="login" class="form">
        <script>var isLoggedIn = <?= (isset($_SESSION["username"]) ? 1 : 0) ?>;</script>

        <?php if(!isset($_SESSION["username"])) { ?>
          <form method="POST">
            <button class="btn primary" name="login" type="submit">Login</button>
          </form>
        <?php } else { ?>
          Logged in as <?= $_SESSION["username"] ?>

          <div class="right">
            <a href="?sync" class="btn gray">Refresh session</a>
            <a href="?logout">Logout</a>
          </div>
        <?php } ?>
      </div>

      <?php if(isset($_SESSION["username"])) { ?>
        <div id="results"></div>
      <?php } ?>
    </div>

    <?php if(defined("GITHUB_REPO")) { ?>
      <footer>
        <a class="github" href="<?= GITHUB_REPO ?>" title="Find me on Github">
          <img src="https://cdn.rawgit.com/a-mt/4eee1459b499f6970e667f39c12b9e63/raw/146f66fcf3794a0fcd5a7a810341124825246fa1/Octicons-mark-github.svg" alt="Github" height="30">
        </a>
      </footer>
    <?php } ?>

     <script src="static/main.js"></script>
  </body>
</html>