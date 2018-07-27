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

class Devis
{
    // dossier principal des contenu des plans (relatif au dossier 'fct' !)
    const PATH_CONTENU_PLANS = '../datas/PLANS_DATAS/';
    const DEVIS_cFICHIER     = 'fichier'; // champs BDD
    const DEVIS_cID_PLAN     = 'id_plan';
    const DEVIS_cNUM_DEVIS   = 'numDevis';
    const DEVIS_cMATOS       = 'matos';
    const DEVIS_cTEKOS       = 'tekos';
    const DEVIS_cTOTAL       = 'total';

    private $idPlan;
    private $devisInfos;
    private $devisFilename;
    private $devisFilePath;

    public function __construct($idPlan)
    {
        if (!isset($idPlan)) {
            return 'Merci de spécifier l\'ID du plan...';
        }
        $this->idPlan        = $idPlan;
        $this->devisInfos    = new Infos(TABLE_DEVIS);
        $this->devisFilePath = Devis::PATH_CONTENU_PLANS.'/'.$idPlan.'/devis/';
    }

    // Retourne un tableau des noms de fichiers de devis pour le dossier d'un plan
    // (NOTE : PAS BESOIN DE CRÉER UN OBJET DEVIS POUR L'APPELLER (static) !!)
    public static function getDevisFiles($idPlan, $withTotal = false)
    {
        $l = new Liste();
        $dossierDevisPlan = '' . Devis::PATH_CONTENU_PLANS . '/' . $idPlan . '/devis/';
        if ($withTotal == true) {
            $listeDevisFiles = $l->getListe(
                TABLE_DEVIS,
                Devis::DEVIS_cFICHIER.', '.Devis::DEVIS_cTOTAL,
                Devis::DEVIS_cNUM_DEVIS,
                'ASC',
                Devis::DEVIS_cID_PLAN,
                '=',
                $idPlan
            );
            unset($l);

            // Efface le dossier du plan si le retour BDD est nul.
            if ($listeDevisFiles == false) {
                rrmDir($dossierDevisPlan);
                return false;
            }
            $i = 0;
            $devisFilesWithTotaux = false;

            // Efface l'entrée en BDD si le fichier n'existe plus
            foreach ($listeDevisFiles as $devis) {
                if (!file_exists($dossierDevisPlan.$devis[Devis::DEVIS_cFICHIER])) {
                    $d = new Devis($idPlan);
                    $d->deleteDevisBDD($devis[Devis::DEVIS_cFICHIER]);
                    unset($d);
                } else {
                    $devisFilesWithTotaux[$i]['file']  = $devis[Devis::DEVIS_cFICHIER];
                    $devisFilesWithTotaux[$i]['total'] = $devis[Devis::DEVIS_cTOTAL];
                    $i++;
                }
            }
            return $devisFilesWithTotaux;
        } else {
            $listeDevisFiles = $l->getListe(
                TABLE_DEVIS,
                Devis::DEVIS_cFICHIER,
                Devis::DEVIS_cNUM_DEVIS,
                'ASC',
                Devis::DEVIS_cID_PLAN,
                '=',
                $idPlan
            );
            unset($l);

            if ($listeDevisFiles == false) {
                return false;
            }

            // Efface l'entrée en BDD si le fichier n'existe plus
            foreach ($listeDevisFiles as $devis) {
                if (!file_exists('../'.Devis::PATH_CONTENU_PLANS.'/'.$idPlan.'/devis/'.$devis[Devis::DEVIS_cFICHIER])) {
                    $d = new Devis($idPlan);
                    $d->deleteDevisBDD($devis[Devis::DEVIS_cFICHIER]);
                    unset($d);
                }
            }
            return $listeDevisFiles;
        }
    }

    // Supprime un devis et son entrée en BDD
    public function deleteDevis($fileName = '')
    {
        if ($fileName == '') {
            throw new Exception("Il manque le nom du fichier du devis à supprimer !");
        }
        if (!file_exists($this->devisFilePath.$fileName)) {
            throw new Exception("Le fichier $this->devisFilePath$fileName n'existe pas !");
            return;
        }
        if (unlink($this->devisFilePath.$fileName) == false) {
            throw new Exception("Erreur lors de la suppression de $this->devisFilePath$fileName !\n\nMerci de vérifier les droits en écriture.");
        } else {
            try {
                $this->devisInfos->delete('fichier', $fileName, '`id_plan` = "'.$this->idPlan.'"');
            } catch (Exception $e) {
                throw new Exception("Impossible de supprimer le devis en BDD : \n\n" . $e->getMessage());
            }
        }
    }

    // Supprime un devis (selon son fichier) seulement en BDD
    private function deleteDevisBDD($fileName = '')
    {
        if ($fileName == '') {
            return false;
        }
        try {
            $this->devisInfos->delete(Devis::DEVIS_cFICHIER, $fileName);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Suppprime TOUS les devis associés à un plan (dans le cas ou on supprime
    // le plan, pour nettoyer le serveur et la BDD)
    public static function deleteAllDevisBDD($idPlan)
    {
        if ($idPlan == '') {
            return false;
        }
        $l = new Liste();
        $listeDevis = $l->getListe(
            TABLE_DEVIS,
            '*',
            Devis::DEVIS_cNUM_DEVIS,
            'ASC',
            Devis::DEVIS_cID_PLAN,
            '=',
            $idPlan
        );

        if ($listeDevis != false) {
            foreach ($listeDevis as $devis) {
                try {
                    $dI = new Infos(TABLE_DEVIS);
                    $dI->loadInfos('id', $devis['id']);
                    $dI->delete();
                } catch (Exception $e) {
                    return false;
                }
                unset($dI);
            }
        }
        rrmdir(INSTALL_PATH.Devis::PATH_CONTENU_PLANS.'/'.$idPlan.'/devis/');
    }

    // Compte le nombre de devis enregistrés en BDD pour ce plan
    public function getNbDevis()
    {
        $l = new Liste();
        $listeDevis = $l->getListe(
            TABLE_DEVIS,
            '*',
            Devis::DEVIS_cNUM_DEVIS,
            'ASC',
            Devis::DEVIS_cID_PLAN,
            '=',
            $this->idPlan
        );
        unset($l);

        if ($listeDevis == false) {
            return 0;
        } else {
            return (count($listeDevis));
        }
    }

    // Retourne le dernier numDevis enregistré en BDD pour ce plan
    public static function getLastNumDevis($idPlan)
    {
        $l = new Liste();
        $listeDevis = $l->getListe(
            TABLE_DEVIS,
            Devis::DEVIS_cNUM_DEVIS,
            Devis::DEVIS_cNUM_DEVIS,
            'ASC',
            Devis::DEVIS_cID_PLAN,
            '=',
            $idPlan
        );
        unset($l);

        if ($listeDevis == false) {
            return 0;
        } else {
            return (end($listeDevis));
        }
    }

    // GETTERS d'infos de devis
    public function getDevisMatos()
    {
        return json_decode($this->devisMatos);
    }
    public function getDevisTekos()
    {
        return explode(' ', $this->devisTekos);
    }
}
