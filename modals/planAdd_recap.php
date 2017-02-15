<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');			// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$infosPlan  = json_decode( $_POST['infos'], true );
$tekosAsked = json_decode( $_POST['tekos'], true );
$matosAsked = json_decode( $_POST['matos'], true );
$js = substr($infosPlan['picker_start'], 6); $ms = substr($infosPlan['picker_start'], 4, 2); $as = substr($infosPlan['picker_start'], 0, 4);
$je = substr($infosPlan['picker_end'], 6);   $me = substr($infosPlan['picker_end'], 4, 2);   $ae = substr($infosPlan['picker_end'], 0, 4);
//$dateStart  = DateTime::createFromFormat('Ymd', $infosPlan['picker_start']);
//$dateEnd    = DateTime::createFromFormat('Ymd', $infosPlan['picker_end']);
unset($infosPlan['picker_start']);
unset($infosPlan['picker_end']);


// Crée des listes utiles à la vérif et à l'affichage
$l = new Liste();
$matosList = $l->getListe(TABLE_MATOS, '*', 'categorie', "ASC");
$listeTekos = $l->getListe(TABLE_TEKOS, '*', 'surnom', 'ASC');
$l = null;

$tekosNameById = array();
foreach ($listeTekos as $t) {
	$tekosNameById[$t['id']] = $t['surnom'];
}

$matosById = array();
foreach ($matosList as $m) {
	$matosById[$m['id']]['ref']  = $m['ref'];
	$matosById[$m['id']]['cat']  = $m['categorie'];
	$matosById[$m['id']]['Qtot'] = $m['Qtotale'];
	$matosById[$m['id']]['PU']   = $m['tarifLoc'];
	$matosById[$m['id']]['ext']  = $m['externe'];
}

unset($_SESSION['plan_add']);
// Crée le plan temporaire pour tout vérifier
$p = new Plan () ;
foreach ($infosPlan as $k => $v) {
	$p->addPlanInfo ($k, $v);
}
$p->addPlanInfo( Plan::PLAN_cTECKOS , implode(' ', $tekosAsked));
$p->addPlanInfo( Plan::PLAN_cMATOS , $_POST['matos']);
$p->setDateStart( $js, $ms, $as);
$p->setDateEnd( $je, $me, $ae);
//$p->setDateStart( date_format($dateStart, 'd'), date_format($dateStart, 'm'), date_format($dateStart, 'Y') );
//$p->setDateEnd  ( date_format($dateEnd, 'd'), date_format($dateEnd, 'm'), date_format($dateEnd, 'Y') );

$i = 0;
while ( $sp = $p->whileTestSousPlan() ) {
	$tmpDate = $p->getSousPlanDate(); 
	$sousPlans[$i]['jour'] = datefr( date("l j F Y", $p->getSousPlanDate()) ) ;
	$sousPlans[$i]['time'] = $p->getSousPlanDate() ;
	$sousPlans[$i]['rem']  = $p->getSousPlanComment() ;
	$tekosIds = $p->getSousPlanTekos() ;
	$stringTekosList = '';
	foreach ($tekosIds as $idT) {
		if (isset($idT) && $idT != '')
			$stringTekosList .= '<div class="ui-state-default inline ui-corner-all pad3 tekosItem" id="'.$idT.'">'.$tekosNameById[$idT] . ' </div> ';
	}

	$sousPlans[$i]['teks'] = $stringTekosList;
	$i++;
}

$_SESSION['plan_add'] = serialize($p);

require_once ('infos_boite.php');

?>

<script>
	$(function(){
//		$("#modalTekos").html($("#etape-2").html());
		$('.bouton').button();
	});
</script>

<div class="ui-state-default ui-corner-all pad10 leftText shadowOut">
	<div class="ui-widget-header ui-corner-all pad3">Informations</div>
	<br />
	<div class="inline mid marge30r">
		<div class="inline" style="width:100px;">Titre :</div><div class="inline"><b><?php echo $infosPlan['titre']; ?></b></div><br />
		<div class="inline" style="width:100px;">Lieu :</div><div class="inline"><b><?php echo $infosPlan['lieu']; ?></b></div><br />
		<div class="inline" style="width:100px;">Bénéficiaire :</div><div class="inline"><b><?php echo $infosPlan['beneficiaire']; ?></b></div>
	</div>
	<div class="inline mid center enorme marge30l">
		DU : <b><?php echo $js.'/'.$ms.'/'.$as; ?></b>, AU : <b><?php echo $je.'/'.$me.'/'.$ae; ?></b>
	</div>
</div>

<div id="retourAjax" class="ui-state-error ui-corner-all pad10"></div>

<div class="ui-state-default ui-corner-all pad10 leftText shadowOut margeTop10">
	<div class="ui-widget-header ui-corner-all pad3">Techniciens et remarques pour chaque jour</div>
	<br />
	<?php
	$nbJours = 0;
	foreach ($sousPlans as $spInfo) {
		$tmpDate = $spInfo['time'];
		$tmpTime = $spInfo['jour'];
		$nbJours++;
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
	<?php
	$matosListByCateg = array();
	$SsTotalMatos = array();
	$SsTotalCateg = array();
	$totalJour   = 0;
	foreach ($matosAsked as $matId => $matQte) {
		if ($matId == 'undefined' && $matId == 0) continue;
		if ($matQte == 0) continue;
		$categ = $matosById[$matId]['cat'];
		$SsTotalMatos[$matId] = $matosById[$matId]['PU'] * $matQte;
		if (!isset($SsTotalCateg[$categ])) $SsTotalCateg[$categ] = 0;
		$SsTotalCateg[$categ] += $SsTotalMatos[$matId];
		$totalJour += $SsTotalMatos[$matId];
		
		$matosListByCateg[$categ][] = $matQte . ' x ' . $matosById[$matId]['ref'] . ' <span class="mini">('.$SsTotalMatos[$matId].' €)</span><br />';
	}
	
	$totalFinal = $totalJour * $nbJours / coef($nbJours);
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
	<div class="inline top tiers">
		<?php
		foreach ($SsTotalCateg as $cat => $ssTc) {
			echo 'total '.strtoupper($cat).' = <b>'.$ssTc.' €</b><br />';
		}
		?>
	</div>
	<div class="inline top tiers gros leftText">
		Pour <?php echo $nbJours; ?> jours : <?php echo number_format($totalJour * $nbJours, 2); ?> €, <b>coef <?php echo coef($nbJours); ?></b><br />
		<i class="micro">Tarif 1 jour : <?php echo number_format($totalJour, 2); ?> €</i>
	</div>
	<div class="inline top tiers rightText">
		<?php echo '<div class="enorme">TOTAL H.T. : '.number_format($totalFinal, 2).' €</div><div class="enorme">TOTAL T.T.C. : '.number_format($totalFinal + ($totalFinal * TVA_VAL), 2).' €</div>'; ?>
	</div>
</div>

<div id="modalTekos" class="petit hide">
	<?php include 'plan_tekos_list.php' ; ?>
</div>
