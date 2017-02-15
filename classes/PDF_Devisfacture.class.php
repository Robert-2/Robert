<?php
require('FPDF.class.php');
define('EURO', 'Euros' );
define('EURO_VAL', 6.55957 );

// Xavier Nicolay 2004
// Version 1.02
//
// Reste à faire :
// + Multipage (gestion automatique sur plusieurs pages)
//

//////////////////////////////////////
// fonctions à utiliser (publiques) //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class PDF_Devisfacture extends FPDF {
	// variables privées
	var $colonnes;
	var $format;
	var $angle=0;
	var $logoPresent = false;
	var $logoH;

	// fonctions privées
	function RoundedRect($x, $y, $w, $h, $r, $style = '') {
		$k = $this->k;
		$hp = $this->h;
		if($style=='F')
			$op='f';
		elseif($style=='FD' || $style=='DF')
			$op='B';
		else
			$op='S';
		$MyArc = 4/3 * (sqrt(2) - 1);
		$this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
		$xc = $x+$w-$r ;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

		$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
		$xc = $x+$w-$r ;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
		$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
		$xc = $x+$r ;
		$yc = $y+$h-$r;
		$this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
		$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
		$xc = $x+$r ;
		$yc = $y+$r;
		$this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
		$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
		$this->_out($op);
	}

	function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
		$h = $this->h;
		$this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
							$x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
	}

	function Rotate($angle, $x=-1, $y=-1) {
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0)
		{
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	function _endpage() {
		$this->logoPresent = false;
		if($this->angle!=0)
		{
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}

	// fonctions publiques
	function sizeOfText( $texte, $largeur ) {
		$index    = 0;
		$nb_lines = 0;
		$loop     = TRUE;
		while ( $loop )
		{
			$pos = strpos($texte, "\n");
			if (!$pos)
			{
				$loop  = FALSE;
				$ligne = $texte;
			}
			else
			{
				$ligne  = substr( $texte, $index, $pos);
				$texte = substr( $texte, $pos+1 );
			}
			$length = floor( $this->GetStringWidth( $ligne ) );
			$res = 1 + floor( $length / $largeur) ;
			$nb_lines += $res;
		}
		return $nb_lines;
	}

	// Ajoute un logo en haut de page
	function addLogo ( $fichier, $w, $h, $type ) {
		$this->SetXY( 0, 0 );
		$this->Image($fichier, 10, 10, $w, $h, $type);
		$this->logoPresent = true;
		$this->logoH = $h*1.5;
	}


	// Cette fonction affiche en haut, a gauche,
	// le nom de la societe dans la police Arial-14-Bold
	// les coordonnees de la societe dans la police Arial-12
	function addSociete( $nom, $adresse ) {
		if ( $this->logoPresent == true ) {
			 $y1 = $this->logoH;
		}
		else $y1 = 8;
		$x1 = 10;
		$this->SetXY( $x1, $y1 );
		$this->SetFont('Arial','B',14);
		$length = $this->GetStringWidth( $nom );
		$this->Cell( $length, 2, $nom);
		$this->SetXY( $x1, $y1 + 4 );
		$this->SetFont('Arial','',12);
		$length = $this->GetStringWidth( $adresse );
		//Coordonnées de la société
		$lignes = $this->sizeOfText( $adresse, $length) ;
		$this->MultiCell($length, 4, $adresse);
	}


	function addCreateur ($texte, $tailleTxt) {
		if ( $this->logoPresent == true ) {
			 $y1 = $this->logoH + 33;
		}
		else $y1 = 38;
		$x1 = 10;
		$this->SetXY( $x1, $y1 );
		$this->SetFont('Arial','I', $tailleTxt);
		$length = $this->GetStringWidth( $texte );
		$this->MultiCell($length + 10, 4, $texte);
	}


	// Affiche en haut, a droite le libelle
	// (FACTURE, DEVIS, Bon de commande, etc...)
	// et son numero
	// La taille de la fonte est auto-adaptee au cadre
	function fact_dev( $libelle, $num, $indEuros = true )
	{
		$r1  = $this->w - 100;
		$r2  = $r1 + 98;
		$y1  = 6;
		$y2  = $y1 + 2;
		$mid = ($r1 + $r2 ) / 2;

		if ($indEuros == true)
			$texte  = $libelle . "en " . EURO . ", N° " . $num;
		else $texte = $libelle . " N° " . $num;
		$szfont = 12;
		$loop   = 0;

		while ( $loop == 0 )
		{
		$this->SetFont( "Arial", "B", $szfont );
		$sz = $this->GetStringWidth( $texte );
		if ( ($r1+$sz) > $r2 )
			$szfont --;
		else
			$loop ++;
		}

		$this->SetLineWidth(0.1);
		$this->SetFillColor(192);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
		$this->SetXY( $r1+1, $y1+2);
		$this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
	}

	// Genere automatiquement un numero de devis
	function addDevis( $numdev )
	{
		$string = sprintf("DEV%04d",$numdev);
		$this->fact_dev( "Devis", $string );
	}

	// Genere automatiquement un numero de facture
	function addFacture( $numfact )
	{
		$string = sprintf("FA%04d",$numfact);
		$this->fact_dev( "Facture", $string );
	}

	// Affiche un cadre avec la date de la facture / devis
	// (en haut, a droite)
	function addDate( $date )
	{
		$r1  = $this->w - 81;
		$r2  = $r1 + 30;
		$y1  = 17;
		$y2  = $y1 ;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell(10,5, "DATE", 0, 0, "C");
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
		$this->SetFont( "Arial", "", 10);
		$this->Cell(10,5,$date, 0,0, "C");
	}

	// Affiche un cadre avec les references du client
	// (en haut, a droite)
	function addClient( $ref )
	{
		$r1  = $this->w - 51;
		$r2  = $r1 + 49;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell(10,5, "BENEFICIAIRE", 0, 0, "C");
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
		$this->SetFont( "Arial", "", 10);
		if (strlen($ref) >= 20) {
			$words = explode(' ',$ref);
			$ref = '';
			foreach($words as $word) {
				if ($word != 'de' && $word != 'du' && $word != 'le' && $word != 'la' && $word != 'et' && $word != 'd\'')
					$ref .= strtoupper(substr($word,0,1)).'.';
			}
		}
		$this->Cell(10,5,$ref, 0,0, "C");
	}

	// Affiche un cadre avec un numero de page
	// (en haut, a droite)
	function addPageNumber( $page )
	{
		$r1  = $this->w - 100;
		$r2  = $r1 + 19;
		$y1  = 17;
		$y2  = $y1;
		$mid = $y1 + ($y2 / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell(10,5, "PAGE", 0, 0, "C");
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
		$this->SetFont( "Arial", "", 10);
		$this->Cell(10,5,$page, 0,0, "C");
	}

	// Affiche l'adresse du client
	// (en haut, a droite)
	function addClientAdresse( $adresse )
	{
		$r1     = $this->w - 80;
		$r2     = $r1 + 68;
		$y1     = 40;
		$this->SetXY( $r1, $y1);
		$this->SetFont( "Arial", "B", 12);
		$this->MultiCell( 60, 4, $adresse);
	}

	// Affiche un cadre avec le règlement (chèque, etc...)
	// (en haut, a gauche)
	function addReglement( $mode )
	{
		$r1  = 10;
		$r2  = $r1 + 60;
		$y1  = 80;
		$y2  = $y1+10;
		$mid = $y1 + (($y2-$y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell(10,4, "MODE DE REGLEMENT", 0, 0, "C");
		$this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
		$this->SetFont( "Arial", "", 10);
		$this->Cell(10,5,$mode, 0,0, "C");
	}

	// Affiche un cadre avec la date d'echeance
	// (en haut, au centre)
	function addEcheance( $date )
	{
		$r1  = 80;
		$r2  = $r1 + 40;
		$y1  = 80;
		$y2  = $y1+10;
		$mid = $y1 + (($y2-$y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell(10,4, "DATE D'ECHEANCE", 0, 0, "C");
		$this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
		$this->SetFont( "Arial", "", 10);
		$this->Cell(10,5,$date, 0,0, "C");
	}

	// Affiche un cadre avec le numero de la TVA
	// (en haut, au droite)
	function addNumTVA($tva)
	{
		$r1  = $this->w - 60;
		$r2  = $r1 + 50;
		$y1  = 74;
		$y2  = $y1+10;
		$mid = $y1 + (($y2-$y1) / 2);
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2, 'D');
		$this->Line( $r1, $mid, $r2, $mid);
		$this->SetFont( "Arial", "B", 9);
		$this->SetXY( $r1 + 6 , $y1+1 );
		$this->Cell(40, 4, "TVA Intracommunautaire", '', '', "C");
		$this->SetFont( "Arial", "", 9);
		$this->SetXY( $r1 + 6 , $y1+5 );
		$this->Cell(40, 5, $tva, '', '', "C");
	}

	// Affiche une ligne avec des reference
	// (en haut, a gauche)
	function addReference( $ref, $refDetail)
	{
		$lengthRef     = $this->GetStringWidth( "Évènement : ".$ref );
		$lengthDetails = $this->GetStringWidth( $refDetail );
		$x  = 10;
		$y  = 88;
		$this->SetXY( $x , $y );
		$this->SetFont( "Arial", "", 10);
		$this->Cell( $lengthRef, 4, "Évènement : ".$ref);
		$this->SetXY( $x , $y + 6 );
		$this->SetFont( "Arial", "B", 10);
		$this->Cell( $lengthDetails, 4, $refDetail);

	}

	// trace le cadre des colonnes du devis/facture
	function addCols( $tableau, $topTableau = 100, $botTableau = 50 ) {
		global $colonnes;
		$r1  = 10;
		$r2  = $this->w - ($r1 * 2) ;
		$y1  = $topTableau;
		$y2  = $this->h - $botTableau - $y1;
		$this->SetXY( $r1, $y1 );
		$this->SetTextColor(0,0,0);
		$this->Rect( $r1, $y1, $r2, $y2, "D");
		$this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
		$colX = $r1;
		$colonnes = $tableau;
		while ( list( $lib, $pos ) = each ($tableau) )
		{
			$this->SetXY( $colX, $y1+2 );
			$this->Cell( $pos, 1, $lib, 0, 0, "C");
			$colX += $pos;
			$this->Line( $colX, $y1, $colX, $y1+$y2);
		}
	}

	// mémorise le format (gauche, centre, droite) d'une colonne
	function addLineFormat( $tab )
	{
		global $format, $colonnes;

		while ( list( $lib, $pos ) = each ($colonnes) )
		{
			if ( isset( $tab["$lib"] ) )
				$format[ $lib ] = $tab["$lib"];
		}
	}

	function lineVert( $tab )
	{
		global $colonnes;

		reset( $colonnes );
		$maxSize=0;
		while ( list( $lib, $pos ) = each ($colonnes) )
		{
			$texte = $tab[ $lib ];
			$longCell  = $pos -2;
			$size = $this->sizeOfText( $texte, $longCell );
			if ($size > $maxSize)
				$maxSize = $size;
		}
		return $maxSize;
	}

	// Affiche chaque "ligne" d'un devis / facture
	/*    $ligne = array( "REFERENCE"    => $prod["ref"],
						"DESIGNATION"  => $libelle,
						"QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
						"P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
						"MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
						"TVA"          => $prod["tva"] );
	*/
	function addLine( $ligne, $tab, $sizeTxt = 8, $color = false )
	{
		global $colonnes, $format;

		$ordonnee     = 10;
		$maxSize      = $ligne;

		reset( $colonnes );
		while ( list( $lib, $pos ) = each ($colonnes) )
		{
			$longCell  = $pos -2;
			$texte     = $tab[ $lib ];
			if ($lib == 'REF.')
				$this->SetFont( "Arial", "", $sizeTxt -1 );
			else $this->SetFont( "Arial", "", $sizeTxt );
			if ($color != false)
				$this->SetTextColor($color['R'], $color['G'], $color['B']);
			else $this->SetTextColor(0, 0, 0);
			$length    = $this->GetStringWidth( $texte );
			$tailleTexte = $this->sizeOfText( $texte, $length );
			$formText  = $format[ $lib ];
			$this->SetXY( $ordonnee, $ligne-1);
			$this->MultiCell( $longCell, 4 , $texte, 0, $formText);
			if ( $maxSize < ($this->GetY()  ) )
				$maxSize = $this->GetY() ;
			$ordonnee += $pos;
		}
		return ( $maxSize - $ligne );
	}

	// Ajoute une remarque (en bas, a gauche)
	function addRemarque($remarque, $topFromBot = 45.5)
	{
		$this->SetFont( "Arial", "", 10);
		$length = $this->GetStringWidth( "Remarque : " . $remarque );
		$r1  = 10;
		$r2  = $r1 + $length;
		$y1  = $this->h - $topFromBot;
		$y2  = $y1+5;
		$this->SetXY( $r1 , $y1 );
		$this->Cell($length,4, "Remarque : " . $remarque);
	}

	// trace le cadre des TVA
	function addCadreTVAs( $topFromBot = 40 )
	{
		$this->SetFont( "Arial", "B", 8);
		$r1  = 10;
		$r2  = $r1 + 120;
		$y1  = $this->h - $topFromBot;
		$y2  = $y1+20;
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D'); // tracage du rectangle aux coins arrondis
		$this->Line( $r1, $y1+4, $r2, $y1+4);	 // ligne de séparation horizontale des titres
		$this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BASES HT
		$this->Line( $r1+27, $y1, $r1+27, $y2);  // avant COEF
		$this->Line( $r1+40, $y1, $r1+40, $y2);  // avant REMISE
		$this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
		$this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
		$this->SetXY( $r1+5, $y1);
		$this->Cell(10,4, "BASE HT jour");
		$this->SetX( $r1+29);
		$this->Cell(10,4, "COEF.");
		$this->SetX( $r1+42 );
		$this->Cell(10,4, "REMISABLE");
		$this->SetX( $r1+70 );
		$this->Cell(10,4, "REMISE");
		$this->SetX( $r1+100 );
		$this->Cell(10,4, "TOTAUX");
		$this->SetFont( "Arial", "B", 6);
		$this->SetXY( $r1+93, $y2 - 9 );
		$this->Cell(6,0, "HT  :");
		$this->SetXY( $r1+93, $y2 - 4 );
		$this->Cell(6,0, "TVA");
	}

	// trace le cadre des totaux
	function addCadreTotaux($topFromBot = 40, $isFacture = false)
	{
		$r1  = $this->w - 50;
		$r2  = $r1 + 40;
		$y1  = $this->h - $topFromBot;
		$y2  = $y1+20;
		$this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
		$this->Line( $r1+20,  $y1, $r1+20, $y2); // ligne avant "EUROS"
		$this->Line( $r1+20, $y1+4, $r2, $y1+4); // ligne dessous "EUROS"
		$this->SetFont( "Arial", "B", 8);
		$this->SetXY( $r1+22, $y1 );
		$this->Cell(15,4, "EUROS", 0, 0, "C");
		$this->SetFont( "Arial", "", 8);
		$this->SetFont( "Arial", "B", 6);
		$this->SetXY( $r1, $y1+5 );
		$this->Cell(20,4, "TOTAL TTC", 0, 0, "C");
		$this->SetXY( $r1, $y1+10 );
		$this->Cell(20,4, "NET A PAYER", 0, 0, "C");
		if (!$isFacture) {											// Si facture, pas besoin d'afficher la val. de remplacement
			$this->SetXY( $r1, $y1+15 );
			$this->Cell(20,4, "VAL. REMPL.", 0, 0, "C");
		}
	}


	// remplit les cadres TVA / Totaux et la remarque
	function addTVAs( $params, $tab_tva, $listProdHT, $exclRemiseT, $exclRemiseE, $valRemp, $nbjours, $salaires = false, $topFromBot = 40) {
		$this->SetFont('Arial','',8);
		reset ($listProdHT);
		$px = array();
		foreach( $listProdHT as $prod) {
			$tva_code = $prod["tva"];
			@$px[$tva_code] += $prod["qte"] * $prod["px_unit"];
		}

		$totalHT  = 0; $totalTTC = 0; $totalTVA = 0;
		$y = $this->h - $topFromBot + 8;
		reset ( $px );
		natsort( $px );
		foreach( $px as $code_tva => $grpTVAtotalHT ) {		// Pour chaque code_tva différent (inutile pour le robert, mais concervé tt de même)
			$tva = $tab_tva[$code_tva];
			$this->SetXY(17, $y);
			$this->Cell( 19,4, sprintf("%0.2F", $grpTVAtotalHT- (float)$exclRemiseE - (float)$exclRemiseT),'', '','R' );								// Affiche le total HT BASE JOUR

			$this->SetFont('Arial','B',8);
			$this->SetXY( 40, $y );
			$this->Cell( 10,4, coef($nbjours) );															// Affiche le coeficient
			$this->SetFont('Arial','',6);
			($nbjours > 1) ? $pluriel = 's' : $pluriel = '';
			$this->SetXY( 38, $y+3 );
			$this->Cell( 10,4, '('.$nbjours.' jour'.$pluriel.')' );											// Affiche le nombre de jours

			$this->SetFont('Arial','',8);
			if ( $params["RemiseGlobale"] == 1 ) {
				$rabais = 0;
				if ( $params["remise_tva"] == $code_tva ) {
					$this->SetXY( 78, $y );
					$baseJour = $grpTVAtotalHT - (float)$exclRemiseE - (float)$exclRemiseT;
					$remisable = $baseJour * coef($nbjours) ;
					if ( $params["remise_percent"] > 0 ) {
						$remiseP = (float)$params["remise_percent"] / 100;
						$rabais  = $remisable * $remiseP;
						$l_remise = number_format($rabais, 2);
						$this->Cell( 16.5,4, $params["remise_percent"]."%", '', '', 'C' );	// Affiche le % de la remise
						$this->SetXY( 78, $y+4 );
						$this->SetFont('Arial','',6);
						$this->Cell( 16.5,4, '(soit -'.$l_remise.' EUR)', '', '', 'C' );	// Affiche le montant de la remise
					}
					else
						$this->Cell( 16.5,4, "Sans remise", '', '', 'R' );
				}
			}
			$totalHT = $remisable - $rabais + (float)$exclRemiseE * coef($nbjours) + (float)$exclRemiseT ;
			$totalTTC += $totalHT * ( 1 + $tva/100 );
			$tmp_tva = $totalHT * $tva/100;
			$totalTVA += $tmp_tva;
			$this->SetFont('Arial','',8);
			$this->SetXY(11, $y);
			$this->Cell( 5,4, $code_tva);
			$this->SetXY(103, $y+8);
			$this->SetFont('Arial','',6);
			$this->Cell( 10,4, '('.sprintf("%0.2F",$tva).' %)' ,'', '', 'R');								// Affiche le % de la TVA
			$y+=4;
		}
		$this->SetFont('Arial','',8);
		$this->SetXY(57, $y-4);
		$this->Cell(15,4, sprintf("%0.2F", @$remisable), '', '', 'R');										// Affiche le remisable
		$this->SetXY(114, $this->h - $topFromBot + 9 );
		$this->Cell(15,4, sprintf("%0.2F", $totalHT), '', '', 'R' );										// Affiche le montant H.T.
		$this->SetXY(114, $this->h - $topFromBot + 14 );
		$this->Cell(15,4, sprintf("%0.2F", $totalTVA), '', '', 'R' );										// Affiche le montant de TVA

		$params["totalHT"] = $totalHT;
		$params["TVA"]	   = $totalTVA;
		$accompteTTC = 0;
		if ( @$params["AccompteExige"] == 1 )
		{
			if ( $params["accompte"] > 0 )
			{
				$accompteTTC=sprintf ("%.2F", $params["accompte"]);
				if ( strlen ($params["Remarque"]) == 0 )
					$this->addRemarque( "Accompte de $accompteTTC Euros exigé à la commande.");
				else
					$this->addRemarque( $params["Remarque"] );
			}
			else if ( $params["accompte_percent"] > 0 )
			{
				$percent = $params["accompte_percent"];
				if ( $percent > 1 )
					$percent /= 100;
				$accompteTTC=sprintf("%.2F", $totalTTC * $percent);
				$percent100 = $percent * 100;
				if ( strlen ($params["Remarque"]) == 0 )
					$this->addRemarque( "Accompte de $percent100 % (soit $accompteTTC Euros) exigé à la commande." );
				else
					$this->addRemarque( $params["Remarque"], $topFromBot );
			}
			else
				$this->addRemarque( "Drôle d'acompte !!! " . $params["Remarque"]);
		}
		else
		{
			if ( strlen ($params["Remarque"]) > 0 )
				$this->addRemarque( $params["Remarque"], $topFromBot + 8 );
		}
		$re  = $this->w - 30;
		$y1  = $this->h - $topFromBot ;
		$this->SetFont( "Arial", "", 8);
		$this->SetXY( $re, $y1+5 );
		$this->Cell( 17,4, sprintf("%0.2F", $totalTTC), '', '', 'R');
		$this->SetFont( "Arial", "B", 10);
		$this->SetXY( $re, $y1+9.5);
		$this->Cell( 17,4, sprintf("%0.2F", $totalTTC - $accompteTTC), '', '', 'R');
		$this->SetFont( "Arial", "", 8);
		if ($valRemp != false) {
			$this->SetXY( $re, $y1+14.8 );
			$this->Cell( 17,4, sprintf("%0.2F", $valRemp), '', '', 'R');
		}
		if ($salaires != false) {
			$this->SetFont( "Arial", "B", 10);
			$this->SetXY( $re, $y1+21 );
			$this->Cell(20,6, "A cette somme, vous devrez ajouter ".number_format($salaires, 2)." Euros pour l'emploi des techniciens.", 0, 0, "R");
			$this->SetXY( $re, $y1+27 );
			$this->Cell(20,6, "Le coût total de la prestation sera donc de ".number_format($totalTTC+$salaires, 2, '.', '')." Euros", 0, 0, "R");
		}
		return number_format($totalTTC, 2);
	}

	// Permet de rajouter un commentaire (Devis temporaire, REGLE, DUPLICATA, ...)
	// en sous-impression
	// ATTENTION: APPELER CETTE FONCTION EN PREMIER
	function temporaire( $texte )
	{
		$this->SetFont('Arial','B',50);
		$this->SetTextColor(203,203,203);
		$this->Rotate(45,55,190);
		$this->Text(55,190,$texte);
		$this->Rotate(0);
		$this->SetTextColor(0,0,0);
	}

}
?>