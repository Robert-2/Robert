<?php
session_start();
require_once ('initInclude.php');
require_once ('global_config.php');        // OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

require_once ('matos_tri_sousCat.php');
require_once ('../fct/plans_actions.php');
require_once ( '../' . FOLDER_CONFIG . 'infos_boite.php');

if (empty($_GET['plan'])) {
    return;
}
$idPlan = $_GET['plan'];

function formatTelephone($tel){
    $length = strlen($tel);
    if ($length != 10) {
        return $tel;
    }

    $number ='';
    for ($i = 0 ; $i <= $length ; $i+=2) {
        $number .= substr($tel, $i , 2) . ' ';
    }
    return $number ;
}

try {
    $p = new Plan();
    $p->load( 'id', $idPlan );

    $l = new Liste();

    $list_Matos   = $l->getListe( TABLE_MATOS, 'id, ref, tarifLoc, externe, categorie, sousCateg, ownerExt');
    $list_sousCat = $l->getListe ( TABLE_MATOS_CATEG, '*', 'ordre', 'ASC' );
    array_push($list_sousCat, array ( 'id' => 999, 'label' => 'A louer' ));
    $list_sousCat = simplifySousCatArray($list_sousCat);

    $listeTeks = $l->getListe( TABLE_TEKOS );
    $listeTeks = $l->simplifyList('surnom');

    if (get_class($p) !== 'Plan') {
        return -1;
    }

    $retour['id']          = $p->getPlanID();
    $retour['titre']       = $p->getPlanTitre();
    $retour['dateDebut']   = datefr( date('j F Y', $p->getPlanStartDate()) );
    $retour['dateFin']     = datefr( date('j F Y', $p->getPlanEndDate()) );
    $retour['timeDebut']   = date('d/m/y', $p->getPlanStartDate());
    $retour['timeFin']     = date('d/m/y', $p->getPlanEndDate());
    $matos_plan            = $p->getPlanMatos();
    $retour['lieu']        = $p->getPlanLieu();
    $retour['benef']       = $p->getPlanBenef();
    $retour['nbSousPlans'] = $p->getNBSousPlans();
    if ($p->getPlanConfirm() != 0) {
        $retour['resa']    = 'reservation';
        $retour['resaTxt'] = 'confirmé, devis accepté';
    } else {
        $retour['resa']    = 'devis';
        $retour['resaTxt'] = 'en attente de confirmation';
    }

    // - récupération de la liste du matos, des qtés, et recalcul du sous-total
    foreach($list_Matos as $matos) {
        if ( isset($matos_plan[$matos['id']]) ) {
            $retour['matos'][$matos['id']]['qte'] = $matos_plan[$matos['id']];
            $retour['matos'][$matos['id']]['ref'] = $matos['ref'];
            $retour['matos'][$matos['id']]['prix'] = $matos['tarifLoc'] * $matos_plan[$matos['id']];
            $retour['matos'][$matos['id']]['externe'] = $matos['externe'];
            $retour['matos'][$matos['id']]['cat'] = $matos['categorie'];
            $retour['matos'][$matos['id']]['sousCateg'] = $matos['sousCateg'];
            $retour['matos'][$matos['id']]['ownerExt'] = $matos['ownerExt'];
        }
    }

    // - récupération des sous plans
    while ( $sp = $p->whileTestSousPlan() ) {
        $spID  = $p->getSousPlanId() ;
        $retour['sousPlans'][$spID]['id']        = $spID ;
        $retour['sousPlans'][$spID]['jour']        = datefr( date("l j F Y", $p->getSousPlanDate()) ) ;
        $retour['sousPlans'][$spID]['timestamp']= $p->getSousPlanDate() ;
        $retour['sousPlans'][$spID]['rem']        = $p->getSousPlanComment() ;
        $tekosIds = $p->getSousPlanTekos() ;
        $stringTekosList = '';
        foreach ($tekosIds as $id) {
            try {
                $tmpTekos = new Tekos($id);
                $stringTekosList .= $tmpTekos->getTekosInfos('surnom') . ', ';
            }
            catch (Exception $e) { continue; }
        }
        $retour['sousPlans'][$spID]['tekos'] = substr($stringTekosList, 0, -2);
    }

    // - Tri du tableau des sous plans par leur timestamp
    $tmp = [];
    foreach($retour['sousPlans'] as &$spTs) {
        $tmp[] = &$spTs["timestamp"];
    }
    array_multisort($tmp, $retour['sousPlans']);

    $retour['levelAuth'] = $_SESSION['user']->isLevelMod();

    $benef          = $p->getPlanBenef() ;
    $listeInterlock = $l->getListe(TABLE_INTERLOC, '*', 'label', 'ASC', 'nomStruct','=', $benef);
    $benefInfos     = $l->getListe(TABLE_STRUCT, '*', 'label', 'ASC', 'label', '=', $benef);
    if ($benefInfos) {
        $benefInfos = $benefInfos[0];
    } else {
        $benefInfos = $retour['benef'];
    }
    $p = null;
} catch (Exception $e) {
    echo ('Impossible d\'afficher la liste du plan : <BR />' . $e->getMessage() );
    return -1;
}

$titrePageBar = NOM_BOITE . " - Fiche : " . $retour['titre'];
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />

    <meta name="robots" content="noindex,nofollow" />

    <title><?php echo $titrePageBar ; ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="../gfx/favicon.ico" />

    <link type="text/css" href="../<?php echo chooseThemeFolder(); ?>/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
    <link type="text/css" href="../css/ossature.css" rel="stylesheet" />
    <link type="text/css" href="../css/ossature_print.css" rel="stylesheet" media="print"/>
    <link type="text/css" href="../<?php echo chooseThemeFolder(); ?>/colors.css" rel="stylesheet" />

    <script type="text/javascript" src="../js/jquery-1.7.min.js">// JQUERY CORE</script>
    <script type="text/javascript" src="../js/jquery-ui-1.8.17.custom.min.js">// JQUERY UI</script>
    <script type="text/javascript" src="../js/toolTip.js">// petit script pour afficher les tooltips</script>
    <style>
        .titreSection { margin : 5% 2% 0% 2%; }
        .container    { margin : 1% 0% 0% 2%;}
    </style>
</head>
<body>
    <div style="float:right;">
        <button class="gros marge30r margeTop5 printHide doigt" onClick="window.print()">IMPRIMER</button>
    </div>
    <div id='planInfos' class='ui-widget titreSection bordFin ui-corner-all'>
        <div class='ui-widget-header ui-corner-all gros pad5' style='color:white; background-color:orange;'>
            <?php  echo $retour['titre'];  ?>
            <span style='margin-left:10%' id='dateStart'> du  <?php  echo $retour['dateDebut'];  ?> </span>
            <span id='dateEnd'>, au  <?php  echo $retour['dateFin'];  ?> </span>
            <span id='lieu'> à <?php  echo $retour['lieu'];  ?> </span>
        </div>
        <div class='container'>
            <?php
            foreach ($retour['sousPlans'] as $sp) {
                $tekDiv = '<div class="inline marge30l" style="width:115px;">Techniciens : </div>';
                $tekosExp = explode(',', $sp['tekos']);
                foreach ($tekosExp as $tek) {
                    $tek = ltrim($tek);
                    $tekDiv .= '<div class="tekName inline marge30l"><b>' . $tek . '</b></div>';
                }

                if ($sp['rem'] != '') {
                    $remarque = '<br />
                        <div class="inline marge30l" style="width:115px;">Remarque :</div>
                        <div class="inline marge30l"><i>' . $sp['rem'] . '</i></div>';
                } else {
                    $remarque = '';
                }

                echo "<div class='gros marge15bot pad5 top'>"
                      . '<div class="pad5 ui-corner-all"><b>' . $sp['jour'] . '</b></div>' .
                        $tekDiv .
                        $remarque
                    ."</div>";
            }
            ?>
        </div>
    </div>

    <div id='planTekos' class='ui-widget titreSection bordFin ui-corner-all'>
        <div class='ui-widget-header ui-corner-all enorme pad5'>Infos Techniciens</div>
        <div class='container marge30l'>
        <?php
        $tekDiv = '';
        foreach ($retour['sousPlans'] as $sp) {
            $tekosExp = explode(',', $sp['tekos']);
            foreach( $tekosExp as $tek){
                $tek = ltrim($tek);
                $tel = $listeTeks[$tek]['tel'] ;

                if (!isset($listeTeks[$tek]['nbJoursTaf'])) {
                    $listeTeks[$tek]['nbJoursTaf'] = 0;
                }
                $listeTeks[$tek]['nbJoursTaf'] += 1;
                if (!@$listeTeks[$tek]['tel_print']) {
                    $tekDiv .= '
                    <div class="tekName padH5">
                        <div class="leftText inline" style="width:100px;"><b>' . $tek . '</b></div>
                        <div class="center inline" style="width:150px;" >' . formatTelephone($tel) . '</div>
                        <div class="inline" style="width:220px;">' .$listeTeks[$tek]['prenom'] . ' ' . $listeTeks[$tek]['nom'] . '</div>
                        <div class="inline" style="width:50px;">' . strtoupper($listeTeks[$tek]['categorie']) . '</div>
                    </div>';
                }

                $listeTeks[$tek]['tel_print'] = true ;
            }
        }

        echo "<div class='gros inline marge15bot pad5 ui-corner-all top'>"
              . $tekDiv . '</div>';
        ?>
        </div>
    </div>
    <div id='benefInfo' class='ui-widget titreSection bordFin ui-corner-all'>
        <div class='ui-widget-header ui-corner-all enorme pad5'>
            Bénéficiaire :
            <span class='mini'>
                <?php
                if (!is_array($benefInfos)) {
                    echo $retour['benef'] . ' <span class="printHide mini">'.
                    '<a href="../index.php?go=beneficiaires" style="color:#00a">Remplir les informations</a></span>';
                } else {
                    echo $benefInfos['type'] . ' ' . $benefInfos['NomRS'] .
                    ' (' .  formatTelephone( $benefInfos['tel'])  . ') ' .
                    $benefInfos['adresse'];
                }
                ?>
            </span>
        </div>
        <div class='container marge30l'>
        <?php
            if (!empty($listeInterlock)) {
                foreach ($listeInterlock as $inter) {
                    echo '
                    <div class="interlockItem gros padH5">
                        <div class="leftText inline" style="width:150px;">' . $inter['nomPrenom'] . '</div>
                        <div class="center inline" style="width:150px;" >' . formatTelephone($inter["tel"]) . '</div>
                        <div class="inline" style="width:100px;">' .$inter['poste'] . '</div>
                    </div>';
                }
            } else {
                echo '<span class="ui-state-disabled"><i>aucun interlocuteur spécifié pour cette structure bénéficiaire.</i></span><br /><br />';
            }
        ?>
        </div>
    </div>
    <div id='planMatos' class='ui-widget titreSection pageBreakBefore bordFin ui-corner-all'>
        <div class='ui-widget-header ui-corner-all enorme pad5'>Infos Matériel</div>
        <div id='matosContainer' class='container marge30l'>
            <div class='inline demi'>
                <?php
                $retour['matos'] = Matos_getManque ( $retour['id'], $retour['matos']);
                $matosBySousCat  = creerSousCatArray_showExterieur ( $retour['matos'] );

                foreach ($matosBySousCat as $id => $matos) {
                    if (empty($matos)) {
                        continue;
                    }

                    if ($list_sousCat[$id]['label'] != 'A louer') {
                        echo "<div class='margeTop10'><div class='gros'><b>" . $list_sousCat[$id]['label']  . "</b></div>";
                        foreach( $matos as $mat ){
                            if ( isset ($mat['manque']) && $mat['manque'] != 0 )
                                $pasdispo = ' + <b>' . -$mat['manque'] . ' à louer</b> !';
                            else $pasdispo = '';

                            echo "<div class='marge30l'>
                                    <div class='inline' style='width:20pt'>" . $mat['qte'] . "</div> x <div class='inline' style='width:150px;'>" . $mat['ref'] ."</div>
                                    <div class='inline red'>$pasdispo</div>
                                </div>";
                        }
                        echo "</div>" ;
                    } else {
                        $alouer = "
                            <div class='margeTop10'>
                                <div class='enorme'><b>" . $list_sousCat[$id]['label']  . " à l'extérieur</b>
                            </div>
                            <p></p>";
                        $matos_a_louer_trie_structure = MatosExt_by_Location($matos);
                        foreach ($matos_a_louer_trie_structure as $struct => $matList) {
                            $alouer .= "<div class='gros padH5'><b> " . $struct  . "</b></div>";
                            foreach ($matList as $mat) {
                                $alouer .= "
                                    <div class='marge30l'>
                                        <div class='inline'>" . $mat['qte'] . "</div> x <div class='inline'>" . $mat['ref'] . "</div>
                                    </div>";
                            }
                        }
                        $alouer .= "</div>" ;
                    }
                }
                ?>
            </div>
            <div class='inline demi top'>
                <?php echo @$alouer ; ?>
            </div>
            <br /><br />
        </div>
    </div>
    <div id='planDecla' class='ui-widget titreSection pageBreakBefore bordFin ui-corner-all'>
        <div style='width:99%'>
            <div class='inline top padV10' style='width:47%;'>
                <br />
                <?php
                echo TYPE_BOITE . ' <span class="enorme inline">' . NOM_BOITE .'</span><br />';
                echo ADRESSE_BOITE . "<br />" . CP_BOITE . ' ' . VILLE_BOITE . '<p>';
                echo "N° de TVA intracommunautaire : " . N_TVA_BOITE . '<br />';
                echo "N° de SIRET : " . SIRET_BOITE . '<p>';
                echo "<span>" . TEL_BOITE . " - " . EMAIL_BOITE . ' - </span>';
                ?>
            </div>
            <div class='demi inline rightText'><img src="../config/logo.jpg" /></div>
        </div>
        <div class='ui-widget-header ui-corner-all center enorme pad5'>
            Infos Déclaration des techniciens
            <span class='micro'>
                <br />
                Pour la période Du <?= $retour['timeDebut']; ?> au <?= $retour['timeFin']; ?> inclus
            </span>
        </div>
        <div>
            <?php
            $declaDiv = ''; $defaultTotalDecla = 0;
            foreach ( $retour['sousPlans'] as $sp ){
                $tekosExp = explode(',', $sp['tekos']);
                foreach( $tekosExp as $tek){
                    $tek = ltrim($tek);

                    if ( ! @$listeTeks[$tek]['print_decla'] ){
                        if ( $listeTeks[$tek]['intermittent'] == '0' ) $entrepriseNote = ' <span class="red">*</span>'; else $entrepriseNote = '';
                        $declaDiv .= '<div class="tekName padH5 marge30l">
                                        <div class = "inline" style="width:200px;"><b>'.$listeTeks[$tek]['prenom'] . ' ' . $listeTeks[$tek]['nom'] .$entrepriseNote. '</b></div>

                                        <div class="inline">
                                                  Jours travaillés : <input size="2" class="decla nbJourTaf"   type="text" value="'.$listeTeks[$tek]['nbJoursTaf'].'"/>
                                                   hrs/jour : <input size="2" class="decla nbHeureTaf"  type="text" value="8" /></div>
                                                  Cout Journalier : <input size="2" class="decla nbBrouzJour" type="text" value="160" />
                                                  TOTAL : <div class="nbBrouzGlobal gras inline rightText" style="width:35px;">'.($listeTeks[$tek]['nbJoursTaf'] * 160).'</div> € '.$entrepriseNote.'
                                        </div>';
                        $defaultTotalDecla += $listeTeks[$tek]['nbJoursTaf'] * 160;
                        $listeTeks[$tek]['print_decla'] = true ;
                    }
                }
            }
            echo "<div>$declaDiv</div>";
            ?>
            <div class='micro marge30l'>
                <i><span class="enorme red">*</span> Auto-entrepreneur, fournira une facture séparée.</i>
            </div>
            <div class="tekName padH5 marge30r rightText enorme">
                <i class="nano">Somme déduite de la facture globale TTC initialement prévue :</i>
                TOTAL : <span class='totalDecla'><?= number_format($defaultTotalDecla, 2, '.', ''); ?></span> €
            </div>
        </div>
        <p></p>
        <span class='ui-widget-header ui-corner-all pad5'>Coordonnées des techniciens</span><p>
        <div class="marge30l" style='width:99%;'>
            <?php
            $tekDiv = '';
            foreach ($retour['sousPlans'] as $sp) {
                $tekosExp = explode(',', $sp['tekos']);
                foreach( $tekosExp as $tek){
                    $tek      = ltrim($tek);
                    $tel      = $listeTeks[$tek]['tel'] ;
                    $intermit = $listeTeks[$tek]['intermittent'] ;
                    $siret    = $listeTeks[$tek]['SIRET'] ;
                    if ( $intermit == '1' ) {
                        $infosEmploi = '<div class="inline tiers marge30l"><i>N° Assedic :</i> '  . @$listeTeks[$tek]['assedic'] . '</div>
                                        <div class="inline tiers"><i>N° Congés Sp. :</i> '   . @$listeTeks[$tek]['CS'] . '</div>
                                        <div class="inline"><i>N° GUSO :</i> ' . @$listeTeks[$tek]['GUSO'] . '</div>';
                    }
                    else $infosEmploi = '<div class="marge30l"><b>AUTO-ENTREPRENEUR</b> : Fournira directement une facture.</b></div>
                                         <div class="marge30l"><i>N° SIRET :</i> '. @$siret.'</div>';

                    if (@$listeTeks[$tek]['categorie'] == 'regisseur') {
                        $posteTek = 'REGISSEUR';
                    } else {
                        $posteTek = 'Technicien '.strtoupper(@$listeTeks[$tek]['categorie']);
                    }

                    if (!@$listeTeks[$tek]['print_list']) {
                        $tekDiv .= '
                            <div>
                                <b>'.$listeTeks[$tek]['prenom'] . ' ' . $listeTeks[$tek]['nom'] .'</b>
                                ( '. formatTelephone($tel) .') <u>' . @$listeTeks[$tek]['email'] . '</u>
                            </div>
                            <div class="tekName" style="margin-bottom:25px;">
                                <div class="marge30l"><i>Adresse :</i> ' .@$listeTeks[$tek]['adresse'] . " " . @$listeTeks[$tek]['cp']. ' ' .@$listeTeks[$tek]['ville'] . '</div>
                                <div class="inline tiers marge30l"><i>Date de naissance :</i> ' . @$listeTeks[$tek]['birthDay'] . '</div>
                                <div class="inline tiers"><i>Lieu de Naissance :</i> ' . @$listeTeks[$tek]['birthPlace'] . '</div>
                                <br />
                                <div class="marge30l"><i>Poste :</i> '.$posteTek.'</div>
                                <div class="marge30l"><i>No SÉCU :</i> ' . @$listeTeks[$tek]['SECU'] . '</div>
                                '. $infosEmploi .'
                            </div>';
                    }
                    $listeTeks[$tek]['print_list'] = true ;
                }
            }
            echo "<div class='gros'>" . $tekDiv . "</div>";
            ?>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('.decla').change( function(){
                var nbJours =  parseInt ($(this).parents('.tekName').find('.nbJourTaf').val() );
                var nbHeures = parseInt ($(this).parents('.tekName').find('.nbHeureTaf').val() );
                var nbPrix =   parseInt ($(this).parents('.tekName').find('.nbBrouzJour').val());

                if ( isNaN (nbJours) ) { alert ('Nombre de jours incorrect');   $(this).parents('.tekName').find('.nbJoursTaf').val(0);  nbJours = 0 ;}
                if ( isNaN (nbHeures) ) { alert ('Nombre d\'heures incorrect'); $(this).parents('.tekName').find('.nbHeureTaf').val(0);  nbHeures = 0 ;}
                if ( isNaN (nbPrix) ) { alert ('Cout Journalier incorrect');    $(this).parents('.tekName').find('.nbBrouzJour').val(0); nbPrix = 0 ;}

                $(this).parents('.tekName').find('.nbBrouzGlobal').html( nbJours * nbPrix );

                var total = 0 ;
                $('.nbBrouzGlobal').each( function (){
                    var subTotal = parseInt( $(this).html());
                    if ( ! isNaN ( subTotal ) ) total = total + subTotal ;
                })
                $('.totalDecla').html( total.toFixed(2) );
            });
        });
    </script>
</body>
</html>
