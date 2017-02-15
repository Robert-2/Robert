<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );


function ICS_exporter ( $fichierICS='../calendars/acousmieCal.ics', $calName='Robert' ) {

	$table_plans = TABLE_PLANS;
	$titre		 = Plan::PLAN_cTITRE;
	$lieu		 = Plan::PLAN_cLIEU;
	$date_start	 = Plan::PLAN_cDATESTART;
	$date_end	 = Plan::PLAN_cDATEEND;
	$beneficiaire= Plan::PLAN_cBENEFICIAIRE;
	
// Contenu du fichier final (attention, ne pas indenter sinon le fichier final ne marchera pas

$startFile = "BEGIN:VCALENDAR
PRODID:-//MPM.org//$calName Calendar//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:$calName Calendar
X-WR-TIMEZONE:Europe/Paris";

$eventFile = "
BEGIN:VEVENT
DTSTART;VALUE=DATE:%start%
DTEND;VALUE=DATE:%end%
DTSTAMP:%stamp%
DESCRIPTION:%descr%
LOCATION:%lieu%
STATUS:CONFIRMED
SUMMARY:%titre%
TRANSP:TRANSPARENT
CLASS:PUBLIC
END:VEVENT";

$endFile = "
END:VCALENDAR";

// fin du fichier final															@TODO : vérifier si le fait d'ajouter un UID améliore la non-mise en cache du ICS par Google !

	global $bdd; $result = array();

// formatage SQL > php et suppr des accents (google pas content)
	$search  = array ('/"/', '/,/', '/\n/', '/\r/', '/:/', '/;/', '/\\//', '/é/', '/è/', '/ê/', '/à/', '/â/', '/î/', '/ô/', '/ç/');
	$replace = array ('\"', '\\,', '\\n', '', '\:', '\\;', '\\\\', 'e', 'e', 'e', 'a', 'a', 'i', 'o', 'c');
	
	$sql = "SELECT `$titre`, `$lieu`, `$date_start`, `$date_end`, `$beneficiaire` FROM `$table_plans` ORDER BY `$date_start` DESC";
	$q = $bdd->prepare($sql);
	$q->execute();
	
	if ($q->rowCount() >= 1) {
		for ($i = 1; $i <= $q->rowCount(); $i++) {
			$result[] = $q->fetch(PDO::FETCH_ASSOC) ;
			$result[$i-1]["titre"]	= preg_replace($search, $replace, $result[$i-1]["titre"]);
			$result[$i-1]["lieu"]	= preg_replace($search, $replace, $result[$i-1]["lieu"]);
			$result[$i-1]["start"]	= date('Ymd', $result[$i-1]["date_start"]);
			$result[$i-1]["end"]	= date('Ymd', $result[$i-1]["date_end"]);
			$result[$i-1]["descr"]	= preg_replace($search, $replace, $result[$i-1]["beneficiaire"]);
			$result[$i-1]["stamp"]	= gmdate('Ymd\THis\Z');
		}
	}
	
	$fsearch  = array('/%start%/', '/%end%/', '/%stamp%/', '/%descr%/', '/%lieu%/', '/%titre%/');
	$output = $startFile;
	foreach ($result as $e) {
		$freplace = array($e['start'], $e['end'], $e['stamp'], $e['descr'], $e['lieu'], $e['titre']);
		$event = preg_replace($fsearch, $freplace, $eventFile);
		$output .= $event;
	}
	$output .= $endFile;
	
	if (file_put_contents($fichierICS, $output)) {
		return true;
	}
	else return false;
}

?> 

