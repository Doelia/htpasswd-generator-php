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
        file_put_contents($this->htpasswd, "$login:$password");
        file_put_contents($this->htaccess, "AuthUserFile ".$this->htpasswd."
AuthGroupFile /dev/null
AuthName \"Accès Restreint\"
AuthType Basic
require valid-user");
    }

    public static function getFolderList() {
        return array_map(function($v) { return new Path(basename($v)); },
            array_filter(scandir(__DIR__), function($v) {
                return (is_dir($v) && $v != '..');
            })
        );
    }

    public function isSecureByRecursive() {
        return !file_exists($this->htaccess)
            && $this->name != "."
            && (new Path('.'))->isSecure();
    }

    public function setSecureByRecursivity() {
        @unlink($this->htaccess);
        @unlink($this->htpasswd);
    }

    public function removeAccess() {
        $this->setSecureByRecursivity();
        file_put_contents($this->htaccess, "Satisfy any");
    }

    public function isSecure() {
        return @file_get_contents($this->htpasswd) !== FALSE;
    }

    public function mkdir() {
        mkdir($this->path);
        $this->removeAccess();
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

if (isset($_GET['setSecureByRecursivity'])) {
    (new Path($_GET['setSecureByRecursivity']))->setSecureByRecursivity();
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
                    <td>
                        Repertoire <strong>/<?php echo $d->name; ?></strong>
                    </td>
                    <td>
                        <?php if ($d->isSecure()) { ?>
                            <span class="label label-danger">Protégé</span>
                        <?php } else { ?>
                            <?php if ($d->isSecureByRecursive()) { ?>
                                <span class="label label-warning">Protégé par récursivité</span>
                            <?php } else { ?>
                                <span class="label label-success">Public</span>
                            <?php } ?>

                        <?php } ?>
                    </td>
                    <td style="width: 60%">
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
                                <?php if ($d->isSecureByRecursive()) { ?>
                                    <a href="?removeProtect=<?php echo $d->name ?>" class="btn btn-warning">Retirer la protection par récursivité</a>
                                <?php } else { ?>
                                    <a href="?protect=<?php echo $d->name ?>" class="btn btn-success">Protéger</a>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="<?php echo $d->name; ?>" class="btn btn-primary">Tester l'accès</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Créer un nouveau répertoire</h3>
        <form class="form-inline" action="<?php echo basename(__FILE__); ?>" method="post">
            <input type="text" class="form-control" placeholder="Repertoire" name="path">
            <input type="submit" name="mkdir" class="form-control" value="Créer">
        </form>

        <h3 style="margin-top: 30px">Légende</h3>
        <div>
            <span class="label label-warning" style="margin-right: 5px">Protégé par récursivité</span> Indique que le répertoire est protégé avec le même mot de passe que le repertoire racine (./)
        </div>
    </div>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
