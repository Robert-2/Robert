<?php
	if ( !isset($_SESSION["user"])) { header('Location: index.php'); }
	
	$hideForPoppy = '';
	if ( ! $_SESSION["user"]->isLevelMod() )
		$hideForPoppy = 'style="display:none;"'
?>
	
<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>


<div class="miniSousMenu padV10">
	<div class="sousMenuIcon inline bouton big structMiniSsMenu" title="STRUCTURES">
		<img src="gfx/icones/menu/mini-structures.png" />
	</div>
	<div class="inline top leftText">
		<div class="sousMenuBtns hide" id="structMenuBtns">
			<a class="bouton miniSmenuBtn" id="benef_list_struct" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="benef_add_struct" <?php echo $hideForPoppy; ?> href="#">Ajout</a>
		</div>
	</div>
	
	<div class="sousMenuIcon inline bouton big marge30l interlocMiniSsMenu" title="INTERLOCUTEURS">
		<img src="gfx/icones/menu/mini-beneficiaires.png" />
	</div>
	<div class="inline top rightText">
		<div class="sousMenuBtns hide" id="interlocMenuBtns">
			<a class="bouton miniSmenuBtn" id="benef_list_interloc" href="#">Liste</a>
<!--			<a class="bouton miniSmenuBtn" id="benef_add_interloc" <?php echo $hideForPoppy; ?> href="#">Ajout</a>-->
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



<div id="benefPage" class="pageContent">
	<?php
	if ( isset($_GET['sousPage']) ) {
		$goto = $_GET['sousPage'].'.php';
	}
	else $goto = 'benef_list_struct.php';
	
	if ((@include($goto)) == false)
		echo "<div class='ui-state-error ui-corner-all enorme center pad10'>Cette page n'existe pas !</div>";
	
	?>
</div>
