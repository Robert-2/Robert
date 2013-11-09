<?php
	if ( ! isset($_SESSION["user"])) { header('Location: index.php'); }
	$hideForPoppy = '';
	if ( ! $_SESSION["user"]->isLevelMod() )
		$hideForPoppy = 'style="display:none;"'
	
?>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>


<div class="miniSousMenu padV10">
	<div class="sousMenuIcon inline bouton big tekosMiniSsMenu">
		<img src="gfx/icones/menu/mini-techniciens.png" />
	</div>
	<div class="inline top leftText">
		<div class="sousMenuBtns" id="tekosMenuBtns">
			<a class="bouton miniSmenuBtn" id="personnel_list_techniciens" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="personnel_add_techniciens" <?php echo $hideForPoppy; ?> href="#">Ajout</a>
		</div>
	</div>
	
	<div class="inline top center" style="display: none;" id="chercheDiv">
		
		<div class="inline top Vseparator bordSection"></div>
		
		<div class="inline top margeTop10">
			<input type="text" id="chercheInput" size="15" />
		</div>
		<div class="inline top margeTop10">
			<select id="filtreCherche"></select>
		</div>
		<div class="inline top nano margeTop10">
			<button class="bouton chercheBtn">
				<span class="ui-icon ui-icon-search"></span>
			</button>
		</div>
		
		<div class="inline top Vseparator bordSection"></div>
		
	</div>
</div>



<div id="tekosPage" class="pageContent">
	<?php
	if ( isset($_GET['sousPage']) ) {
		$goto = $_GET['sousPage'].'.php';
		if ((@include($goto)) == false)
			echo "<div class='ui-state-error ui-corner-all enorme center pad10'>Cette page n'existe pas !</div>";
	}
	else include('personnel_list_techniciens.php');
	?>
</div>