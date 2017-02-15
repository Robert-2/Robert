<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'avez pas accès à cette partie du Robert."); }

extract($_POST) ;
$infosBoiteFile = $install_path . FOLDER_CONFIG . 'infos_boite.php';

if ($action == 'modifConsts') {
	unset($_POST['action']);
	$newConstFile = "<?php \n\n";
	foreach ($_POST as $key => $val) {
		$newConstFile .= "define('$key', '$val');\n";
	}
	$newConstFile .= "\n?>";
	
	if ( file_put_contents($infosBoiteFile, $newConstFile) !== false )
		echo 'Informations sauvegardées.';
	else echo 'Impossible de sauvegarder les infos...';
}


if($action == 'upload_logo'){
    $data['file'] = $_FILES;
    $data['text'] = $_POST;
    $extensions = array('jpg');
    $maxsize = 10000000;
    $NouvelleHauteur = 150;

     if (!isset($_FILES['image']) OR $_FILES['image']['error'] > 0) echo "Image corrompue !";
     if ($maxsize !== FALSE AND $_FILES['image']['size'] > $maxsize) echo "Taille du fichier trop important !";
     $ext = substr(strrchr($_FILES['image']['name'],'.'),1);
     if ($extensions !== FALSE AND !in_array($ext,$extensions)) echo "L'image n'est pas un fichier .jpg !";

     $ImageChoisie = imagecreatefromjpeg($_FILES['image']['tmp_name']);
     $TailleImageChoisie = getimagesize($_FILES['image']['tmp_name']);
     $NouvelleLargeur =  $TailleImageChoisie[0] * ($NouvelleHauteur/$TailleImageChoisie[1]);
     if($NouvelleImage = imagecreatetruecolor($NouvelleLargeur , $NouvelleHauteur)) echo "Problème lors du redimentionnement";

     imagecopyresampled($NouvelleImage , $ImageChoisie  , 0,0, 0,0, $NouvelleLargeur, $NouvelleHauteur, $TailleImageChoisie[0],$TailleImageChoisie[1]);
     imagedestroy($ImageChoisie);
	 imagejpeg($NouvelleImage , "../gfx/logo.jpg", 100);
 
    echo "<script>windows.reload();</script>";
}	







?>