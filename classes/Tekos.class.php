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

class Tekos extends Users
{
    const ID_TEKOS           = 'id';
    const ID_USER_FROM_TEKOS = 'idUser';
    const ID_TEKOS_FROM_USER = 'idTekos';
    const SURNOM_TEKOS       = 'surnom';

    const TEKOS_cDIPLOMEFOLDER = 'diplomes_folder';

    private $userInfos; // Instance de Infos pour la table 'users'
    private $tekosInfos; // Instance de Infos pour la table 'tekos'
    private $id; // id du Tekos
    private $gotUser; // TRUE si le tekos est lié à un User (c.a.d. que le champ 'idUser' n'est pas à '0' en BDD)
    private $idTekos_fromUser; // ID du techos (tel qu'il apparaît dans la table User)
    private $idUser_fromTekos; // ID de l'user associé (tel qu'il apparaît dans la table Tekos)

    public function __construct($id = 'new')
    {
        $this->userInfos  = new Infos(TABLE_USERS);
        $this->tekosInfos = new Infos(TABLE_TEKOS);
        if ($id == 'new') {
            return 1;
        }
        $this->loadFromBD(Tekos::ID_TEKOS, $id);
    }

    public function loadFromBD($keyFilter, $value)
    {
        try {
            $this->tekosInfos->loadInfos($keyFilter, $value);
            $idT = $this->tekosInfos->getInfo(Tekos::ID_TEKOS);
            $idU = $this->tekosInfos->getInfo(Tekos::ID_USER_FROM_TEKOS);
            $this->id = $this->idTekos_fromUser = $idT;
            $this->idUser_fromTekos = $idU;
        } catch (Exception $e) {
            throw new Exception(Users::INFO_ERREUR.' (tekos) : '.$e->getMessage());
        }
    }

    public function setTekosInfo($key, $value)
    {
        if ($key == 'birthDay' && $value== '') {
            $value = null;
        }
        $this->tekosInfos->addInfo($key, $value);
    }

    public function save()
    {
        // verification des infos minimales pour autoriser la sauvegarde
        $surnomTekos = $this->tekosInfos->getInfo(Tekos::SURNOM_TEKOS);

        if (!$surnomTekos) {
            throw new Exception(Users::SAVE_LOSS);
        }

        // Si le 'diplom_folder' n'est pas défini, on le défini avec le surnom du tekos
        if (!$this->tekosInfos->getInfo(Tekos::TEKOS_cDIPLOMEFOLDER)) {
            $this->tekosInfos->addInfo(Tekos::TEKOS_cDIPLOMEFOLDER, $surnomTekos . 'Diploms');
        }

        $this->tekosInfos->save(Tekos::ID_TEKOS, $this->id);
        return Users::USERS_OK;
    }

    public function deleteTekos()
    {
        $del = $this->tekosInfos->delete(Tekos::ID_TEKOS, $this->id);

        return $del;
    }

    public function getTekosInfos($what = '*')
    {
        if ($what == 'password') {
            return Users::INFO_FORBIDDEN;
        }

        $infosTekos = $this->tekosInfos->getInfo($what);
        $result = [];

        if (isset($infosTekos)) {
            if (is_array($infosTekos)) {
                foreach ($infosTekos as $key => $val) {
                    $result[$key] = $val;
                }
            } else {
                $result = $infosTekos;
            }
        }

        return $result;
    }
}
