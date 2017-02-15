<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );
require_once ('date_fr.php');

$l = new Liste();
$listeNotes = $l->getListe(TABLE_NOTES, '*', 'date', 'ASC');
unset($l);

$lp = new Liste();
$thisMorning = strtotime('midnight');
$listeNotesPass = $lp->getListe(TABLE_NOTES, '*', 'date', 'ASC', 'date', '<', $thisMorning);
(is_array($listeNotesPass)) ? $btnPurge = '$("#purgeNotes").show();' : $btnPurge = '$("#purgeNotes").hide();' ;
unset($lp);

?>

<script>
	$(function() {
		$('.bouton').button();
		<?php echo $btnPurge ; ?>
	});
</script>

<?php

if (!is_array($listeNotes)) {
	echo '<div class="ui-state-default ui-corner-all pad10 gros center">Cliquez sur le bouton "+" <i class="mini">(à droite ci-contre)</i> pour ajouter un post-it !</div>';
}
else {
	foreach ($listeNotes as $note) {
		$dateOK = datefr(date('l d F Y', $note['date']));
		( $note['important'] == '1' ) ? $state = 'ui-state-error' : $state = 'ui-state-default' ;
		if ($note['date'] == strtotime('midnight')) $state = 'ui-state-highlight';
		elseif ($note['date'] < strtotime('midnight')) $state = 'ui-state-disabled';
		echo '<div class="'.$state.' ui-corner-all pad10 shadowOut">';
		if ( $note['createur'] == $_SESSION['user']->getUserInfos(Users::USERS_PRENOM) )
			echo '	<div style="float:right;"><button class="bouton supprNote" id="'.$note['id'].'" title="Supprimer ce post-it"><span class="ui-icon ui-icon-trash"></span></button></div>';
		echo '	<div id="noteDate-'.$note['id'].'" class="inline mid ui-state-highlight ui-corner-all pad3 gras" style="width:170px;">'.$dateOK.'</div>';
		echo '	<div class="inline mid">(par <b>'.$note['createur'].'</b>)</div>';
		echo '	<div id="noteTxt-'.$note['id'].'" class="margeTop5">'.nl2br($note['texte']).'</div>';
		echo '</div>';
	}
}

?>

