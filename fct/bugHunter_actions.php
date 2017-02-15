<?php
@session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');  // OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$action = '';
extract($_POST);

if ($action == '') {
	$retour['error'] = "Il manque l'action à effectuer !";
	echo json_encode($retour);
	die();
}

if ($type == 'bug') {
	$fileXML = 'bugs.xml';
	$bigBloc = 'bugList';
	$bloc    = 'bug';
}
elseif ($type == 'wish') {
	$fileXML = 'wishes.xml';
	$bigBloc = 'wishList';
	$bloc    = 'wish';
}
elseif ($type == 'panic') {
	$mailDevs = DEVS_MAILS;
}
else {
	$retour['error'] = 'Il manque la var de type !';
	echo json_encode($retour);
	die();
}



// AJOUT D'UN NOEUD XML
if ( @$action == 'addToXML') {								// need vars : $id & $user & $descr & $repro | $prio
	$xml = simplexml_load_file('../bugHunter/'.$fileXML);
	$xmlBloc = $xml->$bigBloc;

	$newNode = $xmlBloc->addChild($bloc);
	$newNode->addAttribute('id', $id);
	$newNode->addAttribute('by', $user);
	$newNode->addAttribute('fixer', '');
	$newNode->addChild('descr', $descr);
	if ($bloc == 'bug')
		$newNode->addChild('repro', $repro);
	if ($bloc == 'wish')
		$newNode->addAttribute('prio', $prio);

	if ($xml->asXML('../bugHunter/'.$fileXML)) {
		$retour = array('id'=>$id, 'by'=>$user, 'descr'=>$descr, 'repro'=>@$repro, 'prio'=>@$prio, 'fixer'=>'');
		$retourMail = sendMailToDevs ("$user a ajouté un $type dans le BUG HUNTER du ROBERT !", $descr, $idUser);
		if ($retourMail['error'] == "Le message a bien été envoyé !")
			 $retour['mailSent'] = 'Un email a été envoyé aux devs pour les prévenir !';
		else $retour['mailSent'] = "Impossible de prévenir les devs par email.";
	}
	else {
		$retour['error'] = "Impossible de sauvegarder le XML...";
	}

	echo json_encode($retour);
}



// MODIFICATION D'UN NOEUD XML
if ( @$action == 'modXML' ) {								// need vars : $id & $fixer
	$dom = new DOMDocument ;
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->load('../bugHunter/'.$fileXML) ;

	$xpath = new DOMXPath($dom);
	$thebloc = $xpath->query('//*[@id="'.$id.'"]')->item(0);
	$thebloc->removeAttribute('fixer') ;
	$thebloc->setAttribute('fixer', $fixer);

	if ($dom->save('../bugHunter/'.$fileXML)) {
		$retour = array('id'=>$id, 'fixer'=>$fixer);
	}
	else $retour['error'] = "Impossible de sauvegarder le XML...";

	echo json_encode($retour);
}



// SUPPRESSION D'UN NOEUD XML
if ( @$action == 'supprXML') {								// need var : $id
	$dom = new DOMDocument ;
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->load('../bugHunter/'.$fileXML) ;

	$domDoc = $dom->documentElement;
	$xpath = new DOMXPath($dom);
	$thebloc = $xpath->query('//*[@id="'.$id.'"]')->item(0);

	$domBlocs = $domDoc->getElementsByTagName($bigBloc)->item(0) ;
	$domBlocs->removeChild($thebloc) ;

	if ($dom->save('../bugHunter/'.$fileXML)) {
		$retour = array('id'=>$id);
	}
	else $retour['error'] = "Impossible de sauvegarder le XML...";

	echo json_encode($retour);
}



// ENVOI DU MAIL DE PANIC
if ( @$action == 'sendPanic') {
	$messagePanic = urldecode($message);
	$retourMail = sendMailToDevs (" est en PANIQUE SUR LE ROBERT !'", $messagePanic, $idUser);
	$retourMail['type'] = "reloadPage";
	echo json_encode($retourMail);
}


function sendMailToDevs ($sujet, $messageInt, $from) {
	try {
		$user = new Users();
		$user->loadFromBD(Users::USERS_ID, $from);
		$infosUser = $user->getUserInfos();
		$mailUser  = $infosUser['email'];
		$nomUser   = $infosUser['prenom']." ".$infosUser['nom'];
		unset($user);
		$datePanic = "( le ".date('d/m/Y')." )";
	}
	catch (Exception $e) {
		$retour['error'] = "Envoi du mail : erreur de récup d'info !\n\nMessage :\n".$e->getMessage();
	}

	$headerMail = "MIME-Version: 1.0\r\nFrom: $mailUser\r\nContent-type: text/html; charset=utf-8";
	$messageMail = '<html>
						<body bgcolor="#F1EDE9">
							Hellow mes ptits devs !! <br />
							<p><b>'.$infosUser['nom'].' '.$sujet.'</b> ! '.$datePanic.'</p>
							--------------------------------------------------------------------------------------
							<p><b>'.nl2br($messageInt).'</b></p>
							--------------------------------------------------------------------------------------
							<p>Signé : <a href="http://www.robert.acousmie.fr">LE ROBERT</a></p>
						</body>
					</html>';

	// Envoi du mail
	if (mail(DEVS_MAILS, $sujet, $messageMail, $headerMail))
		 $retour['error'] = "Le message a bien été envoyé !" ;
	else $retour['error'] = "Impossible d'envoyer le message aux developpeurs... Désolé !";
	return $retour;
}


?>