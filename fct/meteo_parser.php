<?php
 
$location	= @$_POST['ville'];
$date		= @$_POST['date'];
//$location	= @$_GET['ville'];
//$date		= @$_GET['date'];

if (!isset($location) && !isset($date)) die();

$date_wanted= date_create($date);
$time_wanted = $date_wanted->format('U');

$date_curr = date_create();
$date_curr->setTime(0,0,0);
$time_current = $date_curr->format('U');

$location = urlencode($location);
$xmlWeatherAdr = 'http://api.meteorologic.net/forecarss?p='.$location ;

// CURL
$curl = curl_init();								// Initialiser cURL.
curl_setopt($curl, CURLOPT_URL, $xmlWeatherAdr);	// Indiquer quel URL récupérer
curl_setopt($curl, CURLOPT_HEADER, 0);				// Ne pas inclure l'header dans la réponse.
ob_start();											// Commencer à 'cache' l'output.
curl_exec($curl);									// Exécuter la requète.
curl_close($curl);									// Fermer cURL.
$xmlStr = ob_get_contents();						// Sauvegarder le 'cache' dans la variable $Results.
ob_end_clean();										// Vider le buffer.

$gotXmlTag = preg_match('/^\<\?xml/', $xmlStr);
//echo $xmlStr;

if ($gotXmlTag > 0) {
	$xml = simplexml_load_string(utf8_encode($xmlStr));
	$item = $xml->channel->item;
	$previz = array();
	foreach($item->xpath('//meteo:weather') as $tag){
		$desc = utf8_decode((string)$tag['namepictos_apmidi']);
		$previz[] = array( 'icon' => (string)$tag['pictos_apmidi'], 'condition' => $desc, 'temp' => (string)$tag['tempe_apmidi'].'°');
	}
	$previz['err'] = array('icon' => 'inconnu', 'condition'=>"Impossible de trouver la météo... C'est dans trop longtemps, ou bien c'est déjà passé !", 'temp' => '?');
	
	$timeOffset = $time_wanted - $time_current;
	$dateOffset	= number_format($timeOffset / (3600 * 24), 0);
	if ($dateOffset > 2 || $dateOffset < 0)
		$retour = $previz['err'];
	else
		$retour = $previz[$dateOffset];

	$json = json_encode($retour);
	echo $json;
}
else echo '{"day_of_week":"inconnu","icon":"gfx\/icones\/unknown_weather.png","condition":"Impossible de trouver la m\u00e9t\u00e9o... Meteorologic ne répond pas, ou mal."}';

?>