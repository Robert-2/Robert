<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );
require_once ('date_fr.php');

$lp = new Liste();
$listeBenef = $lp->getListe ( TABLE_STRUCT, 'id, label' );
$listeTekos = $lp->getListe ( TABLE_TEKOS, 'id, surnom, categorie', 'surnom', 'ASC' );
$listeMatos = $lp->getListe ( TABLE_MATOS, '*', 'categorie', "ASC" ) ;
$listePacks = $lp->getListe ( TABLE_PACKS, '*', 'categorie', "ASC" ) ;
$lp = null ;

// initialise une liste des categories de tekos et de matos
$catTekos = array(); $catMatos = array();
foreach ( $listeTekos as $k => $v ) { if ( ! in_array($v['categorie'], $catTekos) ) $catTekos[] = $v['categorie'] ; }
foreach ( $listeMatos as $k => $v ) { if ( ! in_array($v['categorie'], $catMatos) ) $catMatos[] = $v['categorie'] ; }


// Pour l'autocomplete des bénéficiaires
$varsBenefs = '';
foreach ($listeBenef as $v ){
	$varsBenefs .= "'".$v['label']."',";
}
$varsBenefs = substr($varsBenefs,0,strlen($varsBenefs)-1 ) ;

?>


<script type="text/javascript" src="./js/init_all_pages.js"></script>
<script type="text/javascript" src="./js/JSON.js"></script>

<script type="text/javascript" src="./fct/plans_ajax.js"></script>
<script type="text/javascript">
	var autoCompleteBENEF = [<?php echo $varsBenefs; ?>];
	$(function() {
		$("#<?php echo Plan::PLAN_cBENEFICIAIRE ; ?>").autocomplete( {source: autoCompleteBENEF});
		initToolTip('#planTekosMatos');
	});
</script>

<style>
	.addSection	{ box-shadow: inset 0 0 8px #888888; }
	#planTekosMatos { list-style-type: none; }
	#planTekosMatos li { margin: 0 3px 3px 3px; padding: 5px 15px; font-size: 1.4em; height: 40px; width:90%;}
	.TekosSelected  { border:2px solid #3DA333; }
	.sousPlanInfos  { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 200px; height: 200px; font-size: 1em; text-align: center; border : 1px solid black;}
</style>


<div class="addSection ui-widget-content ui-corner-all center gros pad20" id="planInfos">
	<div class="ui-widget-header ui-corner-all pad5">Informations, et période de l'évènement à ajouter</div>
	<br />
	<div class="inline top gros" style="width: 250px;">
		<div class="ui-widget-header ui-corner-all petit">Titre : <b class="red">*</b></div>
		<input class="newPlan_data petit" type="text" id="<?php echo Plan::PLAN_cTITRE ; ?>" size="20" value="Plan"/>
		<br />
		<div class="ui-widget-header ui-corner-all petit margeTop5">Bénéficiaire : <b class="red">*</b></div>
		<input class="newPlan_data petit" type="text" id="<?php echo Plan::PLAN_cBENEFICIAIRE ; ?>" size="20" value="Nous"/>
		<br />
		<div class="ui-widget-header ui-corner-all petit margeTop5">Lieu : <b class="red">*</b></div>
		<input class="newPlan_data petit" type="text" id="<?php echo Plan::PLAN_cLIEU ; ?>" size="20" value="Ici"/>
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
	<div class="inline bot" style="width: 120px;">
		<button class="bouton" id="PlanAddNext1">SUIVANT ></button>
	</div>
	<br /><br /><br />
</div>


<div class="addSection ui-widget-content ui-corner-all center gros pad20 hide" id="planTekosMatos">
	<div class="ui-widget-header ui-corner-all pad5"> Choix des techniciens et du matériel pour la période du <span id="periode"></span></div>
	<p id="displayNbPlanSimult" class="leftText red petit"></p>
	<div class="big">
		Création de <i><b id="rappelTitrePlan"></b></i>	à <b id="rappelLieuPlan"></b>, pour <b id="rappelBenefPlan"></b>
		<span class="micro"><button class="bouton" id="PlanAddPrev1">< PRÉCÉDENT</button></span>
	</div>
	<table class="petit" style="width:99%;">
			<TH class="ui-corner-all"> Choix des techniciens - filtre : <select id="selectTekosCateg"><option>Tout</option>
						<?php foreach ( $catTekos as $k) echo "<option>$k</option>"; ?></select></TH>
						
			<TH class="ui-corner-all">
				Choix du matos - filtre :
				<select id="selectMatosCateg"><option>Tout</option><?php foreach ( $catMatos as $k) echo "<option>$k</option>"; ?></select>
				<button class="bouton" id="packMatosSwitch">MATÉRIEL au détail</button>
			</TH>
			<tr>
				<td class="tiers bordFin top">
					<div class="mini pad10" id="TekosHolder">
						<?php
							foreach ( $listeTekos as $k => $v ){
								$id =  $v['id'];
								$sur = $v['surnom'];
								$categTek = $v['categorie'];
								
								echo "<li id='tek-$id' class='ui-state-default tekosPik doigt'>
											<div class='inline mid tek_categ'><img src='gfx/icones/categ-$categTek.png' alt='$categTek' /></div>
											<div class='inline mid tiers tek_name' id='$id'>$sur</div>
											<div class='inline mid tiers tekosDispo'></div>
										</li>";
							}
						?>
					</div>
				</td>
				<td class="tiers bordFin top">
					<div class="pad10 hide" id="MatosHolder">
						<?php
							foreach ( $listeMatos as $k => $v ){
								$id         = $v['id'] ;
								$label      = $v['ref'] ;
								$categMat   = $v['categorie'] ;
								$qte        = $v['Qtotale'];
								$panne      = $v['panne'];
								$pu         = $v['tarifLoc'];

								 $qte  -= $panne ; 
								( $panne > 0 )? $affichPanne = "<span class='mini red'>(+ $panne en panne)</span>" : $affichPanne = '';

								echo "<div id='matos-$id' class='ui-state-default matosPik'>
											<div class='inline mid matos_categ'><img src='gfx/icones/categ-$categMat.png' alt='$categMat' /></div>
											<div class='inline mid quart matos_name'>$label</div>
											<div class='inline mid quart matosDispo mini'>
												<div class='inline mid qteDispo'>
													<div><span>Total : </span><span class='qteDispo_total'> $qte </span></div>
													<div><span>Dispo : </span><span class='qteDispo_update'></span></div>
													<div class='hide'><span class='qteDispo_onload'></span></div>
													<div class='qtePanne center'>$affichPanne</div>
												</div>
												<div class='inline mid qtePik bordFin bordSection' id='$id'><input type='text' class='qtePikInput hide' size='2' value='1' /></div>
												<div class='inline mid toggleMatos'><a class='bouton plus' href='#'>+</a></div>
												
											</div>
											
											<div class='inline mid quart'>
												<div class='inline mid'><span> X </span>     <span class='matos_PU'   >$pu</span></div>
												<div class='inline mid'><span> = </span><span class='matos_PRICE'> </span> €</div>
											</div>
										</div>";
							}
						?>
					</div>
					
					<div class="mini" id="PacksHolder">
						<?php
							foreach ( $listePacks as $k => $v ){
								$id         = $v['id'] ;
								$label      = $v['ref'] ;
								$categPack  = $v['categorie'] ;
								$qte        = $v['Qtotale'];
								$detail		= json_decode($v['detail'], true);
								
								echo "<div id='pack-$id' class='ui-state-default packPik'>
											<div class='inline mid pack_categ'><img src='gfx/icones/categ-$categPack.png' alt='$categPack' /></div>								
											<div class='inline mid quart pack_name'>$label</div>
											<div class='inline mid quart packDispo mini'>
												<div class='inline mid qteDispo'>
													<span>Dispo : </span>
													<span class='qteDispo_QTE'></span>
													<span class='qteDispo_MAX hide'></span>
												</div>
												
												<div class='inline mid qtePik bordFin bordSection' id='$id'></div>
												<div class='inline togglePack'>
													<button class='bouton pack_plus' id='plus' href='#'>+</button>
													<button class='bouton pack_plus' id='moins' href='#'>-</button>
												</div>
											</div>
									</div>
									<div id='packDetail-$id' class='leftText packDetail'>
										Détail du pack :<br />";
									foreach ($detail as $id => $qte) {
										foreach ($listeMatos as $k => $matos) {
											if ($matos['id'] == $id)
												$ref = $matos['ref'];
										}
										echo "<div id='pD-$id' class='packItem pD-$id'><div class='inline quart'>$ref</div> <div class='need inline' style='width:50px;'>$qte</div><div class='dispo inline quart center'></div></div>";
									}
								echo "</div>";
							}
						?>
					</div>
				</td>
			</tr>
	</table>
	<p>
		<button class="bouton hide plan_save" id="devis">CRÉER DEVIS</button>
		<button class="bouton ui-state-highlight hide plan_save" id="reservation">RÉSERVATION</button>
	</p>
</div>


<div id="debugAjax" class="addSection ui-state-error pad10 hide"></div>

