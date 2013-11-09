
<div id="topPage">
	<div id="logo"><img src="gfx/Robert2.png" alt="LOGO"/></div>
	
    <div id="titrePage">
		<?php echo $titrePage; ?><br />
		<span class="boutonMenu nano noMarge">system <a href="debug.php?file=d_Calendar.php"><b>Flask</b></a></span>
	</div>
	
	<div id="connexion">
		<?php 
		if ($logged === true) {
			echo 'Bienvenue <b>'.$_SESSION['user']->getUserInfos('prenom').'</b> !
				  <span class="boutonMenu"><a href="index.php?action=deconx">déconnexion</a></span><br />
				<select id="themeSel">
					<option disabled selected value="">Choisir un thème :</option>';
					include ('fct/list_themes.php');
					$themesDispo = list_themes();
					foreach ($themesDispo as $theme)
						echo "<option value='$theme'>$theme</option>";
			echo '</select>';
		}
		else {
			echo '<form action="index.php?go=p_test.php" method="post">
					<input type="hidden" name="conx" value="MPM" />
					<input type="text" name="email" value="votre email" size="20" /><br />
					<input type="password" name="password" value="pass" size="20" />
					<input type="submit" class="boutonMenu" value="Connexion" /><br />
				</form>';
		}
		if ($errAuth === true) echo '<span class="ui-state-error pad5">ERREUR ! mauvais mail / mot de passe</span>';
?>
	</div>
</div>

