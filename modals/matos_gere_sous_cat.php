<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$lscm = new Liste();
$liste_sousCat = $lscm->getListe(TABLE_MATOS_CATEG, '*', 'ordre', 'ASC');
$maxOrdreSsCat = Liste::getMax(TABLE_MATOS_CATEG, 'ordre');
?>

<script>
	$(function(){
		$('#sousCategList').sortable({placeholder: "ui-state-highlight", cancel: ".modifCatLabel"});
	});
</script>


<div class="inline top ui-state-disabled padV10 padH5 ui-corner-all leftText shadowIn" style="width:745px;">
	Modifiez le nom en cliquant sur <span class="inline ui-icon ui-icon-pencil"></span>, ou directement dessus, puis pressez "Entrée".
	Pour ajouter une sous catégorie, c'est le bouton <span class="inline ui-icon ui-icon-plusthick"></span> ci-contre.<br />
	Faites glisser les sous catégories pour les ranger dans l'ordre voulu, grâce au bouton <span class="inline ui-icon ui-icon-carat-2-n-s"></span>
</div>
<div class="inline top enorme rightText marge15bot" style="width:55px;">
	<input type="hidden" class="hide" id="max_ordre_ssCat" value="<?php echo (int)$maxOrdreSsCat +1 ; ?>"/>
	<button class="bouton" id="ajouteSousCatMatos" title="AJOUTER une Sous Catégorie">
		<span class="ui-icon ui-icon-plusthick"></span>
	</button>
</div>
<br />
<div class="inline top" id="ordreCategList">
	<?php
	foreach ($liste_sousCat as $sousCat) {
		echo '<div class="ui-state-default ui-corner-all pad20 leftText enorme gras" style="height: 25px; width:20px;">'.$sousCat['ordre'].'</div>';
	}
	?>
</div>

<div class="inline top" id="sousCategList" style="width: 700px;">
<?php
foreach ($liste_sousCat as $sousCat) {
	echo '<div class="ui-state-default ui-corner-all pad10 leftText gros matosSousCatItem" style="height: 25px;" id="ssCat-'.$sousCat['id'].'">
		<div style="float:right">
			<button class="bouton modifCatLabel"><span class="ui-icon ui-icon-pencil"></span></button>
			<button class="bouton supprCat"><span class="ui-icon ui-icon-trash"></span></button>
		</div>
		<div class="inline top ui-state-default ui-corner-all pad5 poigneeSsCat"><span class="ui-icon ui-icon-carat-2-n-s"></span></div>
		<div class="inline top pad5 modifCatLabel" style="width:500px;" id="nameSsCat-'.$sousCat['id'].'">'.$sousCat['label'].'</div>
	</div>';
}
?>
</div>