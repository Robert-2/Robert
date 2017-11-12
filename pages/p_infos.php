<?php
if (!isset($_SESSION["user"])) {
    header('Location: index.php');
}
if ($_SESSION["user"]->isAdmin() !== true) {
    header('Location: index.php');
}

$cfg_boite_file = INSTALL_PATH . FOLDER_CONFIG . 'infos_boite.php';
if (file_exists($cfg_boite_file)) {
    include($cfg_boite_file);
} else {
    define('NOM_BOITE', '');
    define('TYPE_BOITE', '');
    define('ADRESSE_BOITE', '');
    define('CP_BOITE', '');
    define('VILLE_BOITE', '');
    define('TEL_BOITE', '');
    define('EMAIL_BOITE', '');
    define('SIRET_BOITE', '');
    define('APE_BOITE', '');
    define('N_TVA_BOITE', '');
    define('TVA_VAL', '0.2');
}
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
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="NOM_BOITE" value="<?= NOM_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Statut</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="TYPE_BOITE" value="<?= TYPE_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <br /><br />
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Adresse Postale</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="ADRESSE_BOITE" value="<?= ADRESSE_BOITE ?>" size="20" class="inputConsts" /></div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Code Postal</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="CP_BOITE" value="<?= CP_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Ville</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="VILLE_BOITE" value="<?= VILLE_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <br /><br />
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">No de Téléphone</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="TEL_BOITE" value="<?= TEL_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Adresse Email</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="EMAIL_BOITE" value="<?= EMAIL_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <br /><br />
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">No de SIRET</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="SIRET_BOITE" value="<?= SIRET_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Code APE</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="APE_BOITE" value="<?= APE_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">No de TVA</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="N_TVA_BOITE" value="<?= N_TVA_BOITE ?>" size="20" class="inputConsts" />
        </div>
    </div>
    <div class="inline ui-widget-content ui-corner-all pad10">
        <div class="ui-widget-header ui-corner-all center">Valeur de TVA (%)</div>
        <div class="ui-state-default ui-corner-all">
            <input type="text" id="TVA_VAL" value="<?= TVA_VAL * 100 ?>" size="20" class="inputConsts" />
        </div>
    </div>
</div>

<br /><br />

<div class="marge30l big">
    <button class="bouton" id="saveInfos">ENREGISTRER les modifs</button>
</div>

<br/><br/>

<div class="big">
    <div class="ui-widget-header ui-corner-all center">LOGO</div>
</div>

<div class="inline marge30l marge15bot big">
    <p>Logo actuel</p>
    <img src="config/logo.jpg" />
</div>

<form class="inline marge30l margeTop10" id="changeLogo" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload_logo" />
    <input type="file" name="newLogo" accept="image/*">
    <button type="submit" class="bouton">ENVOYER</button>
</form>

<br/><br/>
<br/><br/>
