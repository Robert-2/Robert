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
<form action="index.php?go=calendrier" method="post" id="connexionRobert">
	<input type="hidden" name="conx" value="MPM" />
    <div class="connexion-label">Connectez-vous avec votre adresse email et votre mot de passe :</div>
	<input type="text" name="email" placeholder="Adresse email" class="connexion-input" />
	<input type="password" name="password" placeholder="Mot de passe" class="connexion-input" />
	<input type="submit" class="bouton boutonMenu connexion-submit" value="Connexion" />
</form>
<?php if ($errAuth === true) : ?>
    <div class="connexion-message ui-state-error ui-corner-all">
        L'adresse email et le mot de passe ne correspondent pas.
    </div>
<?php endif;
if (isset($_GET['action']) && $_GET['action'] == 'deconx') : ?>
    <div class="connexion-message ui-state-highlight ui-corner-all" id="byebye">
        Vous êtes bien déconnecté(e).
    </div>
<?php endif; ?>
<div class="connexion-welcome">
	<div class="inline ui-state-default ui-corner-all shadowOut padV10 gros leftText">
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
            <b><a href="https://robertmanager.org/wiki.php" target="_new">WIKI de Robert</a></b>
		</p>
		<p>
			Si vous trouvez un truc qui cloche, ou qui manque, allez faire un tour dans le
            <b><a href="https://robertmanager.org/buglist.php" target="_new">BugHunter</a></b>
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
