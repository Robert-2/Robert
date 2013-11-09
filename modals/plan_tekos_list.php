
<script>
	$(function() {
		$(".filtreTekos").off();
		$(".filtreTekos").click(function (){
			var id = $(this).attr('id');
			
			if ( $(this).hasClass('ui-state-error') ){
				// affiche tout //
				$(this).removeClass('ui-state-error');
				$("#tekosmodalHolder").find('.tekosPik').show();
			}
			else {
				$("#modalTekos").find('.filtreTekos').removeClass('ui-state-error');

				$("#tekosmodalHolder").find('.tekosPik').each( function (ind, obj) {
					var currentCat = $(this).find('.tek_categ img').attr('alt') ;
					if ( currentCat == id )
						$(this).show();
					else
						$(this).hide();

				});
				$(this).addClass('ui-state-error');
			}
		});
	});
</script>

<div class="addSection ui-widget-content ui-corner-all pad20 hide" id="etape-2">
	<div class="ui-widget-header ui-corner-all pad5 center gros">Choix des techniciens</div>
	<br />
	<div class="inline top tiers center mini" id="filtresTekosDiv">
		<button class="bouton filtreTekos" id="son" title="voir les SONDIERS"><img src="./gfx/icones/categ-son.png" alt="SON" width="30" /></button>
		<button class="bouton filtreTekos" id="lumiere" title="voir les LIGHTEUX"><img src="./gfx/icones/categ-lumiere.png" alt="LUMIERE" width="30" /></button>
		<button class="bouton filtreTekos" id="polyvalent" title="voir les POLYVALENTS"><img src="./gfx/icones/categ-polyvalent.png" alt="POLYVALENT" width="30" /></button>
		<button class="bouton filtreTekos" id="roadie" title="voir les ROADIES"><img src="./gfx/icones/categ-roadie.png" alt="STRUCTURE" width="30" /></button>
	</div>
	<div class="inline top center big" style="width: 60%;">
		Pour la période du <span id="periode"></span><br />
		<span id="displayNbPlanSimult" class="red mini"></span>
	</div>
	<br /><br />
	<div id="tekosHolder" class="inline top tiers center shadowOut"> <?php
		foreach ( $listeTekos as $k => $v ) {
			$id  = $v['id'];
			$surnom = $v['surnom'];
			$categTek = $v['categorie'];
			echo "<div id='tek-$id' class='ui-state-default tekosPik doigt pad3'>
					<div class='inline mid tek_categ'><img src='gfx/icones/categ-$categTek.png' alt='$categTek' /></div>
					<div class='inline mid tiers tek_name gros' id='$id'>$surnom</div>
					<div class='inline mid tiers tekosDispo'></div>
				</div>";
		}
		?>
	</div>
	<div class="inline top pad10 ui-corner-all marge30l center shadowIn" style="width: 60%; height:300px;">
		<div class="tekosVideHelp ui-state-disabled gros marge15bot">
			Cliquez sur un technicien pour l'ajouter à l'équipe
		</div>
		<div id="tekosEquipe" class="enorme margeTop10">
			
		</div>
	</div>
	<br />
	<br />
</div>


