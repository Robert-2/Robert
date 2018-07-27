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

// laps de temps à attendre après avoir fait trop de tentatives (en secondes)
define('BF_TIME_LAPS', (60*5)); // 5 minutes

// Nbre de tentatives maxi, tous les TIME_LAPS
define('BF_NB_TENTATIVE', 5); // 5 tentatives

// répertoire de stockage des logs
define('BF_DIR', INSTALL_PATH.'BFlogs/');

if (!is_dir(INSTALL_PATH.'BFlogs/')) {
    mkdir(INSTALL_PATH.'BFlogs/');
}

// CLASSE DE SÉCU ANTI FORCE BRUTE
class NoBF
{
    public function __construct()
    {
    }

    // Teste si le nbre de tentative n'exède pas BF_NB_TENTATIVE dans le laps de temps défini avec BF_TIME_LAPS
    public static function bruteCheck($email)
    {
        $filename = BF_DIR . $email . '.tmp';
        $deny_access = false;

        if (file_exists($filename)) {
            $infos = NoBF::fileToArray($filename);
            $nb_tentatives = count($infos);
            $premiere_tentative = @$infos[0];

            if ($nb_tentatives < BF_NB_TENTATIVE) {
                $deny_access = false;
            } elseif ($nb_tentatives > BF_NB_TENTATIVE && (BF_TIME_LAPS + $premiere_tentative) > time()) {
                $deny_access = true;
            } else {
                $deny_access = false;
            }
        }
        return $deny_access;
    }

    public static function addTentative($email)
    {
        $filename = BF_DIR . $email . '.tmp';
        $date = time();

        if (file_exists($filename)) {
            $infos = NoBF::fileToArray($filename);
        } else {
            $infos = [];
        }

        $infos[] = $date;
        NoBF::arrayToFile($filename, $infos);
    }

    // Permet de supprimer les enregistrements trop anciens
    public static function cleanUp($infos)
    {
        foreach ($infos as $n => $date) {
            if ((BF_TIME_LAPS + $date) < time()) {
                unset($infos[$n]);
            }
        }

        return array_values($infos);
    }

    // Récupère les infos du fichier et les retourne unserialisé
    public static function fileToArray($filename)
    {
        $infos = unserialize(file_get_contents($filename));
        $infos = NoBF::cleanUp($infos);
        return $infos;
    }

    // Enregistre les infos dans le fichier de log serialisé
    public static function arrayToFile($filename, $data)
    {
        $file = fopen($filename, "w");
        fwrite($file, serialize($data));
        fclose($file);
        return true;
    }
}

// CLASSE DE CONNEXION D'UN UTILISATEUR
class Connecting
{
    private $db; // Instance de PDO
    private $connected;
    private $user			 = [];
    private $user_table_name = TABLE_USERS;
    private $login_cookie	 = COOKIE_NAME_LOG ;
    private $password_cookie = COOKIE_NAME_PASS;
    private $salt			 = SALT_PASS ;

    public function __construct($db)
    {
        $this->db = $db;
        if ($this->testConnexion() == false) {
            $this->connected = false;
        }
    }

    // Retourne si cette personne est connectée ou pas
    public function is_connected()
    {
        if ($this->connected) {
            return $_SESSION[$this->login_cookie];
        } else {
            return false;
        }
    }

    // Connexion : $email : string (email) / $password : string non crypté (mot de passe)
    public function connect($email, $password)
    {
        $deny_login = NoBF::bruteCheck($email);
        $this->disconnect();
        $email = preg_replace('/\\\'/', '', $email); // Empêcher les injections SQL en virant les '

        if ($deny_login == true) {
            exit('Trop de tentatives de connexion. Merci de recommencer dans quelques minutes.');
        } else {
            $q = $this->db->prepare(
                "SELECT `id`, `email`, `password`
                FROM `".$this->user_table_name."`
                WHERE `email` = '$email'
                AND `password` = '".md5($this->salt.$password)."' "
            );

            try {
                $q->execute();
            } catch (Exception $e) {
                echo $e->getMessage();
                // die();
            }

            if ($q->rowCount() == 1) {
                $this->connected = true;
                $this->user = $q->fetch(PDO::FETCH_ASSOC);
                $this->setSecuredData();
                $this->updateUser($this->user['id'], 1);
                return true;
            } else {
                NoBF::addTentative($email);
                return false;
            }
        }
    }

    // Déconnexion
    public function disconnect()
    {
        $this->resetSessionData();
        session_unset();
    }

    // Teste la connexion en cours
    private function testConnexion()
    {
        // def des vars à tester
        $toTestToken = '';
        $toTestPassword = '';
        $toTestLogin = '';

        // Conservation d'une connexion via cookie
        if (!empty($_COOKIE[$this->login_cookie])
            && !empty($_COOKIE[$this->password_cookie])
            && empty($_SESSION[$this->password_cookie])) {
            $toTestLogin    = $_COOKIE[$this->login_cookie];
            $toTestPassword = $_COOKIE[$this->password_cookie];
            $toTestToken    = $_COOKIE['token'];
        } elseif (!empty($_SESSION[$this->password_cookie]) && !empty($_SESSION[$this->login_cookie])) {
            $toTestLogin    = $_SESSION[$this->login_cookie];
            $toTestPassword = $_SESSION[$this->password_cookie];
            $toTestToken    = $_SESSION['token'];
        }

        // Si le token n'est pas identique au fingerprint du navigateur, on reset tout
        if ($toTestToken != $this->fingerprint()) {
            $this->resetSessionData();
            return false;
        }

        if (!empty($toTestLogin) && !empty($toTestPassword)) {
            // teste si l'utilisateur existe bel et bien
            $q  =   $this->db->prepare(
                "SELECT id,email,password
                FROM `".$this->user_table_name."`
                WHERE `email`='$toTestLogin'
                AND `password`='$toTestPassword'"
            );
            $q->execute();

            if ($q->rowCount() == 1) {
                $this->connected = true;
                $this->user = $q->fetch(PDO::FETCH_ASSOC);

                // Si connexion depuis cookie : on remet en place les sessions + cookies
                if (empty($_SESSION[$this->password_cookie]) || !empty($_SESSION[$this->login_cookie])) {
                    $this->setSecuredData();
                }

                $this->updateUser($this->user['id']);
                return true;
            } else {
                $this->resetSessionData();
                return false;
            }
        } else {
            return false;
        }
    }

    // Génère le token (jeton) du navigateur en cours
    private function fingerprint()
    {
        $fingerprint = $this->salt . $_SERVER['HTTP_USER_AGENT'];
        $token = md5($fingerprint . session_id());

        return $token;
    }

    // On défini les variables d'identifications (token, login et mot de passe)
    // !! obligation d'avoir défini $this->user avant de l'utiliser !!
    private function setSecuredData()
    {
        // declaration des sessions
        $_SESSION[$this->password_cookie] = $this->user['password'];
        $_SESSION[$this->login_cookie]    = $this->user['email'];
        $_SESSION['token']                = $this->fingerprint();

        // déclaration des cookies
        setcookie($this->login_cookie, $this->user['email'], COOKIE_PEREMPTION, "/");
        setcookie($this->password_cookie, $this->user['password'], COOKIE_PEREMPTION, "/");
        setcookie('token', $_SESSION['token'], COOKIE_PEREMPTION, "/");
    }

    // Reset complet des variables d'identification... C'est une déconnexion !
    private function resetSessionData()
    {
        // declaration des sessions
        $_SESSION[$this->password_cookie] = '';
        $_SESSION[$this->login_cookie]    = '';
        $_SESSION['token']                = '';

        // destruction des cookies en leur mettant une expiration dans le passé
        $peremptionCookies = time() - (3600 * 24 * 31 * 365); // - 1 an
        setcookie($this->login_cookie, '', $peremptionCookies, "/");
        setcookie($this->password_cookie, '', $peremptionCookies, "/");
        setcookie('token', '', $peremptionCookies, "/");

        $this->connected = false;
        $this->user = [];
        session_unset();
    }

    // Mise à jour de divers infos de connexion dans la BDD ($id : int du user) ($connexion : 0 ou 1)
    //     => 0 : test de connexion
    //     => 1 : connexion
    private function updateUser($id, $connexion = 0)
    {
        $date = time();
        $addReq = ($connexion == 1) ? ", `date_last_connexion` = '$date'" : "";
        $q = $this->db->prepare(
            "UPDATE ".$this->user_table_name." SET `date_last_action` = '$date'$addReq WHERE `id` = '$id'"
        );
        $q->execute();
    }
}
