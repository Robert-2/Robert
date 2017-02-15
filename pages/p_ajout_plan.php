<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );
require_once ('date_fr.php');

$lp = new Liste();
$listeBenef = $lp->getListe ( TABLE_STRUCT, 'id, label' );
$listeTekos = $lp->getListe ( TABLE_TEKOS, 'id, surnom, categorie', 'surnom', 'ASC' );
$listeMatos = $lp->getListe ( TABLE_MATOS, '*', 'ref', "ASC" ) ;
$listePacks = $lp->getListe ( TABLE_PACKS, '*', 'ref', "ASC" ) ;
$lp = null ;

// initialise une liste des categories de tekos et de matos
$catTekos = array(); $catMatos = array();
foreach ( $listeTekos as $k => $v ) { if ( ! in_array($v['categorie'], $catTekos) ) $catTekos[] = $v['categorie'] ; }
foreach ( $listeMatos as $k => $v ) { if ( ! in_array($v['categorie'], $catMatos) ) $catMatos[] = $v['categorie'] ; }


// Pour l'autocomplete des bénéficiaires
$varsBenefs = '';
foreach ($listeBenef as $v ){
	$varsBenefs .= "'".addslashes($v['label'])."',";
}
$varsBenefs = substr($varsBenefs,0,strlen($varsBenefs)-1 ) ;


if (isset($_GET['start']) && isset($_GET['end'])) {		// Si des GET de dates sont définies, on écrase le plan en session
	$recupStart = $_GET['start'];
	$recupEnd   = $_GET['end'];
	$recupTitre = 'nom du plan';
	$recupBenef = 'TEST';
	$recupLieu  = 'lieu du plan';
	$refreshEtape1 = '';
	unset($_SESSION['plan_add']);
}

if (isset($_SESSION['plan_add'])) {						// Sinon, on rappelle les valeurs du plan en session (si défini)
	$recupPlan = unserialize($_SESSION['plan_add']);
	$recupTitre = $recupPlan->getPlanTitre();
	$recupBenef = $recupPlan->getPlanBenef();
	$recupLieu  = $recupPlan->getPlanLieu();
	$recupStart = date('Ymd', $recupPlan->getPlanStartDate());
	$recupEnd   = date('Ymd', $recupPlan->getPlanEndDate());
	$recupTekos = $recupPlan->getPlanTekos();
	$recupMatos = $recupPlan->getPlanMatos();
	$refreshEtape1 = "		refreshEtapesBtns(1);\n";
}
else {													// Sinon, on met des valeurs par défaut
	$recupTitre = 'nom du plan';
	$recupBenef = 'TEST';
	$recupLieu  = 'lieu du plan';
	$refreshEtape1 = '';
}

?>


<script src="./js/JSON.js"></script>
<script src="./fct/plan_matos_init_modal.js"></script>
<script src="./fct/ajout_plan_Ajax.js"></script>

<script type="text/javascript">
	var autoCompleteBENEF = [<?php echo $varsBenefs; ?>];
	
	$(function() {
		
		$("#themeSel").parent('p').hide();
		$("#modifInfoUserActif").hide();
		
		$("#<?php echo Plan::PLAN_cBENEFICIAIRE ; ?>").autocomplete( { source: autoCompleteBENEF });
		
	/// Initialisation de l'aperçu des détails dans le menu de droite
		$('#rightMenuSection').html(
			'<div class="etapes ui-state-default ui-state-highlight ui-corner-all pad5 center doigt" id="indic-etape-1">'
				+'<div class="gros">ÉTAPE 1</div>'
				+'infos générales'
			+'</div>'
			+'<div class="etapes ui-state-disabled ui-corner-all pad5 center margeTop5 doigt" id="indic-etape-2">'
				+'<div class="gros">ÉTAPE 2</div>'
				+'choix techniciens'
			+'</div>'
			+'<div class="etapes ui-state-disabled ui-corner-all pad5 center margeTop5 doigt" id="indic-etape-3">'
				+'<div class="gros">ÉTAPE 3</div>'
				+'choix matériel'
			+'</div>'
			+'<div class="etapes ui-state-disabled ui-corner-all pad5 center margeTop5 doigt" id="indic-etape-4">'
				+'<div class="gros">ÉTAPE 4</div>'
				+'récapitulatif'
			+'</div>'
			+'<div class="totaux ui-state-error ui-corner-all pad5 margeTop5 hide">'
				+'<div class="gros" id="bigTotal"></div>'
				+'<div class="margeTop5 rightText padV10" id="sousTotal"></div>'
			+'</div>'
			+'<div class="totaux ui-state-highlight ui-corner-all pad5 margeTop5 hide">'
				+'A LOUER EN +'
				+'<div class="leftText pad3 petit" id="extAlouer">Rien, pour le moment...</div>'
			+'</div>'
		);
		
		matosIdQte['1'] = 0; qteMatos_update(1);
		
<?php
if (isset($recupStart)) echo "		$('#picker_start').datepicker('setDate', '$recupStart');\n";
if (isset($recupEnd))   echo "		$('#picker_end').datepicker('setDate', '$recupEnd');\n";
echo $refreshEtape1;
if (isset($recupTekos) && $recupTekos[0] != '') {
	echo "		var tekosIds = [];\n";
	foreach ($recupTekos as $t)
		echo "		tekosIds.push('$t'); $('.tek_name[id*=\"$t\"]').click();\n";
	echo "		refreshEtapesBtns(2);\n";
}
if (isset($recupMatos)) {
	foreach ($recupMatos as $m => $q)
		echo "		matosIdQte['$m'] = $q; qteMatos_update($m);\n" ;
	echo "		refreshEtapesBtns(3);\n";
}
?>
		recalcDispoPacks();
	});
</script>


<style>
	.addSection	{ box-shadow: inset 0 0 8px #888888; }
</style>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>

<div class="miniSousMenu gros" style="padding: 5px 10px 10px 10px;">
	<div class="inline quart leftText">
		<button class="bouton" onclick="retourCalendar()"><span class="inline ui-icon ui-icon-arrowthickstop-1-w"></span>Retour calendrier</button>
	</div>
	<div class="inline demi center">
		<div class="inline ui-state-highlight ui-corner-all pad5 gros" id="rappelPlanInfos">
			Création de <i><b id="rappelTitrePlan"></b></i>	à <b id="rappelLieuPlan"></b>, pour <b id="rappelBenefPlan"></b>
		</div>
	</div>
	<div class="inline quart rightText">
		<button class="bouton prevEtape" onclick="prevEtape()"><span class="inline ui-icon ui-icon-arrowthick-1-w"></span>Précédent</button>
		<button class="bouton nextEtape" onclick="nextEtape()"><span class="inline ui-icon ui-icon-arrowthick-1-e"></span>Suivant</button>
	</div>
</div>


<div id="toolTipPopup" class="ui-state-highlight ui-corner-all pad10 hide"></div>


<div class="addSection ui-widget-content ui-corner-all center pad20" id="etape-1">
	<div class="ui-widget-header ui-corner-all pad5 gros">Informations, et période de l'évènement à ajouter</div>
	<br />
	<div class="inline top gros" style="width: 250px;">
		<div class="ui-widget-header ui-corner-all petit">Titre : <b class="red">*</b></div>
		<input class="newPlan_data" type="text" id="<?php echo Plan::PLAN_cTITRE ; ?>" size="25" value="<?php echo $recupTitre; ?>"/>
		<br />
		<div class="ui-widget-header ui-corner-all petit margeTop5">Bénéficiaire : <b class="red">*</b></div>
		<input class="newPlan_data" type="text" id="<?php echo Plan::PLAN_cBENEFICIAIRE ; ?>" size="25" value="<?php echo $recupBenef; ?>"/>
		<br />
		<div class="ui-widget-header ui-corner-all petit margeTop5">Lieu : <b class="red">*</b></div>
		<input class="newPlan_data" type="text" id="<?php echo Plan::PLAN_cLIEU ; ?>" size="25" value="<?php echo $recupLieu; ?>"/>
		<br />
	</div>
	<div class="inline top big" style="width: 250px;">
		<div class="ui-widget-header ui-corner-all mini">Debut : <b class="red">*</b></div>
		<div class="newPlan_data miniCal" id="picker_start"></div>
	</div>
	<div class="inline top big" style="width: 250px;">
		<div class="ui-widget-header ui-corner-all mini">Fin : <b class="red">*</b></div>
		<div class="newPlan_data miniCal" id="picker_end"></div>
	</div>
	<br />
	<br />
</div>


<div id="addBenefDialog" title="Renseigner le bénéficiaire" class="petit hide"></div>


<?php include 'plan_tekos_list.php' ; // ETAPE 2 ?>


<?php include 'plan_matos_list.php' ; // ETAPE 3 ?>



<div class="addSection ui-widget-content ui-corner-all center pad20 hide" id="etape-4">
	<div class="ui-widget-header ui-corner-all pad5 gros">Récapitulatif de l'évènement</div>
	<br />
	
	<div id="recapPlan" class="gros pad10 ui-corner-all leftText shadowIn">
		
	</div>
	<br />
	<br />
	<div class="ui-state-error ui-corner-all center top gros pad20 hide" id="retourAjaxPlan"></div>
	
	<div id="boutonsFinaliseAddPlan" class="ui-widget-content ui-corner-all shadowOut pad20 enorme">
		<button id="devis"		 class="bouton plan_save">POUR DEVIS</button>
		<button id="reservation" class="bouton plan_save ui-state-highlight">RÉSERVATION DIRECTE</button>
	</div>
	
	
	<br />
	<br />
	
</div>
