<script>
	$(function() {
		$('#logo').children('img').attr('src', 'gfx/Robert-anim2.png');
		setTimeout("showMessageAccueil()", 2000);
	});

	function showMessageAccueil () {
		$('#byebye').hide(600, function(){
			$('#messageAccueil').show(600);
			$('#logo').children('img').attr('src', 'gfx/Robert2.png');
		});
	}

</script>


<div id='logo'><img src="gfx/Robert2.png" alt="LOGO"/></div>

<div id="connexionRobert" class="big">
	<form action="index.php?go=calendrier" method="post">
		<input type="hidden" name="conx" value="MPM" />
		<input type="text" name="email" value="votre email" size="20" /><br />
		<input type="password" name="password" value="pass" size="20" /><br />
		<input type="submit" class="bouton boutonMenu" value="Connexion" /><br />
	</form>
</div>
<br /><br />
<?php
if ($errAuth === true) echo '<div class="ui-state-error ui-corner-all pad10 enorme center">ERREUR ! mauvais mail / mot de passe !</div>';
if (isset($_GET['action']) && $_GET['action'] == 'deconx') echo '<div class="ui-state-default ui-corner-all pad10 enorme center" id="byebye">A bientôt !</div>';
else echo '<div class="ui-state-default ui-corner-all pad10 enorme center" id="byebye">BIENVENUE !</div>';
?>

<div class="center">
	<div class="inline ui-state-highlight ui-corner-all shadowOut padV10 gros leftText" id="messageAccueil" style="width:85%; display:none;">
		<h3 class="center">BIENVENUE !</h3>
		<p class="gros">
			<a href="http://www.robert.polosson.com" target="_new">ROBERT v.<?php echo R_VERSION ?></a>
		</p>
		<p>
			Si vous ne savez pas à quoi correspond un bouton, vous pourrez voir plus d'infos en <b>laissant la souris</b> au dessus
			pendant 1/2 sec... Alors n'hésitez pas à ballader la souris un peu partout ! <br />
			Faites <b>bien attention au logo</b> tout en haut à gauche, quand il bouge c'est qu'il y a quelque chose qui se charge, il faut attendre qu'il s'arrête.<br />
			Et, pour une meilleure expérience visuelle,	nous vous conseillons d'<b>appuyer sur la touche "F11"</b> du clavier pour mettre le Robert en plein écran !
		</p>
		<p>
			Vous trouverez de l'aide sur le fonctionnement du Robert ici : <b><a href="http://www.robert.polosson.com/index.php?go=3wiki" target="_new">WIKI du Robert</a></b>
		</p>
		<p>
			Si vous trouvez un truc qui cloche, ou qui manque, allez faire un tour dans le <b><a href="http://www.robert.polosson.com/index.php?go=7bugHunter" target="_new">BugHunter</a></b> pour nous en faire part !
		</p>
		<hr />
		<p class="mini">
			<i>Le Robert est un <b>logiciel libre.</b>
			Vous pouvez le redistribuer et/ou le modifier sous les termes de la <b>Licence Publique Générale GNU Affero</b>
			comme publiée par la Free Software Foundation, version 3.0.</i>
		</p>
	</div>
</div>