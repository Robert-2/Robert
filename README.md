![Robert-logo](https://robertmanager.org/gfx/Logo_ROBERT.png)

### Robert, c'est :
- Une WEB-APP (logiciel en ligne) open source, écrite en `php`, `js`, `html` et `xml`, utilisant _jQuery_, _ajax_ et _mysql_.

**Hum, mais encore ?**

- Un gestionnaire de parc de matériel destiné à la location
- Un outil bien pratique pour assurer le bon fonctionnement d'un parc de matériel et la liaison avec les clients / techniciens, accessible n'importe où depuis internet
- Un moyen simple et efficace de s'y retrouver dans les sorties-retours de matériel
- Une interface claire et fonctionnelle
- Une solution pour stocker les données relative au parc, de manière sécurisée et centralisée sur votre propre serveur
- Un projet communautaire auquel tout le monde (quidam, association, entreprise) peut participer

### Robert, ce n'est pas :
- Un logiciel de compta
- Un logiciel exécutable sur un ordinateur local (c'est une web-app, accessible seulement via un navigateur web)
- Un substitut à votre cerveau
- Une machine à café

## Fonctionnalités
### Gestionnaire d’événements (calendrier)
- Ajout d’événements
- Gestion de la disponibilité des techniciens
- Gestion des quantités de matériel disponible à la (aux) date(s) donnée(s)
- Création de devis et factures au format PDF
- Système de calcul de remises, en % ou en €
- Création de listes récapitulatives du matériel à préparer/louer, des infos sur l'événement et du fichier de déclaration des techniciens.
- Météo du lieu de l’événement (si pas trop éloigné dans le temps)
- Petit système de post-it pour communiquer avec toute l'équipe

### Gestionnaire de parc matériel
- Gestion du matériel (ajout, suppression, modification)
- Création de "Pack" de matériels (liste prédéfinie)
- Création de sous catégories pour le tri du matériel

### Gestion des techniciens
- Ajout de techniciens et de leur infos (n°SÉCU, n°GUSO, coordonnées, etc.)
- Création de comptes (compte utilisateur associé aux infos technicien)

### Gestion des "bénéficiaires" (clients)
- Gestion de structures (associations, collectivités, entreprises, particuliers...)
- Gestion des interlocuteurs associés à ces structures

### Gestion des informations
- Coordonnées de la structure du parc de matériel

### Module de sauvegarde
- Sauvegarde de la base de données dans un fichier téléchargeable
- Restauration d'un fichier de sauvegarde dans la base en cas d'erreur

### Plusieurs thèmes disponibles pour l'interface

## Documentation
### Installation
Sommaire ([voir wiki](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION)) :

1. [Avant de commencer](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION#1-avant-de-commencer)
2. [Installation des fichiers source](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION#2-installation-des-fichiers-source)
3. [Configuration](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION#3-configuration)
4. [Mise à jour de Robert](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION#4-mise-à-jour-de-robert)
5. [Cas d'installation portable sous Windows avec µWamp](https://github.com/RobertManager/robert/wiki/3.-Documentation-du-Robert-:-INSTALLATION#5-cas-dinstallation-portable-sous-windows-avec-µwamp)

### Utilisation
Sommaire ([voir wiki](https://github.com/RobertManager/robert/wiki/4.-Documentation-du-Robert-:-UTILISATION)) :

1. [Pour commencer](https://github.com/RobertManager/robert/wiki/4.-Documentation-du-Robert-:-UTILISATION#1-pour-commencer)
2. [Création des informations](https://github.com/RobertManager/robert/wiki/4.-Documentation-du-Robert-:-UTILISATION#2-création-des-informations)
3. [Création d'un évènement](https://github.com/RobertManager/robert/wiki/4.-Documentation-du-Robert-:-UTILISATION#3-workflow-création-dun-évènement)

### Développement
Sommaire ([voir wiki](https://github.com/RobertManager/robert/wiki/5.-Documentation-du-Robert-:-D%C3%89VELOPPEMENT)) :

1. [Versionning, dépôt GIT et GitHub](https://github.com/RobertManager/robert/wiki/5.-Documentation-du-Robert-:-D%C3%89VELOPPEMENT#1-versionning-dépôt-git-et-github)
2. [Règles de base de présentation du code](https://github.com/RobertManager/robert/wiki/5.-Documentation-du-Robert-:-D%C3%89VELOPPEMENT#2-règles-de-base-de-présentation-du-code)
3. [La structure du Robert expliquée](https://github.com/RobertManager/robert/wiki/5.-Documentation-du-Robert-:-D%C3%89VELOPPEMENT#3-la-structure-du-robert-expliquée)
4. [Liste des classes](https://github.com/RobertManager/robert/wiki/5.-Documentation-du-Robert-:-D%C3%89VELOPPEMENT#4-liste-des-classes)

## Licence
Robert est un logiciel libre; vous pouvez le redistribuer et/ou
le modifier sous les termes de la Licence Publique Générale GNU Affero
comme publiée par la Free Software Foundation;
version 3.0.

Cette WebApp est distribuée dans l'espoir qu'elle soit utile,
mais SANS AUCUNE GARANTIE; sans même la garantie implicite de
COMMERCIALISATION ou D'ADAPTATION A UN USAGE PARTICULIER.
Voir la Licence Publique Générale GNU Affero pour plus de détails.

Vous devriez avoir reçu une copie de la Licence Publique Générale
GNU Affero avec les sources du logiciel (LICENCE.txt); si ce n'est pas
le cas, rendez-vous à http://www.gnu.org/licenses/agpl.txt (en Anglais)
