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

class Interlocuteur implements Iterator
{
    const ID_INTERLOC = 'id';
    const LIST_STRUCT = 'idStructure';
    const ERR_INFO    = 'Impossible de récupérer l\'info : ';
    const SAVE_OK     = true;
    const MANQUE_INFO = 'Il manque une valeur pour pouvoir sauvegarder...';
    const ERR_SAVE    = 'Impossible de sauvegarder en BDD :';

    private $infos; // Instance de 'Infos'
    private $idInterloc; // id de l'interlocuteur

    public function __construct($id = 'new')
    {
        $this->infos = new Infos(TABLE_INTERLOC);
        if ($id != 'new') {
            $this->loadFromBD(Interlocuteur::ID_INTERLOC, $id);
        }
    }

    // Charge les infos de l'interlocuteur en mémoire
    public function loadFromBD($keyFilter, $value)
    {
        try {
            $this->infos->loadInfos($keyFilter, $value);
            $this->idInterloc = $this->infos->getInfo(Interlocuteur::ID_INTERLOC);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Récupère les valeurs de l'interlocuteur en BDD
    public function getInfoInterloc($what = '*')
    {
        try {
            // Récup l(es) info(s) de la structure
            $infosInterloc = $this->infos->getInfo($what);
            return $infosInterloc;
        } catch (Exception $e) {
            return $e->getMessage(); // Si existe pas, récup de l'erreur
        }
    }

    public function getInterelocStructName()
    {
        $idStruct  = $this->infos->getInfo('idStructure');
        $struct    = new Structure($idStruct);
        $nomStruct = $struct->getInfoStruct('label');
        return $nomStruct;
    }

    // Récupère l'ID du dernier interlocuteur nouvellement créé
    public function getNewInterlocID()
    {
        $this->loadFromBD('nomPrenom', $this->infos->getInfo('nomPrenom'));
        return $this->idInterloc;
    }

    // défini des nouvelles valeurs pour l'interlocuteur en mémoire
    public function setVals($arrKeyVals)
    {
        foreach ($arrKeyVals as $key => $val) {
            $this->infos->addInfo($key, $val);
        }
    }

    // Sauvegarde des valeurs en mémoire dans la BDD
    public function save()
    {
        $verifInfo = $this->infos->getInfo(); // Check si on a bien tout ce qu'il faut avant de sauvegarder en BDD
        if (!$verifInfo['nomPrenom'] || !$verifInfo['adresse'] || !$verifInfo['codePostal'] || !$verifInfo['ville']) {
            return Interlocuteur::MANQUE_INFO;
        }
        try {
            $this->infos->save();
            return Interlocuteur::SAVE_OK;
        } catch (Exeption $e) {
            return Interlocuteur::ERR_SAVE . $e->getMessage();
        }

    }

    // Supprime un interlocuteur dans la BDD
    public function deleteInterloc()
    {
        return $this->infos->delete(Interlocuteur::ID_INTERLOC, $this->idInterloc);
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
