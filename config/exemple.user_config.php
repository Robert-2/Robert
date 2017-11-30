<?php
/*
 * Modifiez les lignes 12 à 15 ci-dessous, en vous inspirant de l'exemple, pour renseigner :
 * le nom du serveur MySQL hôte où se trouve la base de données MySQL
 * le nom de l'utilisateur MySQL
 * le mot de passe de l'utilisateur MySQL
 * le nom de la base de données
 *
 * N'oubliez pas de sauvegarder sous le nom : user_config.php
*/

define("HOST", "localhost");   // serveur hôte MySQL
define("USER", "root");        // utilisateur MySQL
define("PASS", "      ");      // mot de passe MySQL
define("BASE", "robert");      // nom de la base de données

/*
 * Vous pouvez aussi modifier ci-dessous la fonction de calcul du coeficient,
 * qui permet d'effectuer un tarif dégressif en fonction de la durée de location
 */

function coef($nbJours)
{
    return ($nbJours - 1) * 3/4 + 1;
}
