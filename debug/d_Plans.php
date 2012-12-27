<?php
if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}

include('plan_Add_Modal.php');

?>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste des Plans</div>
	<?php
		$liste = new Liste ( Plan::PLAN_cID.',' . Plan::PLAN_cTITRE ) ;
		$l = $liste->getListe(TABLE_PLANS) ;
		
		if ( count($l) > 0 ){
			$combo = "<select id='plansTitres'><option value='noSelect' disabled='disabled' selected >LISTE DES PLANS ENREGISTRÉS</option>";
			foreach ($l as $k =>$v){
				$id = $v[Plan::PLAN_cID] ; $ti = $v[Plan::PLAN_cTITRE] ; 
				$combo .= "<option value='$id'>$ti</option>" ;
			}
			echo $combo .= "</select>";
		}
		else { echo "Aucun plan enregistré ds la BDD" ; }
	?>
	<div id="displayPlan"></div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20 hide" id="displayPlanSel">
	<div class='inline tiers top'>
		<table>
			<tr><td>			</td><td class='pad30L' id="dureePlan"></td></tr>
			<tr><td>Début		</td><td class='pad30L'><input type='text' class="inputCal" id="debutPlan" value='' size="12" /></td></tr>
			<tr><td>Fin			</td><td class='pad30L'><input type='text' class="inputCal" id="finPlan"   value='' size="12" /></td></tr>
			<tr><td>Titre		</td><td class='pad30L'><input type='text' id="titrePlan" value='' size="12" /></td></tr>
			<tr><td>Lieu		</td><td class='pad30L'><input type='text' id="lieuPlan"  value='' size="12" /></td></tr>
			<tr><td>Bénéficiaire</td><td class='pad30L'><input type='text' id="benefPlan" value='' size="12" /></td></tr>
			<tr><td>			</td><td><button class="bouton" id="modifPlanBtn">Modifier</button>
										 <button class="bouton" id="supprPlanBtn">Supprimer</button></td></tr>
		</table>
	</div>
	<div class='inline deuxTiers top' id='ListSpPlan'>
		DÉTAIL DES JOURS<br />
	</div>
</div>


<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>