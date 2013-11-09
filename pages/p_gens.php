<?php
	if ( !isset($_SESSION["user"])) { header('Location: index.php'); }
	
	$hideForUser = '';
	if ( $_SESSION["user"]->isAdmin() !== true )
		$hideForUser = 'style="display:none;"';
	
	$hideForPoppy = '';
	if ( ! $_SESSION["user"]->isLevelMod() )
		$hideForPoppy = 'style="display:none;"';
?>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>


<div class="miniSousMenu padV10">
	<div class="sousMenuIcon inline bouton big tekosMiniSsMenu" title="TECHNICIENS">
		<img src="gfx/icones/menu/mini-techniciens.png" />
	</div>
	<div class="inline top leftText">
		<div class="sousMenuBtns hide" id="tekosMenuBtns">
			<a class="bouton miniSmenuBtn" id="personnel_list_techniciens" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="personnel_add_techniciens" <?php echo $hideForPoppy ?> href="#">Ajout</a>
		</div>
	</div>
	
	<div class="sousMenuIcon inline bouton big marge30l usersMiniSsMenu" <?php echo $hideForUser ?> title="UTILISATEURS">
		<img src="gfx/icones/menu/mini-personnel.png" />
	</div>
	<div class="inline top rightText">
		<div class="sousMenuBtns hide" id="usersMenuBtns">
			<a class="bouton miniSmenuBtn" id="personnel_list_utilisateurs" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="personnel_add_utilisateurs" <?php echo $hideForPoppy ?> href="#">Ajout</a>
		</div>
	</div>
	
	
	<div class="inline top center" style="display: none;" id="chercheDiv">
		
		<div class="inline top Vseparator bordSection"></div>
		
		<div class="inline top">
			<input type="text" id="chercheInput" size="15" />
			<br />
			<select id="filtreCherche"></select>
		</div>
		<div class="inline top nano">
			<button class="bouton chercheBtn">
				<span class="ui-icon ui-icon-search"></span>
			</button>
		</div>
		
		<div class="inline top Vseparator bordSection"></div>
		
	</div>
	
	
</div>


<div id="personnelPage" class="pageContent">
	<?php
	if ( isset($_GET['sousPage']) ) {
		$goto = $_GET['sousPage'].'.php';
	}
	else $goto = 'personnel_list_techniciens.php';
	
	if ((@include($goto)) == false)
		echo "<div class='ui-state-error ui-corner-all enorme center pad10'>Cette page n'existe pas !</div>";
	
	?>
</div>


