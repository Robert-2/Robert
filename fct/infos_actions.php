<?php
session_start();
require_once('initInclude.php');
require_once('common.inc.php'); // OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once('checkConnect.php');

if ($_SESSION["user"]->isAdmin() !== true) {
    die("Vous n'avez pas accès à cette partie du Robert.");
}

extract($_POST);
$infosBoiteFile = $install_path . FOLDER_CONFIG . 'infos_boite.php';

if ($action == 'modifConsts') {
    unset($_POST['action']);
    
    $newConstFile = "<?php \n\n";
    foreach ($_POST as $key => $val) {
        $newConstFile .= "define('$key', '$val');\n";
    }
    $newConstFile .= "\n?>";

    if (file_put_contents($infosBoiteFile, $newConstFile) !== false) {
        echo 'Informations sauvegardées.';
    } else {
        echo 'Impossible de sauvegarder les infos...';
    }
}

if ($action == 'upload_logo') {
    $newLogo         = $_FILES['newLogo'];
    $types           = ['jpeg', 'png'];
    $maxsize         = 5000000;
    $nouvelleHauteur = 150;
    $destination     = '../config/logo';

    if (!isset($newLogo) || $newLogo['error'] > 0) {
        displayResponseAndExit("Aucune image à traiter, ou image corrompue.");
    }
    if ($newLogo['size'] > $maxsize) {
        displayResponseAndExit("Le fichier est trop lourd !");
    }
    $file = $newLogo['tmp_name'];
    $what = getimagesize($file);
    
    switch (strtolower($what['mime'])) {
        case 'image/png':
            $img = imagecreatefrompng($file);
            break;
        case 'image/jpeg':
            $img = imagecreatefromjpeg($file);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($file);
            break;
        default:
            displayResponseAndExit("L'image doit être un fichier JPEG, ou GIF, ou PNG !");
    }
    
    $nouvelleLargeur =  $what[0] * ($nouvelleHauteur/$what[1]);

    $new = imagecreatetruecolor($nouvelleLargeur, $nouvelleHauteur);
    imagecopyresampled($new, $img, 0, 0, 0, 0, $nouvelleLargeur, $nouvelleHauteur, $what[0], $what[1]);

    if (imagejpeg($new, $destination.'.jpg', 100)) {
        imagedestroy($new);
        displayResponseAndExit("OK", "OK");
    } else {
        displayResponseAndExit("Impossible de sauvegarder l'image. Vérifiez les droits d'accès en écriture.");
    }
}

function displayResponseAndExit($message, $error = 'ERROR')
{
    echo json_encode(['error' => $error, 'message' => $message]);
    exit;
}
