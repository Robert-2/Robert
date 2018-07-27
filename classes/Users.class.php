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

require_once(INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php');

class Users implements Iterator
{
    const UPDATE_ERROR_DATA = 'donnée invalide'; // erreur si email ou autre donnée ne correspond pas
    const UPDATE_OK         = 'donnée modifiée, OK !'; // message si une modif BDD a réussi
    const INFO_ERREUR       = 'Impossible de lire les infos en BDD'; // erreur de la méthode Infos::loadInfos()
    const INFO_DONT_EXIST   = 'donnée inexistante'; // erreur si champs inexistant dans BDD lors de récup d'info
    const INFO_FORBIDDEN    = 'donnée interdite'; // erreur si info est une donnée sensible (ici, password)
    const SAVE_LOSS         = 'Champs manquants'; // erreur si il manques des données essentielles à sauvegarder dans la BDD
    const NO_HABILITATION   = 'Vous n\'êtes pas habilité !'; // erreur si niveau d'habilitation insuffisant

    const USERS_OK          = true; // retour général, si la fonction a marché
    const USERS_ERROR       = false;  // retour général, si la fonction n'a pas marché
    const USERS_ERROR_PASSSHORT = 'Mot de passe trop court';

    const USERS_LEVEL_ADMIN  = 7; // niveau d'habilitation ADMIN
    const USERS_LEVEL_MODIFY = 4; // niveau d'habilitation MODÉ
    const USERS_LEVEL_READ   = 2; // niveau d'habilitation POPPY

    const USERS_ID               = 'id';
    const USERS_EMAIL            = 'email';
    const USERS_PASS             = 'password';
    const USERS_NOM              = 'nom';
    const USERS_PRENOM           = 'prenom';
    const USERS_LEVEL            = 'level';
    const USERS_TEKOS            = 'idTekos';
    const USERS_DATE_INSCRIPTION = 'date_inscription';

    private $hide_datas; // tableau contenant les champs qu'on ne peut modifier ds la BDD
    private $email; // email (= 'id' de l'user, quand il se loggue)
    private $infos; // instance de Infos (pour récup et update)


    public function __construct($email = 'new')
    {
        $this->hide_datas = ["password", "date_inscription", "date_last_action"];
        $this->infos = new Infos(TABLE_USERS);
        if ($email == 'new') {
            return 1;
        }
        // si un email est specifié, on lit l'enregistrement dans la base de données
        if ($this->checkEmail($email) === false) {
            throw new Exception('Loggin invalide : attendu adresse mail');
        }
        $this->email = $email;
        $this->loadFromBD(Users::USERS_EMAIL, $this->email);
    }

    // Charge les infos d'un user
    public function loadFromBD($keyFilter, $value)
    {
        try {
            $this->infos->loadInfos($keyFilter, $value);
        } catch (Exception $e) {
            throw new Exception(Users::INFO_ERREUR.' pour : '.$keyFilter.' = '.$value);
        }
    }

    // Ajoute / modifie une info de l'user, peu importe laquelle
    public function setUserInfos($key, $value)
    {
        switch ($key) {
            case Users::USERS_EMAIL:
                if ($this->setEmail($value) == false) {
                    echo 'email impossible à sauver !<br />';
                }
                break;
            case Users::USERS_PASS:
                if ($this->setPassword($value) == false) {
                    echo 'mot de passe impossible à sauver !<br />';
                }
                break;
            default:
                $this->infos->addInfo($key, $value);
                break;
        }
    }


    // Retourne une valeur de l'objet Infos
    public function getUserInfos($what = '')
    {
        if ($what == '') {
            try {
                $info = $this->infos->getInfo();
            } catch (Exception $e) {
                return $e->getMessage();
            }
            unset($info['password']);
        } elseif ($what == 'password') {
            return Users::INFO_FORBIDDEN;
        } else {
            try {
                $info = $this->infos->getInfo($what);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        return $info;
    }


    public function getPassword($authToGetPassword = false)
    {
        if ($authToGetPassword != md5('YawollJesuiSbiEnMoIMêm')) {
            return;
        } else {
            return ($this->infos->getInfo('password'));
        }
    }


    // Nb de valeurs ds l'objet Infos
    public function nbElements()
    {
        return $this->infos->nbInfos();
    }

    // ajoute / modifie une info et la sauvegarde directement
    public function updateInfo($typeInfo, $newInfo)
    {
        $this->infos->addInfo($typeInfo, $newInfo);
        $this->save();
        return;

        if (count($newInfo) == 0) {
            return Users::UPDATE_ERROR_DATA;
        }

        if ($typeInfo == 'email') {
            if ($this->checkEmail($newInfo) === false) {
                return Users::UPDATE_ERROR_DATA;
            }
            $_SESSION[COOKIE_NAME_LOG] = $newInfo;
            setcookie(COOKIE_NAME_LOG, $newInfo, COOKIE_PEREMPTION, "/");
        } elseif ($typeInfo == 'password') {
            $newInfo = md5(SALT_PASS.$newInfo);
            $_SESSION[COOKIE_NAME_PASS] = $newInfo;
            setcookie(COOKIE_NAME_PASS, $newInfo, COOKIE_PEREMPTION, "/");
        }

        try {
            $this->infos->update($typeInfo, $newInfo);
            return "Mise à jour de $typeInfo effectuée !";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Verifie si la syntaxte d'un email est valide
    public function checkEmail($addresse)
    {
        if (count($addresse) == 0) {
            return false;
        }
        $validMail = filter_var($addresse, FILTER_VALIDATE_EMAIL);
        return $validMail;
    }

    // Check si l'user a le niveau DEV
    public function isDev()
    {
        if ($this->infos->getInfo('level') >= 9) {
            return true;
        } else {
            return false;
        }
    }

    // Check si l'user a le niveau admin
    public function isAdmin()
    {
        if ($this->infos->getInfo('level') >= 7) {
            return true;
        } else {
            return false;
        }
    }

    // Check si l'user a le niveau modificateur
    public function isLevelMod()
    {
        if ($this->infos->getInfo('level') >= 5) {
            return true;
        } else {
            return false;
        }
    }

    // sauvegarde les données en BDD
    public function save()
    {
        // verification des infos minimales pour autoriser la sauvegarde
        if (!$this->infos->getInfo(Users::USERS_EMAIL)
            || !$this->infos->getInfo(Users::USERS_NOM)
            || !$this->infos->getInfo(Users::USERS_PASS)) {
            throw new Exception(Users::SAVE_LOSS);
        }
        // nouvel User ? création de la date d'inscription
        if (!$this->infos->getInfo(Users::USERS_DATE_INSCRIPTION)) {
            $this->infos->addInfo(Users::USERS_DATE_INSCRIPTION, time());
        }

        $this->infos->save();
        // update du tekos associé pour lui donner l'id d'user
        $this->update_tekos_assoc();

        return Users::USERS_OK;
    }

    private function update_tekos_assoc()
    {
        // pour pouvoir récupérer l'id de l'user nouvellement créé
        $this->loadFromBD(Users::USERS_EMAIL, $this->infos->getInfo(Users::USERS_EMAIL));

        // récup des id
        $idUser = $this->infos->getInfo(Users::USERS_ID);
        $idTekos = $this->infos->getInfo(Users::USERS_TEKOS);

        // Si l'id Tekos = 0, c'est qu'on veut supprimer l'association
        if ($idTekos == '0') {
            // trouve le Tekos qui AVAIT l'user associé, avant le save
            $l = new Liste();
            $listTekos = $l->getListe(TABLE_TEKOS);
            $idOldTekos = false;
            if (is_array($listTekos)) {
                foreach ($listTekos as $tek) {
                    if ($tek[Tekos::ID_USER_FROM_TEKOS] == $idUser) {
                        $idOldTekos = $tek[Tekos::ID_TEKOS];
                    }
                }
            }
            unset($l);

            // si il a trouvé, on update le tekos (on met l'idUser à 0)
            if ($idOldTekos !== false) {
                $tmpTekos = new Tekos($idOldTekos);
                $tmpTekos->setTekosInfo(Tekos::ID_USER_FROM_TEKOS, '0');
                $tmpTekos->save();
            }
        } else {
            $tmpTekos = new Tekos($idTekos);
            $tmpTekos->setTekosInfo(Tekos::ID_USER_FROM_TEKOS, $idUser);
            $tmpTekos->save();
        }
        unset($tmpTekos);
    }

    // SETTERS
    public function setLevel($level)
    {
        $this->infos->addInfo(Users::USERS_LEVEL, $level);
        return Users::USERS_OK;
    }
    public function setPrenom($prenom)
    {
        $this->infos->addInfo(Users::USERS_PRENOM, $prenom);
        return Users::USERS_OK;
    }
    public function setName($newName)
    {
        $this->infos->addInfo(Users::USERS_NOM, $newName);
        return Users::USERS_OK;
    }
    public function setPassword($newpass)
    {
        if (strlen($newpass) < 4) {
            return Users::USERS_ERROR_PASSSHORT;
        }
        $crypt = md5(SALT_PASS.$newpass);
        $this->infos->addInfo(Users::USERS_PASS, $crypt);
        return Users::USERS_OK;
    }
    public function setEmail($email)
    {
        if (!$this->checkEmail($email)) {
            return Users::USERS_ERROR;
        }
        $this->infos->addInfo(Users::USERS_EMAIL, $email);
        return Users::USERS_OK;
    }
    public function setTekos($idTekos)
    {
        $this->infos->addInfo(Users::USERS_TEKOS, $idTekos);
        return Users::USERS_OK;
    }

    // Supprime l'utilisateur $id de la BDD
    public function deleteUser($id)
    {
        // controle si l'utilisateur est un admin
        $adm = $this->infos->getInfo(Users::USERS_LEVEL);
        if (!$adm || $adm != Users::USERS_LEVEL_ADMIN) {
            throw new Exception(Users::NO_HABILITATION);
        }

        $nb = $this->infos->delete(Users::USERS_ID, $id);
        return $nb;
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
        while ($this->infos->valid()) {
            if (in_array($this->infos->key(), $this->hide_datas)) {
                $this->infos->next();
            } else {
                return true;
            }
        }
        return false;
    }
    public function key()
    {
        return $this->infos->key();
    }
}
