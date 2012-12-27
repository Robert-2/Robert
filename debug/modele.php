<?php if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}


?>

<script type="text/javascript" src=""></script>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">section titre</div>
	<br />
	Section contenu
</div>
	

<div id="debugAjax" class="debugSection ui-state-error pad10">section retour Ajax</div>

	
<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">section titre</div>
	<br />
	Section contenu
</div>

<?php
	if (isset($_SESSION["user"])) {
		$debugVarsTitre = "Titre du visionneur de var";
		$debugVars = "Hello world ! Les vars ici !";
	}
?>