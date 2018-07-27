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

class Structure implements Iterator
{
    const ID_STRUCT          = 'id';
    const IDS_INTERLOC       = 'interlocteurs';
    const ERR_INFO           = 'Impossible de récupérer l\'info : ';
    const SAVE_OK            = true;
    const MANQUE_INFO        = 'Il manque une valeur pour pouvoir sauvegarder...';
    const MANQUE_ID_INTERLOC = 'Besoin de l\'id de l\'interlocuteur !';
    const ERR_SAVE           = 'Impossible de sauvegarder en BDD :';
    const INTERLOC_VIDE      = 'degun !';

    private $infos;     // objet 'Infos' de la structure
    private $idStruct;  // id de la structure
    private $Interlocs; // tableau d'id des interlocuteurs

    public function __construct($id = 'new')
    {
        $this->infos     = new Infos(TABLE_STRUCT);
        $this->Interlocs = [];
        if ($id == 'new') {
            $this->infos->addInfo('id', 0);
            return 1 ;
        }
        $this->loadFromBD(Structure::ID_STRUCT, $id);
    }

    // charge en mémoire les infos de la structure
    public function loadFromBD($keyFilter, $value)
    {
        try {
            $this->infos->loadInfos($keyFilter, $value);
            $this->idStruct = $this->infos->getInfo(Structure::ID_STRUCT);
            $interlocsList  = $this->infos->getInfo(Structure::IDS_INTERLOC);
            if ($interlocsList != '') {
                $this->Interlocs = explode(',', $interlocsList);
            } else {
                $this->Interlocs = false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // récupère les infos de la structure
    public function getInfoStruct($what = '*')
    {
        try {
            $infosStruct = $this->infos->getInfo($what);
            return $infosStruct;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // définit des nouvelles valeurs pour la structure en mémoire
    public function setVals($arrKeyVals)
    {
        foreach ($arrKeyVals as $key => $val) {
            $this->infos->addInfo($key, $val);
        }
    }

    // redéfinit la liste des interlocuteurs pour cette structure
    public function updateInterloc($idToAdd = false)
    {
        if ($idToAdd) {
            $listeActuelleInterlocs = $this->infos->getInfo('interlocteurs');
            if ($listeActuelleInterlocs == '') {
                $listeActuelleInterlocs = $idToAdd;
            } else {
                $listeActuelleInterlocs .= ','.$idToAdd;
            }
            $this->setVals(['interlocteurs' => $listeActuelleInterlocs]);
            return true;
        } else {
            return Structure::MANQUE_ID_INTERLOC;
        }
    }

    // Retourne le tableau des ID (ou autre) de tous les interlocuteurs pour cette structure
    public function getInterlocs($type = Interlocuteur::ID_INTERLOC)
    {
        $infoInterloc = [];
        if ($type == Structure::ID_STRUCT) {
            if (is_array($this->Interlocs)) {
                $infoInterloc = $this->Interlocs;
            } else {
                $infoInterloc[] = Structure::INTERLOC_VIDE;
            }
        } else {
            if (is_array($this->Interlocs)) {
                foreach ($this->Interlocs as $idInterloc) {
                    try {
                        $interloc       = new Interlocuteur($idInterloc);
                        $infoInterloc[] = $interloc->getInfoInterloc($type);
                    } catch (Exception $e) {
                        $infoInterloc[] = Structure::INTERLOC_VIDE;
                    }
                }
            } else {
                $infoInterloc[] = Structure::INTERLOC_VIDE;
            }
        }

        return $infoInterloc;
    }

    // Sauvegarde des valeurs en mémoire dans la BDD
    public function save()
    {
        $verifInfo = $this->infos->getInfo();
        if (!$verifInfo['label']
            || !$verifInfo['NomRS']
            || !$verifInfo['adresse']
            || !$verifInfo['codePostal']
            || !$verifInfo['ville']) {
            return Structure::MANQUE_INFO;
        }
        try {
            $this->infos->save();
            return Structure::SAVE_OK;
        } catch (Exeption $e) {
            return Structure::ERR_SAVE . $e->getMessage();
        }
    }

    // supprimer une structure dans la BDD
    public function deleteStruct()
    {
        $del = $this->infos->delete(Structure::ID_STRUCT, $this->idStruct);
        return $del;
    }

    // Méthodes de l'iterator
    public function current()
    {
        return $this->infos->current();
    }
    public function next()
    {
        $this->infos->next();
    }
    public function rewind()
    {
        $this->infos->rewind();
    }
    public function valid()
    {
        return ($this->infos->valid() === false);
    }
    public function key()
    {
        return $this->infos->key();
    }
}
