<?php

function datefr($i){

$joursFR = array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi', 'Dimanche');    
$joursEN = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday', 'Sunday');    
$moisEN = array('January','February','March','April','May','June','July','August','September','October','November','December');    
$moisFR = array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre');    
$joursAbregeFR = array('Lun','Mar','Mer','Jeu','Ven','Sam', 'Dim');    // Jours abrégés en français
$joursAbregeEN = array('Mon','Tue','Wed','Thu','Fri','Sat', 'Sun');    // Jours abrégés en Anglais
$moisAbregeFR = array('Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc');    // Les mois abrégés en Français
$moisAbregeEN = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');    //Les mois abrégés en Anglais
$val = $i;    // On éxécute la fonction date avec les arguments
$val = str_replace($joursEN, $joursFR, $val);
$val = str_replace($joursAbregeEN, $joursAbregeFR, $val);    // Si il y a des mois en Anglais dans la variable retournée par la fonction Date(), bah on les trduits en français
$val = str_replace($moisEN, $moisFR, $val);    // Si il y a des mois en Anglais dans la variable retournée par la fonction Date(), bah on les trduits en français
$val = str_replace($moisAbregeEN, $moisAbregeFR, $val);    // Si il y a des mois en Anglais dans la variable retournée par la fonction Date(), bah on les trduits en français
return $val;    // Pour finir, bah on retourne la variable avec les jours et les mois traduits de l'anglais au français
} 


?>
