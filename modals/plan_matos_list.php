
<div id="toolTipPopup" class="ui-state-highlight ui-corner-all pad10 hide"></div>

<div class="addSection ui-widget-content ui-corner-all leftText pad20 hide" id="etape-3">
	<div class="ui-widget-header ui-corner-all pad5 gros">Choix du matériel</div>
	<br />
	<div class="ui-state-disabled pad10 ui-corner-all leftText shadowIn" id="messHelpMatos" style="float:right; ">
		Bouton "+" pour ajouter un pack,<br /> Bouton "-" pour en enlever un.
	</div>
	<div class="inline top leftText mini">
		<div class="inline top center enorme" style="width: 160px;">
			<button class="bouton big" id="togglePacksMatos" title="voir le matériel en détail pour être plus précis">MATÉRIEL au détail</button>
		</div>
		<button class="inline top bouton" id='add_Matos_Rapide' title="Ajouter un matériel vite fait">
			<span class="ui-icon ui-icon-plusthick doigt">ajouter</span>
		</button>
		
		<div class="inline top Vseparator bordSection"></div>
		
		<button class="bouton filtreMatos" id="son" title="voir le matos SON"><img src="./gfx/icones/categ-son.png" alt="SON" width="30" /></button>
		<button class="bouton filtreMatos" id="lumiere" title="voir le matos LUMIERE"><img src="./gfx/icones/categ-lumiere.png" alt="LUMIERE" width="30" /></button>
		<button class="bouton filtreMatos" id="structure" title="voir le matos STRUCTURE"><img src="./gfx/icones/categ-structure.png" alt="STRUCTURE" width="30" /></button>
		<button class="bouton filtreMatos" id="transport" title="voir le matos TRANSPORT"><img src="./gfx/icones/categ-transport.png" alt="TRANSPORT" width="30" /></button>
		<button class="bouton filtreMatos" id="polyvalent" title="voir le matos POLYVALENT"><img src="./gfx/icones/categ-polyvalent.png" alt="POLYVALENT" width="30" /></button>
		
		<div class="inline top Vseparator bordSection"></div>
		<div class='inline top'>
			<button class="bouton filtreMatos" id="int-ext" title="matos INTERNE / EXTERNE au Parc"><img src="./gfx/icones/matosExterne.png" alt="INT/EXT" width="30"></button>
		</div>
	</div>
	<br />
	<br />
	

	<div id="packsHolder" class="center shadowOut">
		<?php
			foreach ( $listePacks as $k => $v ){
				$id         = $v['id'] ;
				$label      = $v['ref'] ;
				$categPack  = $v['categorie'] ;
				$qte        = $v['Qtotale'];
				$detail		= json_decode($v['detail'], true);
				$ext		= $v['externe'];
				( $ext == '1')? $externeIcon  = "<img src='gfx/icones/matosExterne.png' alt='externe' title='matériel externe au parc' />" : $externeIcon = '';
				( $ext == '1')? $externeClass = "matosExterne" : $externeClass = '';
				( $ext == '1')? $externeHideDispo = "display:none;" : $externeHideDispo = '';

				echo "<div id='pack-$id' class='ui-state-default leftText big packPik cat-$categPack $externeClass'>
						<div class='inline mid rightText' style='width:100px; '>
							<span class='ui-state-disabled'>PACK</span>
						</div>
						<div class='inline mid rightText' style='width:100px;'>
							$externeIcon
						</div>
						<div class='inline mid matos_categ rightText accordionOpen' style='width:100px;'>
							<div class='inline mid pack_categ'><img src='gfx/icones/categ-$categPack.png' alt='$categPack' /></div>	
						</div>							
						<div class='inline mid quart leftText pad30L pack_name accordionOpen'>$label</div>
						<div class='inline mid quart packDispo rightText mini'>
							<div class='inline mid qteDispo' style='$externeHideDispo'>
								Dispo : <span class='qteDispo_QTE'></span>
								<span class='qteDispo_MAX hide'></span>
							</div>
							<div class='inline togglePack enorme'>
								<button class='bouton pack_plus' id='plus' href='#'><span class='ui-icon ui-icon-plusthick'></span></button>
								<button class='bouton pack_plus' id='moins' href='#'><span class='ui-icon ui-icon-minusthick'></span></button>
							</div>
						</div>
						<div class='inline mid padV10 bordFin bordSection' style='display:none;' id='qtePik-$id' title='Combien on peut en faire avec la sélection de matériel actuelle'>0</div>
					</div>
					<div id='packDetail-$id' class='pad5 leftText packDetail'>
						<div class='inline quart ui-state-disabled'>Détail du pack :</div>
						<div class='inline quart ui-state-disabled'>Disponible :</div>";
					foreach ($detail as $id => $qte) {
						foreach ($listeMatos as $k => $matos) {
							if ($matos['id'] == $id)
								$ref = $matos['ref'];
						}
						echo "<div id='pD-$id' class='packItem pD-$id'>
								<div class='inline quart marge30l leftText'><span class='need'>$qte</span> x $ref</div>
								<div class='inline quart leftText dispo'></div>
							  </div>";
					}
				echo "</div>";
			}
		?>
	</div>
	
	<div id="matosHolder" class="center shadowOut gros hide"> <?php
		include('matos_tri_sousCat.php');
		
		$matos_by_categ = creerSousCatArray($listeMatos);
		$categById		= simplifySousCatArray();
		
//		echo '<pre>'; print_r($matos_by_categ); echo '</pre>';
		
		if (is_array($matos_by_categ)) {
			foreach ($categById as $catInfo) {
				$index = $catInfo['id'];
				$hideSsCat = '';						// n'affiche rien si la sous catégorie est vide !
				if (!is_array(@$matos_by_categ[$index]))
					$hideSsCat = 'style="display:none;"';
				echo '<div class="leftText gros gras pad5 sousCategLine" idSsCat="'.$index.'" '.$hideSsCat.'>'.$catInfo['label'].'</div>';
				foreach ($matos_by_categ[$index] as $v) {
					$id         = $v['id'] ;
					$label      = $v['ref'] ;
					$categMat   = $v['categorie'] ;
					$qte        = $v['Qtotale'];
					$panne      = $v['panne'];
					$pu         = $v['tarifLoc'];
					$ext		= $v['externe'];
					$extChezQui = $v['ownerExt'];

					$qte -= $panne ; 
					( $panne > 0 )? $affichPanne = "<span class='mini red'>(+ $panne en panne)</span>" : $affichPanne = '';
					( $ext == '1')? $externeIcon = "<img src='gfx/icones/matosExterne.png' alt='externe' popup='matériel externe au parc !<br />A louer chez <b>$extChezQui</b>' />" : $externeIcon = '';
					( $ext == '1')? $externeClass = "matosExterne" : $externeClass = '';
					( $ext == '1')? $externeHideDispo = "class='hide'" : $externeHideDispo = '';

					echo "<div id='matos-$id' class='ui-state-default matosPik cat-$categMat $externeClass pad3'>
								<div class='inline mid rightText' style='width:100px; '>
									<span class='ui-state-disabled'>DETAIL</span>
								</div>
								<div class='inline mid rightText' style='width:100px;'>
									$externeIcon
								</div>
								<div class='inline mid matos_categ rightText' style='width:100px;'>
									<img src='gfx/icones/categ-$categMat.png' alt='$categMat' title='catégorie $categMat' class='marge30l' />
								</div>
								<div class='inline mid quart leftText pad30L matos_name' ext='$ext'>$label</div>
								<div class='inline mid quart matosDispo rightText mini' style='width:200px;'>
									<div class='inline mid qteDispo'>
										<div><span>Total : </span><span class='qteDispo_total'> $qte </span></div>
										<div $externeHideDispo><span>Dispo : </span><span class='qteDispo_update'></span></div>
										<div class='hide'><span class='qteDispo_onload'></span></div>
										<div class='qtePanne center'>$affichPanne</div>
									</div>
									<div class='inline mid qtePik bordFin bordSection' id='$id'><input type='text' class='qtePikInput hide' size='2' value='0' /></div>
									<div class='inline mid matos_plus'><button class='bouton plus'><span class='ui-icon ui-icon-plusthick'></span></button></div>

								</div>

								<div class='inline mid quart'>
									<div class='inline mid demi petit rightText'><span class='matos_PU'>$pu €</span></div>
									<div class='inline mid demi gros'> = <span class='matos_PRICE'>0</span> €</div>
								</div>
							</div>";
				}
			}
		}
	?></div>
	
	<br />
	<br />
</div>

<?php
$lm = new Liste();
$liste_ssCat = $lm->getListe(TABLE_MATOS_CATEG);
?>

<div class="ui-widget-content ui-corner-all leftText petit hide" id="addMatosModal">
	<div class="inline top center pad3" style="width: 140px;">
		<div class="ui-widget-header ui-corner-all">Référence : <b class="red">*</b></div>
		<input type="text" id="newMatosRef" class="addMatosInput" size="15" />
	</div>
	<div class="inline top center pad3" style="width: 600px;">
		<div class="ui-widget-header ui-corner-all">Désignation complète : <b class="red">*</b></div>
		<input type="text" id="newMatosLabel" class="addMatosInput" size="72" />
	</div>
	<br />
	<div class="inline top center pad3" style="width: 140px;">
		<div class="ui-widget-header ui-corner-all">Catégorie : <b class="red">*</b></div>
		<select id="newMatosCateg">
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="structure">STRUCTURE</option>
			<option value="transport">TRANSPORT</option>
		</select>
	</div>
	<div class="inline top center pad3" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Sous Categ :</div>
		<select id="newMatosSousCateg">
			<option value="0">---</option>
			<?php
			foreach ($liste_ssCat as $ssCat) {
				echo '<option value="'.$ssCat['id'].'">'.$ssCat['label'].'</option>';
			}
			?>
		</select>
	</div>
	<div class="inline top center pad3" style="width: 120px;">
		<div class="ui-widget-header ui-corner-all">Tarif loc. : <b class="red">*</b></div>
		<input class="NumericInput" type="text" id="newMatosTarifLoc" class="addMatosInput" size="6" /> €
	</div>
	<div class="inline top center pad3" style="width: 130px;">
		<div class="ui-widget-header ui-corner-all">Val. Remp. : <b class="red">*</b></div>
		<input class="NumericInput" type="text" id="newMatosValRemp" class="addMatosInput" size="8" /> €
	</div>
	<div class="inline top center pad3" style="width: 120px;">
		<div class="ui-widget-header ui-corner-all">Qté Parc : <b class="red">*</b></div>
		<input class="NumericInput" type="text" id="newMatosQtotale" class="addMatosInput" size="7" />
	</div>
	<br />
	<div class="inline top center pad3" style="width: 480px;">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea id="newMatosRemark" cols="55" rows="5"></textarea>
	</div>
	<div class="inline top center pad3" style="width: 130px;">
		<div class="ui-widget-header ui-corner-all">Externe ?</div>
		<input type="checkbox" id="newMatosExterne" class="externeBox" />
	</div>
	<div class="inline top center pad3" style="width: 120px;">
		<div id="dateAchatDiv">
			<div class="ui-widget-header ui-corner-all">Acheté le :</div>
			<input type="text" id="newMatosDateAchat" class="inputCal2" class="addMatosInput" size="9" />
		</div>
		<div id="chezQuiDiv" class="hide">
			<div class="ui-widget-header ui-corner-all">A louer chez :</div>
			<input type="text" id="newMatosExtOwner" size="9" class="addMatosInput" />
		</div>
	</div>

</div>


