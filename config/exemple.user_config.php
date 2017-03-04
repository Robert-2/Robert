<?php
/*
 * Modifiez les lignes 40 à 43 ci-dessous, en vous inspirant de l'exemple, pour renseigner :
 * le nom de l'hôte ou se trouve la base de données SQL
 * le nom de l'utilisateur SQL
 * le mot de passe de l'utilisateur SQL
 * le nom de la base de données
 *
 * N'oubliez pas de sauvegarder sous le nom : user_config.php
*/

define("HOST", "localhost");   // nom de l'hôte ou se trouve la bdd
define("USER", "root");        // nom de l'utilisateur autorisé à se connecter
define("PASS", "      ");      // son mot de passe
define("BASE", "robert");      // nom de la base de données

/*
 * Vous pouvez aussi modifier la fonction de calcul du coeficient
 * qui permet d'effectuer un tarif dégressif en fonction de la durée de location
 */

function coef($nbJours)
{
    return ($nbJours - 1) * 3/4 + 1;
}
