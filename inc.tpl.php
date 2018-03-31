<!DOCTYPE html>
<html>
  <head>
     <meta charset="UTF-8">
     <title>Search amongst your Gists</title>
     <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>
      <div class="container"><strong>Gist</strong> search</div>
    </h1>

    <div class="container">
      <div id="login" class="form">
        <script>var isLoggedIn = <?= ($_SESSION["username"] ? 1 : 0) ?>;</script>

        <?php if(!isset($_SESSION["username"])) { ?>
          <form method="POST">
            <button class="btn" name="login" type="submit">Login</button>
          </form>
        <?php } else { ?>
          Logged in as <?= $_SESSION["username"] ?>
        <?php } ?>
      </div>
    </div>

     <script src="main.js"></script>
  </body>
</html>