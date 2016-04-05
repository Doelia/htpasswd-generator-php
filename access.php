<?php

function getFolderList() {
    $tab = array();
    foreach (scandir(__DIR__) as $v) {
        if (is_dir($v) && $v != '..') {
            $tab[] = $v;
        }
    }
    return $tab;
}

function setAccess($path, $login, $password) {
    // htpasswd
    $password = crypt($password, base64_encode($password));;
    $content = "$login:$password";
    file_put_contents($path.'/.htpasswd', $content);

    // htaccess
    $content = "AuthUserFile ".$path.'/.htpasswd'."
AuthGroupFile /dev/null
AuthName \"Accès Restreint\"
AuthType Basic
require valid-user";

    file_put_contents($path.'/.htaccess', $content);
}

function removeAccess($path) {
    @unlink($path.'/.htpasswd');
    @unlink($path.'/.htaccess');
}

function getLogin($path) {
    if (($content = @file_get_contents($path.'/.htpasswd')) !== FALSE) {
        $v = explode(':', $content);
        return $v[0];
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

if (isset($_GET['removeProtect'])) {
    $d = __DIR__.'/'.$_GET['removeProtect'];
    removeAccess($d);
}

if (isset($_POST['mkdir'])) {
    $d = __DIR__.'/'.$_POST['path'];
    @mkdir($d);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 101 Template</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

</head>
<body>

    <div class="container">

        <h2>Générateur de fichier htaccess/htpasswd dans une arborescence</h2>

        <h3>Liste des répertoires</h3>
        <table class="table table-hover">
            <?php foreach (getFolderList() as $d): ?>
                <tr>
                    <td>Repertoire <strong>/<?php echo $d; ?></strong></td>
                    <td style="width: 70%">
                    <?php if (isset($_GET['protect']) && $_GET['protect'] == $d) { ?>
                        <form class="form-inline" action="<?php echo basename(__FILE__); ?>" method="post">
                            <input type="hidden" name="path" value="<?php $v = explode('/', $d); echo end($v); ?>">
                            <input type="text" placeholder="Login" name="login" class="form-control" value="<?php echo getLogin($d); ?>">
                            <input type="password" placeholder="Mot de passe" name="password" class="form-control" value="<?php echo ($v = getLogin($d)) ? '*******' : '' ; ?>">
                            <input type="submit" name="add" class="form-control" value="Protéger">
                        </form>
                    <?php } else { ?>
                        <?php if (getLogin($d)) { ?>
                            <a href="?removeProtect=<?php echo $d ?>" class="btn btn-danger">Retirer la protection</a>
                        <?php } else { ?>
                            <a href="?protect=<?php echo $d ?>" class="btn btn-success">Protéger</a>
                        <?php } ?>
                    <?php } ?>
                    </td>
                    <td>
                        <a href="/<?php echo basename($d) ?>" class="btn btn-primary">Tester</a>
                    </td>

                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Créer un nouveau repertoire</h3>
        <form class="form-inline" action="<?php echo basename(__FILE__); ?>" method="post">
            <input type="text" class="form-control" placeholder="Repertoire" name="path">
            <input type="submit" name="mkdir" class="form-control" value="Créer">
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
