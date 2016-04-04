<?php

function getFolderList() {
    $tab = array();
    foreach (scandir(__DIR__) as $v) {
        if (is_dir($v)) {
            $tab[] = $v;
        }
    }
    return $tab;
}

function setAccess($path, $login, $password) {
    $content = "$login:$password";
    @mkdir($path);
    file_put_contents($path.'/.htpasswd', $content);
}

function removeAccess($path) {
    unlink($path.'/.htpasswd');
}

function getLogin($path) {
    if (($content = @file_get_contents($path.'/.htpasswd')) !== FALSE) {
        return $content;
    }
    return false;

}

if (isset($_POST['add'])) {
    $d = __DIR__.'/'.$_POST['path'];
    if ($_POST['login']) {
        setAccess($d, $_POST['login'], $_POST['password']);
    } else {
        removeAccess($d);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 101 Template</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

  </head>
  <body>

      <div class="container">
          <?php foreach (getFolderList() as $d): ?>
              <form class="" action="" method="post">
                  <input type="text" name="path" value="<?php $v = explode('/', $d); echo end($v); ?>">
                  <input type="text" name="login" value="<?php echo getLogin($d); ?>">
                  <input type="password" name="password" value="<?php echo ($v = getLogin($d)) ? '*******' : '' ; ?>">
                  <input type="submit" name="add" value="Save">
              </form>
          <?php endforeach; ?>
          Nouveau dossier :
          <form class="" action="" method="post">
              <input type="text" name="path" value="">
              <input type="text" name="login" value="">
              <input type="password" name="password" value="">
              <input type="submit" name="add" value="Save">
          </form>
          Suppremier le login/pass pour retirer la protection
      </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  </body>
</html>
