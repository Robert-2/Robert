<?php
/*
 *
    Robert est un logiciel libre; vous pouvez le redistribuer et/ou
    le modifier sous les termes de la Licence Publique Générale GNU Affero
    comme publiée par la Free Software Foundation;
    version 3.0.

    Cette WebApp est distribuée dans l'espoir qu'elle soit utile,
    mais SANS AUCUNE GARANTIE; sans même la garantie implicite de
    COMMERCIALISATION ou D'ADAPTATION A UN USAGE PARTICULIER.
    Voir la Licence Publique Générale GNU Affero pour plus de détails.

    Vous devriez avoir reçu une copie de la Licence Publique Générale
    GNU Affero avec les sources du logiciel; si ce n'est pas le cas,
    rendez-vous à http://www.gnu.org/licenses/agpl.txt (en Anglais)
 *
 */
include_once('infos_boite.php');
require_once('date_fr.php');
require_once('matos_tri_sousCat.php');

class SortiePDF {
    // Infos du PDF
    private $datePDF;
    private $pathPDF;
    private $namePDF;

    // Infos du plan
    private $idPlan;
    private $titrePlan;
    private $datePlanS;
    private $datePlanE;
    private $lieuPlan;
    private $nomCreateur;
    private $idCreateur;
    private $createurLine;
    private $infoCreateur;
    private $telCreateur;
    private $benefPlan;
    private $tekosPlan;
    private $matosPlan;
    private $matosBySousCat;
    private $matosForBD;
    private $benefInfos;
    private $totalPlan;
    private $nbjours;

    // spécial boucle Matos
    private $retour;
    private $list_sousCat;

    // Infos spécifiques DEVIS
    private $numDevis;
    private $contratTXT;
    private $dateFinDevis;

    public function __construct ( $idPlan ) {
        if ( !isset($idPlan) ) throw new Exception ('Merci de spécifier l\'ID du plan...');
        $this->idPlan = $idPlan;
        $planInfo = new Plan();
        $planInfo->load(Plan::PLAN_cID, $idPlan);
        $this->titrePlan   = $planInfo->getPlanTitre();
        $this->datePlanS   = datefr(date('l d F Y', $planInfo->getPlanStartDate()));
        $this->datePlanE   = datefr(date('l d F Y', $planInfo->getPlanEndDate()));
        $this->lieuPlan       = $planInfo->getPlanLieu();
        $this->idCreateur  = $planInfo->getPlanCreateur('id');
        $this->nomCreateur = $planInfo->getPlanCreateur();
        $this->benefPlan   = $planInfo->getPlanBenef();
        $this->tekosPlan   = $planInfo->getPlanTekos();
        $this->matosPlan   = $planInfo->getPlanMatos();
        $this->nbjours       = $planInfo->getNBSousPlans();

        $this->pathPDF = '../datas/PLANS_DATAS/'.$idPlan.'/';                    // relatif au dossier 'fct/', (le dossier par défaut des instanciations)
        $this->datePDF = date('Ymd');
        $this->dateTXT = date('d/m/Y');

        try {
            // Infos du créateur
            $this->infoCreateur = new Infos(TABLE_USERS);
            $this->infoCreateur->loadInfos('id', addslashes($this->idCreateur));

            //////////////////////////////////////////////////////////////////////////////////
            $nomCreateur  = $this->infoCreateur->getInfo('prenom');
            $mailCreateur = $this->infoCreateur->getInfo('email');
            $idTekosCreateur = $this->infoCreateur->getInfo('idTekos');
            if ($idTekosCreateur != 0) {
                $infoTekosCreateur = new Infos(TABLE_TEKOS);
                $infoTekosCreateur->loadInfos('id', $idTekosCreateur);
                $this->telCreateur = $infoTekosCreateur->getInfo('tel');
                unset($infoTekosCreateur);
                $this->createurLine = $nomCreateur.", ".$this->telCreateur." (".$mailCreateur.")";
            }
            else $this->createurLine = $nomCreateur." (".$mailCreateur.")";

            // Infos du bénéficiaire
            $benefObj = new Structure();
            $benefObj->loadFromBD('label', addslashes($this->benefPlan));
            $this->benefInfos = $benefObj->getInfoStruct();

            // Infos du Matos
            $l = new Liste();
            $list_Matos   = $l->getListe( TABLE_MATOS, 'id, ref, label, externe, tarifLoc, valRemp, categorie, sousCateg');
            $list_sousCat = $l->getListe ( TABLE_MATOS_CATEG, '*', 'ordre', 'ASC' );
            $this->list_sousCat = simplifySousCatArray($list_sousCat);

            foreach($list_Matos as $matos) {
                if ( isset($this->matosPlan[$matos['id']]) ) {
                    $this->retour['matos'][$matos['id']]['qte'] = $this->matosPlan[$matos['id']];
                    $this->retour['matos'][$matos['id']]['ref'] = $matos['ref'];
                    $this->retour['matos'][$matos['id']]['label'] = $matos['label'];
                    $this->retour['matos'][$matos['id']]['PU'] = $matos['tarifLoc'];
                    $this->retour['matos'][$matos['id']]['valRemp'] = $matos['valRemp'];
                    $this->retour['matos'][$matos['id']]['cat'] = $matos['categorie'];
                    $this->retour['matos'][$matos['id']]['ext'] = $matos['externe'];
                    $this->retour['matos'][$matos['id']]['sousCateg'] = $matos['sousCateg'];
                    $this->matosForBD[$matos['id']] = $this->matosPlan[$matos['id']];
                }
            }
            $this->matosBySousCat = creerSousCatArray ( $this->retour['matos'] );

            $this->dateFinDevis = date('d/m/Y', $planInfo->getPlanEndDate());
//            $dateFinDevis = '01/' . date('m/Y', strtotime("+6 month"));
        }
        catch (Exception $e) { throw new Exception ('Création PDF : Impossible de récupérer une info... '.$e->getMessage()); }
    }

    // CRÉATION D'UN DEVIS EN PDF, PUIS AJOUT EN BDD (table 'devis')
    public function createDevis ( $salaires, $remise = 0, $contratTxt = false ) {
        $this->numDevis = Devis::getLastNumDevis($this->idPlan) + 1;        // numéro du devis à créer
        try { $this->createDossierContenu ('devis'); }                        // check si les dossiers DATA existe, sinon on le crée
        catch (Exception $e) { throw new Exception ('Erreur création du dossier de contenu : ' . $e->getMessage()); return; }

        $nomBoite = preg_replace('/ /', '_', NOM_BOITE);
        $this->namePDF = 'Devis_'.$nomBoite.'_'.$this->datePDF.'-'.$this->numDevis.'_'.$this->nomCreateur.'_'.$this->benefPlan.'.pdf' ;
        $this->pathPDF .= 'devis/'.$this->namePDF;

        $this->contratTXT = "Ce devis est valable jusqu'au " . $this->dateFinDevis . ", et a valeur de contrat, dont les conditions sont les suivantes :\n\n";
        if ($contratTxt == false)
            $this->contratTXT .= include('../fct/contrat_location.php');
        else $this->contratTXT .= $contratTxt;

        $this->contratTXT .= "\n\nPour tout renseignement complémentaire, n'hésitez pas à nous contacter.\n\n
                                                                        Signature, précédée de la mention \"bon pour accord\" :";

        $pdf = new PDF_Devisfacture( 'P', 'mm', 'A4' );
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $logoSize = getimagesize('../config/logo.jpg');
        $pdf->addLogo('../config/logo.jpg', $logoSize[0]/5, $logoSize[1]/5, 'JPG');
        $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n"
                        .CP_BOITE ." ". VILLE_BOITE ."\n"
                        ."SIRET : " . SIRET_BOITE . "\n\n"
                        ."Devis Géré par ".$this->infoCreateur->getInfo('prenom')." ".$this->infoCreateur->getInfo('nom')."\n"
                        ."Tél.  : " . $this->telCreateur . "\n"
                        ."Email : " . $this->infoCreateur->getInfo('email') );
//        $pdf->addCreateur( "Devis géré par : " . $this->createurLine, 8 );
        $pdf->fact_dev( "Devis ", "$this->datePDF-$this->numDevis" );
        $pdf->addDate( $this->dateTXT );
        $pdf->addClient( $this->benefInfos['label'] );
        $pdf->addPageNumber("1");
        $pdf->addClientAdresse( $this->benefInfos['type']." ".$this->benefInfos['NomRS']."\n\n"
                              . $this->benefInfos['adresse']."\n\n"
                              . $this->benefInfos['codePostal']." ".strtoupper($this->benefInfos['ville'])."\n");
        $pdf->addNumTVA( N_TVA_BOITE );
        $pdf->addReference($this->titrePlan, "du $this->datePlanS au $this->datePlanE, à $this->lieuPlan");

    /// CADRE des SOUS TOTAUX par catégorie de matos
        $cols=array("CATEGORIE"       => 100,
                    "QUANTITÉ" => 40,
                    "TOTAL HT"     => 40 );
        $pdf->addCols( $cols, 100, 169 );
        $cols=array("CATEGORIE"       => "L",
                    "QUANTITÉ" => "C",
                    "TOTAL HT"     => "R" );
        $pdf->addLineFormat( $cols );

        $y = 108;
        $qteCateg['son'] = 0; $qteCateg['lumiere'] = 0; $qteCateg['structure'] = 0; $qteCateg['transport'] = 0;
        $ssTotalCateg['son'] = 0; $ssTotalCateg['lumiere'] = 0; $ssTotalCateg['structure'] = 0; $ssTotalCateg['transport'] = 0;
        $listMatosHT = array(); $totalValRemp = 0; $exclRemiseT = 0; $exclRemiseE = 0;
        foreach($this->retour['matos'] as $infoMatos) {
            $qteMatos = (int)$infoMatos['qte'];
            $PUmatos  = (float)$infoMatos['PU'];
            $listMatosHT[] = array("px_unit" => $PUmatos, "qte" => $qteMatos, "tva" => 1);
            $qteCateg[$infoMatos['cat']]     += $infoMatos['qte'];
            $ssTotalCateg[$infoMatos['cat']] += $infoMatos['PU'] * $infoMatos['qte'];
            $totalValRemp += $infoMatos['valRemp'];
            $categories[$infoMatos['cat']] = array('label' => $infoMatos['cat'], 'qte' => $qteCateg[$infoMatos['cat']], 'sstotal' => $ssTotalCateg[$infoMatos['cat']]);
            if ($infoMatos['cat'] == 'transport')                                                                                            // ICI
                $exclRemiseT += $infoMatos['PU'] * $infoMatos['qte'];                                                                                                    // ICI
            if ($infoMatos['ext'] == '1')                                                                                            // ICI
                $exclRemiseE += $infoMatos['PU'] * $infoMatos['qte'];                                                                                                    // ICI
        }
        foreach($categories as $categ) {
            if ($categ['qte'] != 0) {
                $line = array(  "CATEGORIE"       => "       " . strtoupper($categ['label']),
                                "QUANTITÉ" => $categ['qte'],
                                "TOTAL HT"     => number_format($categ['sstotal'], 2));
                $size = $pdf->addLine( $y, $line, 12 );
                $y   += $size + 2;
            }
        }

    /// CADRE des TVA et des TOTAUX
        $pdf->addCadreTVAs( 158 );
        $tab_tva = array("1" => (float)TVA_VAL * 100, "2" =>  5.5 );
        $params  = array("RemiseGlobale" => 1,
                            "remise_tva"     => 1,                    // {la remise s'applique sur ce code TVA}
                            "remise"         => 0,                    // {montant de la remise}
                            "remise_percent" => (float)$remise,        // {pourcentage de remise sur ce montant de TVA}
                        "Remarque" => "Vous trouverez le détail du matériel sur les pages suivantes." );

        $this->totalPlan = $pdf->addTVAs( $params, $tab_tva, $listMatosHT, $exclRemiseT, $exclRemiseE, $totalValRemp, $this->nbjours, $salaires, 158);                                                            // ICI
        $this->totalPlan = preg_replace('/,/', '', $this->totalPlan);

        $pdf->addCadreTotaux( 158, false );

    /// AFFICHAGE DU CONTRAT
        $pdf->SetXY( 10, 174 );
        $pdf->SetFont('Arial', '', 10);
        $this->contratTXT = strtr($this->contratTXT, '€', 'EUR');
        $pdf->MultiCell(0, 4, $this->contratTXT);

    /// Tableau du détail du matériel
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
//        $pdf->addCreateur( "Devis géré par : " . $this->createurLine, 8 );
        $pdf->fact_dev( "Détail devis No ", "$this->datePDF-$this->numDevis", false );
        $pdf->addDate( $this->dateTXT );
        $pdf->addClient( $this->benefInfos['NomRS'] );
        $pdf->addPageNumber('2');

        $cols=array("REF."           => 30,
                    "DESIGNATION"  => 76,
                    "QTE"           => 13,
                    "P.U. HT"      => 23,
                    "Val. Remp."   => 23,
                    "TOTAL HT"     => 25 );
        $pdf->addCols( $cols, 55, 5 );
        $cols=array("REF."           => "L",
                    "DESIGNATION"  => "L",
                    "QTE"           => "C",
                    "P.U. HT"      => "R",
                    "Val. Remp."   => "R",
                    "TOTAL HT"     => "R" );
        $pdf->addLineFormat( $cols );

        $HtableauMax = 245; $noPage = 2;
        $y = 62.8;
        $infoMatos = new Infos(TABLE_MATOS);
        foreach( $this->matosBySousCat as $id => $matos ){
            if ( empty($matos) ) continue ;
            if ($y <= $HtableauMax) {
                $pdf->SetDrawColor(200, 200, 200);
                $pdf->Line(10, $y-2, 200, $y-2);
                $line = array(  "REF."           => " ",
                                "DESIGNATION"  => strtoupper($this->list_sousCat[$id]['label']),
                                "QTE"           => " ",
                                "P.U. HT"      => " ",
                                "Val. Remp."   => " ",
                                "TOTAL HT"     => " " );
                $couleur = array('R' => 128, 'G' => 128, 'B' => 128);
                $size = $pdf->addLine( $y, $line, 8, $couleur );
                $y   += $size + 2;
            } else {
                $noPage += 1;
                $pdf->AddPage();
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetTextColor(0,0,0);
                $pdf->SetMargins(5, 5, 5);
                $pdf->addSociete( NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
//                $pdf->addCreateur( "Devis géré par : " . $this->createurLine, 8 );
                $pdf->fact_dev( "Détail devis No ", "$this->datePDF-$this->numDevis" );
                $pdf->addDate( $this->dateTXT );
                $pdf->addClient( $this->benefInfos['NomRS'] );
                $pdf->addPageNumber((string)$noPage);
                $yTopNewPage = $pdf->GetY() + 14;
                $cols=array("REF."           => 30,
                            "DESIGNATION"  => 76,
                            "QTE"           => 13,
                            "P.U. HT"      => 23,
                            "Val. Remp."   => 23,
                            "TOTAL HT"     => 25 );
                $pdf->addCols( $cols, $yTopNewPage );
                $cols=array("REF."           => "L",
                            "DESIGNATION"  => "L",
                            "QTE"           => "C",
                            "P.U. HT"      => "R",
                            "Val. Remp."   => "R",
                            "TOTAL HT"     => "R" );
                $pdf->addLineFormat( $cols );
                $y = $yTopNewPage + 8;
                $line = array(  "REF."           => " ",
                                "DESIGNATION"  => strtoupper($this->list_sousCat[$id]['label']),
                                "QTE"           => " ",
                                "P.U. HT"      => " ",
                                "Val. Remp."   => " ",
                                "TOTAL HT"     => " " );
                $size = $pdf->addLine( $y, $line, 8, array('R' => 0, 'G' => 0, 'B' => 0));
                $y   += $size + 2;
            }

            foreach( $matos as $mat ){
                $PUmatos    = (float)$mat['PU'];
                $PUmatosAff = number_format($PUmatos, 2);
                $qteMatos    = (int)$mat['qte'];
                $ssTotalMatos = number_format($qteMatos * $PUmatos, 2) ;

                if ($y <= $HtableauMax) {
                    $line = array(  "REF."           => $mat['ref'],
                                    "DESIGNATION"  => $mat['label'],
                                    "QTE"           => $qteMatos,
                                    "P.U. HT"      => $PUmatosAff,
                                    "Val. Remp."   => number_format($mat['valRemp'] * $qteMatos, 2),
                                    "TOTAL HT"     => $ssTotalMatos );
                    $size = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
                else {
                    $noPage += 1;
                    $pdf->AddPage();
                    $pdf->SetDrawColor(0, 0, 0);
                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetMargins(5, 5, 5);
                    $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
//                    $pdf->addCreateur( "Devis géré par : " . $this->createurLine, 8 );
                    $pdf->fact_dev( "Détail devis No ", "$this->datePDF-$this->numDevis" );
                    $pdf->addDate( $this->dateTXT );
                    $pdf->addClient( $this->benefInfos['NomRS'] );
                    $pdf->addPageNumber((string)$noPage);
                    $yTopNewPage = $pdf->GetY() + 14;
                    $cols=array("REF."           => 30,
                                "DESIGNATION"  => 76,
                                "QTE"           => 13,
                                "P.U. HT"      => 23,
                                "Val. Remp."   => 23,
                                "TOTAL HT"     => 25 );
                    $pdf->addCols( $cols, $yTopNewPage );
                    $cols=array("REF."           => "L",
                                "DESIGNATION"  => "L",
                                "QTE"           => "C",
                                "P.U. HT"      => "R",
                                "Val. Remp."   => "R",
                                "TOTAL HT"     => "R" );
                    $pdf->addLineFormat( $cols );
                    $y = $yTopNewPage + 8;
                    $line = array(  "REF."           => $mat['ref'],
                                    "DESIGNATION"  => $mat['label'],
                                    "QTE"           => $qteMatos,
                                    "P.U. HT"      => $PUmatosAff,
                                    "Val. Remp."   => number_format($mat['valRemp'], 2),
                                    "TOTAL HT"     => $ssTotalMatos );
                    $size = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
            }
        }

        try {
            $pdf->Output($this->pathPDF, 'F');
        }
        catch (Exception $e) {
            throw new Exception('Impossible de créer le devis en PDF !\n\n'. $e->getMessage());
            return;
        }

        // AJOUT du devis en BDD
        try {
            $devisInfos = new Infos(TABLE_DEVIS);
            $devisInfos->addInfo( Devis::DEVIS_cNUM_DEVIS, $this->numDevis );
            $devisInfos->addInfo( Devis::DEVIS_cID_PLAN, $this->idPlan );
            $devisInfos->addInfo( Devis::DEVIS_cFICHIER, $this->namePDF );
            $devisInfos->addInfo( Devis::DEVIS_cTOTAL,   $this->totalPlan );
            $devisInfos->addInfo( Devis::DEVIS_cMATOS,   json_encode($this->matosForBD)  );
            $devisInfos->addInfo( Devis::DEVIS_cTEKOS,   implode(' ', $this->tekosPlan) );
            $devisInfos->save();
        }
        catch (Exception $e) { throw new Exception('Impossible de sauver le devis en BDD : ' . $e->getMessage()); return; }

    }

    // CREATION D'UNE FACTURE EN PDF (overwrite)
    public function createFacture ( $remise = 0) {
        try {
            $this->effaceOldFactures();                        // Si déjà une facture dedans, on la supprime pour la recréer.
            $this->createDossierContenu ('facture');        // check si le dossiers DATA existe, sinon on le crée
        }
        catch (Exception $e) { throw new Exception ('Erreur création du dossier de contenu : ' . $e->getMessage()); return; }

        $nomBoite = preg_replace('/ /', '_', NOM_BOITE);
        $this->namePDF = 'Facture_'.$nomBoite.'_'.$this->datePDF.'-'.$this->idPlan.'_'.$this->nomCreateur.'_'.$this->benefPlan.'.pdf' ;
        $this->pathPDF .= 'facture/'.$this->namePDF;

        $pdf = new PDF_Devisfacture( 'P', 'mm', 'A4' );
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $logoSize = getimagesize('../config/logo.jpg');
        $pdf->addLogo('../config/logo.jpg', $logoSize[0]/5, $logoSize[1]/5, 'JPG');
        $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n"
                        .CP_BOITE ." ". VILLE_BOITE ."\n\n"
                        ."SIRET : " . SIRET_BOITE . "\n\n"
                        ."Tél.  : " . TEL_BOITE . "\n"
                        ."Email : " . EMAIL_BOITE );
        $pdf->addCreateur( "Facture éditée par : " . $this->createurLine, 8 );
        $pdf->fact_dev( "Facture ", "$this->datePDF-$this->idPlan" );
        $pdf->addDate( $this->dateTXT );
        $pdf->addClient( $this->benefInfos['NomRS'] );
        $pdf->addPageNumber("1");
        $pdf->addClientAdresse( $this->benefInfos['type']." ".$this->benefInfos['NomRS']."\n\n"
                              . $this->benefInfos['adresse']."\n\n"
                              . $this->benefInfos['codePostal']." ".strtoupper($this->benefInfos['ville'])."\n");
        $pdf->addNumTVA( N_TVA_BOITE );
        $pdf->addReference($this->titrePlan, "du $this->datePlanS au $this->datePlanE, à $this->lieuPlan");

    /// CADRE des SOUS TOTAUX par catégorie de matos
        $cols=array("CATEGORIE"       => 100,
                    "QUANTITÉ" => 40,
                    "TOTAL HT"     => 40 );
        $pdf->addCols( $cols, 100, 169 );
        $cols=array("CATEGORIE"       => "L",
                    "QUANTITÉ" => "C",
                    "TOTAL HT"     => "R" );
        $pdf->addLineFormat( $cols );

        $y = 108;
        $qteCateg['son'] = 0; $qteCateg['lumiere'] = 0; $qteCateg['structure'] = 0; $qteCateg['transport'] = 0;
        $ssTotalCateg['son'] = 0; $ssTotalCateg['lumiere'] = 0; $ssTotalCateg['structure'] = 0; $ssTotalCateg['transport'] = 0;
        $totalFinal = array(); $totalValRemp = 0; $exclRemiseT = 0; $exclRemiseE = 0;
        foreach($this->retour['matos'] as $infoMatos) {
            $qteMatos = (int)$infoMatos['qte'];
            $PUmatos  = (float)$infoMatos['PU'];
            $totalFinal[] = array("px_unit" => $PUmatos, "qte" => $qteMatos, "tva" => 1);
            $qteCateg[$infoMatos['cat']]     += $infoMatos['qte'];
            $ssTotalCateg[$infoMatos['cat']] += $infoMatos['PU'] * $infoMatos['qte'];
            $totalValRemp += $infoMatos['valRemp'];
            $categories[$infoMatos['cat']] = array('label' => $infoMatos['cat'], 'qte' => $qteCateg[$infoMatos['cat']], 'sstotal' => $ssTotalCateg[$infoMatos['cat']]);
            if ($infoMatos['cat'] == 'transport')                                                                                            // ICI
                $exclRemiseT += $infoMatos['PU'] * $infoMatos['qte'];                                                                                                    // ICI
            if ($infoMatos['ext'] == '1')                                                                                            // ICI
                $exclRemiseE += $infoMatos['PU'] * $infoMatos['qte'];                                                                                                    // ICI
        }
        foreach($categories as $categ) {
            if ($categ['qte'] != 0) {
                $line = array(  "CATEGORIE"       => "       " . strtoupper($categ['label']),
                                "QUANTITÉ" => $categ['qte'],
                                "TOTAL HT"     => number_format($categ['sstotal'], 2));
                $size = $pdf->addLine( $y, $line, 12 );
                $y   += $size + 2;
            }
        }

    /// CADRE des TVA et des TOTAUX
        $pdf->addCadreTVAs( 150 );
        $tab_tva = array("1" => (float)TVA_VAL * 100, "2" =>  5.5 );
        $params  = array("RemiseGlobale" => 1,
                            "remise_tva"     => 1,                    // {la remise s'applique sur ce code TVA}
                            "remise"         => 0,                    // {montant de la remise}
                            "remise_percent" => (float)$remise,        // {pourcentage de remise sur ce montant de TVA}
                        "Remarque" => "Vous trouverez le détail du matériel sur les pages suivantes." );

        $this->totalPlan = $pdf->addTVAs( $params, $tab_tva, $totalFinal, $exclRemiseT, $exclRemiseE, false, $this->nbjours, false, 150);
        $pdf->addCadreTotaux( 150, true );

    /// Tableau du détail du matériel
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
        $pdf->addCreateur( "Facture éditée par : " . $this->createurLine, 8 );
        $pdf->fact_dev( "Détail facture No ", "$this->datePDF-$this->idPlan", false );
        $pdf->addDate( $this->dateTXT );
        $pdf->addClient( $this->benefInfos['NomRS'] );
        $pdf->addPageNumber('2');

        $cols=array("REF."           => 30,
                    "DESIGNATION"  => 76,
                    "QTE"           => 13,
                    "P.U. HT"      => 23,
                    "Val. Remp."   => 23,
                    "TOTAL HT"     => 25 );
        $pdf->addCols( $cols, 55, 5 );
        $cols=array("REF."           => "L",
                    "DESIGNATION"  => "L",
                    "QTE"           => "C",
                    "P.U. HT"      => "R",
                    "Val. Remp."   => "R",
                    "TOTAL HT"     => "R" );
        $pdf->addLineFormat( $cols );

        $HtableauMax = 245; $noPage = 2; $totalFinal = array();
        $y = 62.8;
        $infoMatos = new Infos(TABLE_MATOS);
        foreach( $this->matosBySousCat as $id => $matos ){
            if ( empty($matos) ) continue ;
            if ($y <= $HtableauMax) {
                $pdf->SetDrawColor(200, 200, 200);
                $pdf->Line(10, $y-2, 200, $y-2);
                $line = array(  "REF."           => " ",
                                "DESIGNATION"  => strtoupper($this->list_sousCat[$id]['label']),
                                "QTE"           => " ",
                                "P.U. HT"      => " ",
                                "Val. Remp."   => " ",
                                "TOTAL HT"     => " " );
                $couleur = array('R' => 128, 'G' => 128, 'B' => 128);
                $size = $pdf->addLine( $y, $line, 8, $couleur );
                $y   += $size + 2;
            }
            else {
                $noPage += 1;
                $pdf->AddPage();
                $pdf->SetMargins(5, 5, 5);
                $pdf->addSociete( NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
                $pdf->addCreateur( "Facture éditée par : " . $this->createurLine, 8 );
                $pdf->fact_dev( "Détail facture No ", "$this->datePDF-$this->idPlan" );
                $pdf->addDate( $this->dateTXT );
                $pdf->addClient( $this->benefInfos['NomRS'] );
                $pdf->addPageNumber((string)$noPage);
                $yTopNewPage = $pdf->GetY() + 14;
                $cols=array("REF."           => 30,
                            "DESIGNATION"  => 76,
                            "QTE"           => 13,
                            "P.U. HT"      => 23,
                            "Val. Remp."   => 23,
                            "TOTAL HT"     => 25 );
                $pdf->addCols( $cols, $yTopNewPage );
                $cols=array("REF."           => "L",
                            "DESIGNATION"  => "L",
                            "QTE"           => "C",
                            "P.U. HT"      => "R",
                            "Val. Remp."   => "R",
                            "TOTAL HT"     => "R" );
                $pdf->addLineFormat( $cols );
                $y = $yTopNewPage + 8;
                $line = array(  "REF."           => " ",
                                "DESIGNATION"  => strtoupper($this->list_sousCat[$id]['label']),
                                "QTE"           => " ",
                                "P.U. HT"      => " ",
                                "Val. Remp."   => " ",
                                "TOTAL HT"     => " " );
                $size = $pdf->addLine( $y, $line );
                $y   += $size + 2;
            }

            foreach( $matos as $mat ){
                $PUmatos    = (float)$mat['PU'];
                $PUmatosAff = number_format($PUmatos, 2);
                $qteMatos    = (int)$mat['qte'];
                $ssTotalMatos = number_format($qteMatos * $PUmatos, 2) ;

                if ($y <= $HtableauMax) {
                    $line = array(  "REF."           => $mat['ref'],
                                    "DESIGNATION"  => $mat['label'],
                                    "QTE"           => $qteMatos,
                                    "P.U. HT"      => $PUmatosAff,
                                    "Val. Remp."   => number_format($mat['valRemp'], 2),
                                    "TOTAL HT"     => $ssTotalMatos );
                    $size = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
                else {
                    $noPage += 1;
                    $pdf->AddPage();
                    $pdf->SetMargins(5, 5, 5);
                    $pdf->addSociete(NOM_BOITE, ADRESSE_BOITE . "\n". CP_BOITE ." ". VILLE_BOITE );
                    $pdf->addCreateur( "Facture éditée par : " . $this->createurLine, 8 );
                    $pdf->fact_dev( "Détail facture No ", "$this->datePDF-$this->idPlan" );
                    $pdf->addDate( $this->dateTXT );
                    $pdf->addClient( $this->benefInfos['NomRS'] );
                    $pdf->addPageNumber((string)$noPage);
                    $yTopNewPage = $pdf->GetY() + 14;
                    $cols=array("REF."           => 30,
                                "DESIGNATION"  => 76,
                                "QTE"           => 13,
                                "P.U. HT"      => 23,
                                "Val. Remp."   => 23,
                                "TOTAL HT"     => 25 );
                    $pdf->addCols( $cols, $yTopNewPage );
                    $cols=array("REF."           => "L",
                                "DESIGNATION"  => "L",
                                "QTE"           => "C",
                                "P.U. HT"      => "R",
                                "Val. Remp."   => "R",
                                "TOTAL HT"     => "R" );
                    $pdf->addLineFormat( $cols );
                    $y = $yTopNewPage + 8;
                    $line = array(  "REF."           => $mat['ref'],
                                    "DESIGNATION"  => $mat['label'],
                                    "QTE"           => $qteMatos,
                                    "P.U. HT"      => $PUmatosAff,
                                    "Val. Remp."   => number_format($mat['valRemp'], 2),
                                    "TOTAL HT"     => $ssTotalMatos );
                    $size = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
            }
        }

        try {
            $pdf->Output($this->pathPDF, 'F');
        }
        catch (Exception $e) {
            throw new Exception('Impossible de créer la facture PDF !\n\n'. $e->getMessage());
        }
    }

    // CHECK DE L'EXISTENCE DU DOSSIER DE CONTENU DU PLAN, sinon on le crée, ainsi que le sous dossier "devis"
    private function createDossierContenu ( $type = 'devis') {
        if ( ! is_dir($this->pathPDF) ) {
            if ( ! @mkdir($this->pathPDF) ) {
                throw new Exception( "Impossible de créer le dossier de contenu du plan No $this->idPlan !" );
            }
        }

        if ( ! is_dir($this->pathPDF.$type) ) {
            if ( ! @mkdir($this->pathPDF.$type) ) {
                throw new Exception( "Impossible de créer le sous dossier '$type/' du plan No $this->idPlan !" );
            }
        }
    }

    private function effaceOldFactures () {
        if ( is_dir($this->pathPDF) ) {
            if ( is_dir($this->pathPDF.'facture') ) {
                rrmdir($this->pathPDF.'facture');
            }
        }
    }
}
