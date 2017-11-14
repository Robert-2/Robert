<script>
$(function() {
	$('#logo').children('img').attr('src', 'gfx/Robert-anim2.png');
    $('.colonne.R').hide();
    $('.colonne.C').css({right:0});
	setTimeout(function() {
        $('#logo').children('img').attr('src', 'gfx/Robert2.png');
    }, 2000);
});
</script>
<div id='logo'>
    <img src="gfx/Robert2.png" alt="LOGO"/>
    <span class="version"><?= R_VERSION ?></span>
</div>
<div id="connexionRobert">
	<form action="index.php?go=calendrier" method="post">
		<input type="hidden" name="conx" value="MPM" />
		<input type="text" name="email" placeholder="adresse email" class="connexion-input" /><br />
		<input type="password" name="password" placeholder="mot de passe" class="connexion-input" /><br />
		<input type="submit" class="bouton boutonMenu connexion-submit" value="Connexion" />
	</form>
</div>
<?php if ($errAuth === true) : ?>
    <div class="ui-state-error ui-corner-all pad10 big center">
        L'adresse email et le mot de passe ne correspondent pas.
    </div>
<?php endif;
if (isset($_GET['action']) && $_GET['action'] == 'deconx') : ?>
    <div class="ui-state-highlight ui-corner-all pad10 big center" id="byebye">
        Vous êtes bien déconnecté(e).
    </div>
<?php endif; ?>
<div class="connexion-welcome">
	<div class="inline ui-state-default ui-corner-all shadowOut padV10 gros leftText"
         style="width:85%;">
		<h3 class="center">BIENVENUE !</h3>
		<p>
			Si vous ne savez pas à quoi correspond un bouton, vous pourrez voir plus
            d'infos en <b>laissant la souris</b> au dessus
			pendant 1/2 sec... Alors n'hésitez pas à ballader la souris un peu partout ! <br />
			Faites <b>bien attention au logo</b> tout en haut à gauche, quand il bouge
            c'est qu'il y a quelque chose qui se charge, il faut attendre qu'il s'arrête.
        </p>
        <p>
			Pour une meilleure expérience visuelle,	nous vous conseillons d'<b>appuyer sur
            la touche "F11"</b> du clavier pour mettre Robert en plein écran !
		</p>
		<p>
			Vous trouverez de l'aide sur le fonctionnement de Robert ici :
            <b><a href="http://www.robert.polosson.com/wiki.php" target="_new">WIKI de Robert</a></b>
		</p>
		<p>
			Si vous trouvez un truc qui cloche, ou qui manque, allez faire un tour dans le
            <b><a href="http://www.robert.polosson.com/buglist.php" target="_new">BugHunter</a></b>
            pour nous en faire part !
		</p>
		<hr />
		<p class="mini">
			<i>Robert est un <b>logiciel libre.</b>
			Vous pouvez le redistribuer et/ou le modifier sous les termes de la
            <b>Licence Publique Générale GNU Affero</b> comme publiée par la Free Software Foundation, version 3.0.</i>
		</p>
	</div>
</div>
