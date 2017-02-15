<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');			// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );
require_once ('date_fr.php');

// Si on a pas de plan à modifier, message d'erreur
if (!isset($_GET['plan'])) {
	die ('<br /><br />
		<div class="ui-state-error center pad20 enorme">
			Il me faut un numéro de plan dans l\'url <i>("&plan=xx")</i> pour pouvoir le modifer !!<br /><br />
			<a href="index.php?go=calendrier"><button class="bouton"><span class="inline ui-icon ui-icon-arrowthickstop-1-w"></span>Retour calendrier</button></a>
		</div>');
}


$lp = new Liste();
$listeBenef = $lp->getListe ( TABLE_STRUCT, 'id, label' );
$listeTekos = $lp->getListe ( TABLE_TEKOS, 'id, surnom, categorie', 'surnom', 'ASC' );
$listeMatos = $lp->getListe ( TABLE_MATOS, '*', 'categorie', "ASC" ) ;
$listePacks = $lp->getListe ( TABLE_PACKS, '*', 'categorie', "ASC" ) ;
$lp = null ;


// initialise des listes de categorie pour les tekos et le matos
$catTekos = array(); $catMatos = array();
foreach ( $listeTekos as $k => $v ) { if ( ! in_array($v['categorie'], $catTekos) ) $catTekos[] = $v['categorie'] ; }
foreach ( $listeMatos as $k => $v ) { if ( ! in_array($v['categorie'], $catMatos) ) $catMatos[] = $v['categorie'] ; }


// Crée un tableau pour récupérer les tekos par leur ID
$tekosNameById = array();
foreach ($listeTekos as $t) {
	$tekosNameById[$t['id']] = $t['surnom'];
}

// Crée un tableau pour récupérer le matos par leur ID
$matosById = array();
foreach ($listeMatos as $m) {
	$matosById[$m['id']]['ref']  = $m['ref'];
	$matosById[$m['id']]['cat']  = $m['categorie'];
	$matosById[$m['id']]['Qtot'] = $m['Qtotale'];
	$matosById[$m['id']]['PU']   = $m['tarifLoc'];
	$matosById[$m['id']]['ext']  = $m['externe'];
}


// Pour l'autocomplete des bénéficiaires
$varsBenefs = '';
foreach ($listeBenef as $v ){
	$varsBenefs .= "'".addslashes($v['label'])."',";
}
$varsBenefs = substr($varsBenefs,0,strlen($varsBenefs)-1 ) ;


// On charge les infos du plan à modifier
try {
	if (isset($_SESSION['plan_mod'])) {
		$testPlan = unserialize($_SESSION['plan_mod']);
		if ($testPlan->getPlanID() == $_GET['plan'])
			$thePlan = unserialize($_SESSION['plan_mod']);
		else {
			$thePlan = new Plan();
			$thePlan->load( Plan::PLAN_cID , $_GET['plan'] );
		}
	}
	else {
		$thePlan = new Plan();
		$thePlan->load( Plan::PLAN_cID , $_GET['plan'] );
	}
	$startPlanMod = $thePlan->getPlanStartDate();
	$sD = date('Ymd', $startPlanMod);
	$endPlanMod   = $thePlan->getPlanEndDate();
	$eD = date('Ymd', $endPlanMod);
	$titrePlanMod = $thePlan->getPlanTitre();
	$benefPlanMod = $thePlan->getPlanBenef();
	$lieuPlanMod  = $thePlan->getPlanLieu();
	$userPlanMod  = $thePlan->getPlanCreateur();
	$tekosPlanMod = $thePlan->getPlanTekos();
	$matosPlanMod = $thePlan->getPlanMatos();
	$confirmedPlan = $thePlan->getPlanConfirm();
}
catch (Exception $e) {
	die ('<br /><br />
		<div class="ui-state-error pad20 enorme">
			<p class="center big">Impossible de charger le plan !!</p>
			<p>Message de Plan::load() : <b>'.$e->getMessage().'</b></p>
			<a href="index.php?go=calendrier"><button class="bouton"><span class="inline ui-icon ui-icon-arrowthickstop-1-w"></span>Retour calendrier</button></a>
		</div>');
}

// récupère les sous plans du plan
$i = 0;
while ( $sp = $thePlan->whileTestSousPlan() ) {
	$tmpDate = $thePlan->getSousPlanDate(); 
	$sousPlans[$i]['jour'] = datefr( date("l j F Y", $thePlan->getSousPlanDate()) ) ;
	$sousPlans[$i]['time'] = $thePlan->getSousPlanDate() ;
	$sousPlans[$i]['rem']  = $thePlan->getSousPlanComment() ;
	$tekosIds = $thePlan->getSousPlanTekos() ;
	$stringTekosList = '';
	foreach ($tekosIds as $idT) {
		$stringTekosList .= '<div class="ui-state-default inline ui-corner-all pad3 tekosItem" id="'.$idT.'">'.$tekosNameById[$idT] . ' </div> ';
	}

	$sousPlans[$i]['teks'] = $stringTekosList;
	$i++;
}
$tmp = Array();								// Tri du tableau des sous plans par leur timestamp
foreach($sousPlans as &$spTs)
	$tmp[] = &$spTs["time"];
array_multisort($tmp, $sousPlans);

$matosListByCateg = array();
$SsTotalMatos = array();
$SsTotalCateg = array();
$totalFinal   = 0;
foreach ($matosPlanMod as $matId => $matQte) {
	if ($matId == 'undefined' && $matId == 0) continue;
	if ($matQte == 0) continue;
	$categ = $matosById[$matId]['cat'];
	$SsTotalMatos[$matId] = $matosById[$matId]['PU'] * $matQte;
	if (!isset($SsTotalCateg[$categ])) $SsTotalCateg[$categ] = 0;
	$SsTotalCateg[$categ] += $SsTotalMatos[$matId];
	$totalFinal += $SsTotalMatos[$matId];

	$matosListByCateg[$categ][] = $matQte . ' x ' . $matosById[$matId]['ref'] . ' <span class="mini">('.$SsTotalMatos[$matId].' €)</span><br />';
}

$js = substr($sD, 6); $ms = substr($sD, 4, 2); $as = substr($sD, 0, 4);
$je = substr($eD, 6); $me = substr($eD, 4, 2); $ae = substr($eD, 0, 4);

require_once ('infos_boite.php');

?>

<script> var id_MODPLAN = <?php echo $_GET['plan'] ?> ; </script>

<script src="./fct/plan_matos_init_modal.js"></script>
<script src="./fct/modif_plan_Ajax.js"></script>

<script>
	var old_date_start = "<?php echo $js.'/'.$ms.'/'.$as; ?>";
	var old_date_end   = "<?php echo $je.'/'.$me.'/'.$ae; ?>";
	var autoCompleteBENEF = [<?php echo $varsBenefs; ?>];
	var planConfirmed  = '<?php echo $confirmedPlan; ?>';
<?php
if (!isset($_SESSION['plan_mod_backup'])) {
	echo 'if (planConfirmed == "1") {
			if (!confirm("ATTENTION !\n\nCet évènement a déjà été confirmé !\n(le devis a été accepté, la réservation effectuée)...\n\nVoulez-vous VRAIMENT le modifier ?"))
				window.location = "index.php?go=calendrier";
		}';
}
?>
	$(function() {
<?php foreach ($tekosPlanMod as $t) echo "		tekosIds.push('$t'); $('.tek_name[id*=\"$t\"]').click();\n" ?>
<?php foreach ($matosPlanMod as $m => $q) echo "		matosIdQte['$m'] = $q; qteMatos_update($m);\n" ?> //  /* $('.qtePik[id*=\"$m\"]').children('.qtePikInput').show().val('$q').focus(); */
		$("#themeSel").parent('p').hide();
		$("#modifInfoUserActif").hide();
		$("#modPlanBenef").autocomplete( { source: autoCompleteBENEF });
		$('#etape-2').show();
		$('#etape-3').show();
		$('#raccourcisPlans').hide();
		$('#tekosHolder').attr('id', 'tekosmodalHolder');
		$('#togglePacksMatos').click();
	});
</script>


<?php
// Met le plan à modifier en var de session
$_SESSION['plan_mod'] = serialize($thePlan);
// enregistrement en session d'une sauvegarde du plan avant la modif (au cas ou clic sur Annuler)
if (!isset($_SESSION['plan_mod_backup'])) $_SESSION['plan_mod_backup'] = array($titrePlanMod, $benefPlanMod, $lieuPlanMod, $sD, $eD, $matosPlanMod);
?>


<div class="miniSousMenu gros" style="padding: 5px 10px 10px 10px;">
	<div class="inline quart leftText">
		<button class="bouton" onclick="retourCalendar()"><span class="inline ui-icon ui-icon-arrowthickstop-1-w"></span>Retour calendrier</button>
	</div>
	<div class="inline demi center">
		<div class="inline ui-state-highlight ui-corner-all pad5 gros"id="rappelPlanInfos">
			Modification de <i><b><?php echo $titrePlanMod; ?></b></i>	à <b><?php echo $lieuPlanMod; ?></b>, pour <b><?php echo $benefPlanMod; ?></b>
		</div>
	</div>
	<div class="inline ui-state-focus ui-corner-all pad5">créateur : <?php echo $userPlanMod; ?></div>
	<div id="retourAjax" class="ui-state-error ui-corner-all pad10"></div>
	
</div>

<div class="gros">
	<div class="ui-state-default ui-corner-all pad10 leftText shadowOut">
		<div class="ui-widget-header ui-corner-all pad3">Informations</div>
		<br />
		<div class="inline mid marge30r">
			<div class="inline" style="width:100px;">Titre :</div>		  <div class="inline"><input type="text" id="modPlanTitre" value="<?php echo $titrePlanMod; ?>" /></div><br />
			<div class="inline" style="width:100px;">Bénéficiaire :</div> <div class="inline"><input type="text" id="modPlanBenef" value="<?php echo $benefPlanMod; ?>" /></div><br />
			<div class="inline" style="width:100px;">Lieu :</div>		  <div class="inline"><input type="text" id="modPlanLieu"  value="<?php echo $lieuPlanMod; ?>" /></div>
		</div>
		<div class="inline mid center enorme marge30l">
			DU : <input type="text" class="inputCal" id="modPlanStart" value="<?php echo $js.'/'.$ms.'/'.$as; ?>" size="12" />
			AU : <input type="text" class="inputCal" id="modPlanEnd" value="<?php echo $je.'/'.$me.'/'.$ae; ?>" size="12" />
		</div>
		<div class="inline mid center marge30l">
			<button class="bouton ui-state-highlight" id="refreshModPlanDates">ACTUALISER</button>
		</div>
		<div id="displayNbPlanSimult" class="center red"></div>
	</div>


	<div class="ui-state-default ui-corner-all pad10 leftText shadowOut margeTop10">
		<div class="ui-widget-header ui-corner-all pad3">Techniciens et remarques pour chaque jour</div>
		<br />
		<?php
		foreach ($sousPlans as $spInfo) {
			$tmpDate = $spInfo['time'];
			$tmpTime = $spInfo['jour'];

			echo '<div class="ui-state-highlight ui-corner-all shadowOut inline marge10r pad5 center spInfos" id="'. $spInfo['time'] .'" style="width:30%; line-height:25px;">
					<div class="ui-widget-header ui-corner-all padV10 marge5">'.$spInfo['jour'].'</div>
					Tekos :
					<div id="'.$tmpDate.'" jour="'.$tmpTime.'" style="float:right;" class="bouton addTekosSouplan" title="Modifier les techniciens pour cette date">
						<span class="ui-icon ui-icon-plus">+</span>
					</div>
					<div class="tekosSPlist">'.$spInfo['teks'].'</div><br />

					<textarea class="modifSPrem" id="'.$spInfo['time'].'" rows="5" cols="25" title="remarque pour le '.$spInfo['jour'].'">'.$spInfo['rem'].'</textarea>
				</div>';
		}
		?>
	</div>


	<div class="ui-state-default ui-corner-all pad10 leftText shadowOut margeTop10">
		<div class="ui-widget-header ui-corner-all pad3">Matériel</div>
		<br />
		<div class="center"><button class="bouton ui-state-highlight" id="modPlan_modMatosListe">MODIFIER LA LISTE</button></div>
		<br />
		<?php
		foreach ($matosListByCateg as $categ => $listM) {
			echo '<div class="inline top ui-corner-all shadowIn marge10l pad10 leftText" style="width:21%;">
					<div class="ui-widget-header ui-corner-all center pad3"> <img src="gfx/icones/categ-'.$categ.'.png" style="float:right; margin-right:10px;" />'.strtoupper($categ).'</div>
					<br />';
			foreach ($listM as $matLine) echo $matLine;
			echo '</div>';
		}
		?>
	</div>


	<div class="ui-state-default ui-corner-all pad10 leftText shadowOut margeTop10">
		<div class="ui-widget-header ui-corner-all pad3">Totaux</div>
		<br />
		<div class="inline top tiers padV10">
			<?php
			foreach ($SsTotalCateg as $cat => $ssTc) {
				echo 'total '.strtoupper($cat).' = <b>'.$ssTc.' €</b><br />';
			}
			?>
		</div>
		<div class="inline top tiers rightText">
			<?php echo '<div class="enorme">TOTAL H.T. : '.number_format($totalFinal, 2).' €</div>
					 <div class="enorme">TOTAL T.T.C. : '.number_format($totalFinal + ($totalFinal * TVA_VAL), 2).' €</div>';
			?>
		</div>
	</div>
	
	<div class="ui-state-error ui-corner-all center top gros pad20 hide" id="retourAjaxPlan"></div>
	
	
	<div class="ui-state-default ui-corner-all pad10 shadowOut center gros margeTop10">
		<button class="bouton ui-state-highlight" id="saveModPlan">SAUVEGARDER</button>
		<button class="bouton" id="annuleModPlan">ANNULER</button>
	</div>
	<br /><br />
</div>


<div id="modalTekos" class="hide">
	<?php include 'plan_tekos_list.php' ; ?>
</div>


<div id="modalMatos" class="petit hide">
	<?php include 'plan_matos_list.php' ; ?>
</div>


<div id="addBenefDialog" title="Renseigner le bénéficiaire" class="petit hide"></div>