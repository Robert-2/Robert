<?php
	if ( !isset($_SESSION["user"])) { header('Location: index.php'); }
	if ( $_SESSION["user"]->isAdmin() !== true ) { header('Location: index.php'); }
	require('infos_boite.php');
?>

<script src="./fct/infos_Ajax.js"></script>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>


<div class="big">
	<div class="ui-widget-header ui-corner-all center">MODIFICATION DES INFORMATIONS</div>
</div>

<br /><br /><br />

<div class="marge30l gros">
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Raison Sociale</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="NOM_BOITE" value="<?php echo NOM_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Status</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="TYPE_BOITE" value="<?php echo TYPE_BOITE ?>" size="20" /></div>
	</div>
	<br /><br />
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Adresse Postale</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="ADRESSE_BOITE" value="<?php echo ADRESSE_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Code Postal</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="CP_BOITE" value="<?php echo CP_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Ville</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="VILLE_BOITE" value="<?php echo VILLE_BOITE ?>" size="20" /></div>
	</div>
	<br /><br />
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">No de Téléphone</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="TEL_BOITE" value="<?php echo TEL_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Adresse Email</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="EMAIL_BOITE" value="<?php echo EMAIL_BOITE ?>" size="20" /></div>
	</div>
	<br /><br />
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">No de SIRET</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="SIRET_BOITE" value="<?php echo SIRET_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Code APE</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="APE_BOITE" value="<?php echo APE_BOITE ?>" size="20" /></div>
	</div>
	<br /><br />
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">No de TVA</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="N_TVA_BOITE" value="<?php echo N_TVA_BOITE ?>" size="20" /></div>
	</div>
	<div class="inline ui-widget-content ui-corner-all pad10">
		<div class="ui-widget-header ui-corner-all center">Valeur de TVA (%)</div>
		<div class="ui-state-default ui-corner-all"><input type="text" id="TVA_VAL" value="<?php echo TVA_VAL * 100 ?>" size="20" /></div>
	</div>
</div>

<br /><br /><br />

<div class="marge30l big">
	<button class="bouton" id="saveInfos">ENREGISTRER les modifs</button>
</div>

<br/><br/>

<div class="big">
	<div class="ui-widget-header ui-corner-all center">LOGO</div>
</div>

<p><i>Logo actuel</i></p>
<br/><br/>
<img src="gfx/logo.jpg" />
<br/><br/>

<form id="my_form" method="post" action="fct/infos_actions.php" enctype="multipart/form-data">
	<input type="hidden" name="action" value="upload_logo" />
    <input type="file" name="image" accept="image/*">
    <button type="submit" class="bouton">UPLOAD</button>
</form>

<script>

$(function () {
    $('#my_form').on('submit', function (e) {
        // On empêche le navigateur de soumettre le formulaire
        e.preventDefault();
 
        var $form = $(this);
        var formdata = (window.FormData) ? new FormData($form[0]) : null;
        var data = (formdata !== null) ? formdata : $form.serialize();
 
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            contentType: false, // obligatoire pour de l'upload
            processData: false, // obligatoire pour de l'upload
            dataType: 'json', // selon le retour attendu
            data: data,
            success: function (response) {
                // La réponse du serveur
            }
        });
    });
});

</script>