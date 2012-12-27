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
<?
if ($errAuth === true) echo '<div class="ui-state-error ui-corner-all pad10 enorme center">ERREUR ! mauvais mail / mot de passe !</div>';
if (isset($_GET['action']) && $_GET['action'] == 'deconx') echo '<div class="ui-state-default ui-corner-all pad10 enorme center" id="byebye">A bientôt !</div>';
else echo '<div class="ui-state-default ui-corner-all pad10 enorme center" id="byebye">BIENVENUE !</div>';
?>

<div class="center">
	<div class="inline ui-state-highlight ui-corner-all shadowOut padV10 gros leftText" id="messageAccueil" style="width:85%; display:none;">
		<h3 class="center">BIENVENUE !</h3>
		<p class="gros">
			ROBERT v <? echo R_VERSION ?>
		</p>
		<p>
			Si vous ne savez pas à quoi correspond un bouton, vous pourrez voir plus d'infos en <b>laissant la souris</b> au dessus
			pendant 1/2 sec... Alors n'hésitez pas à ballader la souris un peu partout ! <br />
			Faites <b>bien attention au logo</b> tout en haut à gauche, quand il bouge c'est qu'il y a quelque chose qui se charge, il faut attendre qu'il s'arrête.<br />
			Et, pour une meilleure expérience visuelle,	nous vous conseillons d'<b>appuyer sur la touche "F11"</b> du clavier pour mettre le Robert en plein écran !
			<i class="petit">(ça ressemblera plus à un logiciel "stand alone" ;)</i>
		</p>
		<p>
			Si vous trouvez un truc qui cloche, ou qui manque, allez faire un tour dans le <b><a href="bugHunter.php">BUGs HUNTER</a></b> après vous être connecté.
		</p>
		<p class="gros">
			C'est parti !!
		</p>
	</div>
</div>