<?php
$location = @$_POST['ville'];
$date     = @$_POST['date'];

if (!isset($location) && !isset($date)) {
    die();
}

$retour = [];

$date_wanted = date_create($date);
$time_wanted = $date_wanted->format('U');

$date_curr = date_create();
$date_curr->setTime(0, 0, 0);
$time_current = $date_curr->format('U');

$location      = urlencode($location);
$xmlWeatherAdr = 'http://api.meteorologic.net/forecarss?p=' . $location ;

// CURL
$curl = curl_init(); // Initialiser cURL.
curl_setopt($curl, CURLOPT_URL, $xmlWeatherAdr); // Indiquer quel URL récupérer
curl_setopt($curl, CURLOPT_HEADER, 0); // Ne pas inclure l'header dans la réponse.
ob_start(); // Commencer à 'cache' l'output.
curl_exec($curl); // Exécuter la requète.
curl_close($curl); // Fermer cURL.
$xmlStr = ob_get_contents(); // Sauvegarder le 'cache' dans la variable $xmlStr.
ob_end_clean(); // Vider le buffer.

if (preg_match('/^\<\?xml/', $xmlStr)) {
    $xml    = simplexml_load_string(utf8_encode($xmlStr));
    $item   = $xml->channel->item;
    $previz = [
        'err' => [
            'icon'      => 'inconnu',
            'condition' => "Impossible de trouver la météo pour cette ville... " .
                           "C'est dans trop longtemps, ou bien c'est déjà passé !",
            'temp'      => '?'
        ]
    ];

    if (preg_match('/\<meteo:weather /', $xmlStr)) {
        $tags = $item->xpath('//meteo:weather');
        if (is_array($tags) && !empty($tags)) {
            foreach ($tags as $tag) {
                $desc = utf8_decode((string)$tag['namepictos_apmidi']);
                $previz[] = [
                    'icon'      => (string)$tag['pictos_apmidi'],
                    'condition' => $desc,
                    'temp'      => (string)$tag['tempe_apmidi'].'°'
                ];
            }
        }
    }

    $timeOffset = $time_wanted - $time_current;
    $dateOffset = number_format($timeOffset / (3600 * 24), 0);
    if ($dateOffset > 2 || $dateOffset < 0) {
        $retour = $previz['err'];
    } else {
        $retour = $previz[$dateOffset];
    }
} else {
    $retour = [
        "icon"      => "inconnu",
        "condition" => "Impossible de trouver la météo... Meteorologic ne répond pas, ou mal.",
        "temp"      => "inconnu"
    ];
}
echo json_encode($retour);
