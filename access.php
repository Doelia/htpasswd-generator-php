<?php

/**
 * Générateur de htaccess/htpasswd pour sécuriser des repertoires
 *
 * @auhor Stéphane Wouters <doelia@doelia.fr>
 * @date 5 Apr. 2016
 * https://github.com/Doelia/htpasswd-generator-php
 *
 */

class Path {
    public function __construct($f) {
        $this->name = $f;
        $this->path = $path = __DIR__.'/'.$f;
        $this->htpasswd = $path.'/.htpasswd';
        $this->htaccess = $path.'/.htaccess';
    }

    public function setAccess($login, $password) {
        $password = crypt($password, base64_encode($password));
        $content = "$login:$password";
        file_put_contents($this->htpasswd, $content);

        // htaccess
        $content = "AuthUserFile ".$this->htpasswd."
AuthGroupFile /dev/null
AuthName \"Accès Restreint\"
AuthType Basic
require valid-user";

        file_put_contents($this->htaccess, $content);
    }

    public static function getFolderList() {
        $tab = array();
        foreach (scandir(__DIR__) as $v) {
            if (is_dir($v) && $v != '..') {
                $tab[] = new Path(basename($v));
            }
        }
        return $tab;
    }

    public function removeAccess() {
        @unlink($this->htaccess);
        @unlink($this->htpasswd);
    }

    public function isSecure() {
        return @file_get_contents($this->htpasswd) !== FALSE;
    }

    public function mkdir() {
        @mkdir($this->path);
    }
}

if (isset($_POST['add'])) {
    $d = new Path($_POST['path']);
    if ($_POST['login']) {
        $d->setAccess($_POST['login'], $_POST['password']);
    } else {
        $d->removeAccess();
    }
}

if (isset($_GET['removeProtect'])) {
    (new Path($_GET['removeProtect']))->removeAccess();
}

if (isset($_POST['mkdir'])) {
    (new Path($_POST['path']))->mkdir();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sécurisation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

</head>
<body>

    <div class="container">

        <h2>Sécurisation de répertoire par login/pass</h2>

        <h3>Liste des répertoires</h3>
        <table class="table table-hover">
            <?php foreach (Path::getFolderList() as $d): ?>
                <tr>
                    <td>Repertoire <strong>/<?php echo $d->name; ?></strong></td>
                    <td style="width: 70%">
                        <?php if (isset($_GET['protect']) && $_GET['protect'] == $d->name) { ?>
                            <form class="form-inline" action="<?php echo basename(__FILE__); ?>" method="post">
                                <input type="hidden" name="path" value="<?php echo $d->name; ?>">
                                <input type="text" placeholder="Login" name="login" class="form-control">
                                <input type="password" placeholder="Mot de passe" name="password" class="form-control">
                                <input type="submit" name="add" class="form-control" value="Protéger">
                            </form>
                        <?php } else { ?>
                            <?php if ($d->isSecure()) { ?>
                                <a href="?removeProtect=<?php echo $d->name ?>" class="btn btn-danger">Retirer la protection</a>
                            <?php } else { ?>
                                <a href="?protect=<?php echo $d->name ?>" class="btn btn-success">Protéger</a>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="/<?php echo $d->name; ?>" class="btn btn-primary">Tester l'accès</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Créer un nouveau répertoire</h3>
        <form class="form-inline" action="<?php echo basename(__FILE__); ?>" method="post">
            <input type="text" class="form-control" placeholder="Repertoire" name="path">
            <input type="submit" name="mkdir" class="form-control" value="Créer">
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
