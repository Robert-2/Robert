<?php
/*
 *
    Le Robert est un logiciel libre; vous pouvez le redistribuer et/ou
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


class Matos implements Iterator {

	const UPDATE_ERROR_DATA = 'donnée invalide' ;					// erreur si une donnée ne correspond pas
	const UPDATE_OK			= 'donnée modifiée, OK !' ;				// message si une modif BDD a réussi
	const INFO_ERREUR		= 'Impossible de lire les infos en BDD';// erreur de la méthode Infos::loadInfos()
	const INFO_DONT_EXIST	= 'donnée inexistante' ;				// erreur si champs inexistant dans BDD lors de récup ou update d'info
	const INFO_FORBIDDEN	= 'donnée interdite' ;					// erreur si info est une donnée sensible
	const REF_MANQUE		= 'Il manque la référence !';			// erreur si la référence du matos n'est pas renseignée au __construct
	const MANQUE_INFO		= 'Pas assez d\'info pour sauvegarder';	// erreur si il manque des infos lors de la sauvegarde

	const MATOS_OK          = true ;					// retour général, si la fonction a marché
	const MATOS_ERROR       = false ;					// retour général, si la fonction n'a pas marché

	const REF_MATOS			= 'ref';					// champ BDD où trouver la référence du matos
	const ID_MATOS			= 'id';						// champ BDD où trouver l'id du matos

	private $infos  ;					// instance de Infos (pour récup et update)
	private $id;						// ID (ou autre champ BDD) du matos à construire
	private $baseInfo;					// tableau des infos au construct, pour comparaison lors d'un update

	public function __construct ($champ='new', $id='') {
		$this->infos = new Infos( TABLE_MATOS ) ;						// Création de l'instance de 'Infos'
		if ( $champ == 'new' ) return 1 ;
		if ( $id == '' ) throw new Exception(Matos::REF_MANQUE) ; ;
		$this->id = $id;
		$this->loadFromBD( $champ, $this->id ) ;						// Récupération des données en BDD
	}


	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$this->baseInfo = $this->infos->getInfo();
		}
		catch (Exception $e) { throw new Exception(Matos::INFO_ERREUR) ; }
	}


	public function getMatosInfos ($what='') {
		if ($what == '') {
			try { $info = $this->infos->getInfo(); }					// Récup toutes les infos
			catch (Exception $e) { return $e->getMessage(); }
		}
		else {
			try { $info = $this->infos->getInfo($what); }				// Récup une seule info
			catch (Exception $e) { return $e->getMessage(); }			// Si existe pas, récup de l'erreur
		}
		return $info;
	}


	public function setVals ($arrKeysVals) {							// (re)définit les infos du matos
		foreach ($arrKeysVals as $key => $val)
			$this->infos->addInfo ($key, $val);
	}


	public function updateMatos ($typeInfo = false, $newInfo = false) {
		if ($typeInfo !== false && $newInfo !== false) {				// Si on spécifie une clé/valeur, on update que celle-ci
			try { $this->infos->update($typeInfo, $newInfo); return "Mise à jour de $typeInfo effectuée !"; }
			catch (Exception $e) { return $e->getMessage(); }
		}
		else {															// Sinon, on compare les nouvelles valeurs avec les anciennes
			$retour = ''; $newInfos = $this->infos->getInfo();
			$diffInfos = array_diff_assoc($newInfos, $this->baseInfo);  // retourne un tableau ne contenant que la différence
			foreach ($diffInfos as $key => $val) {						// effectue l'update seulement pour les champs qui sont différents
				if ($key == 'id') continue;								// souf pour ID, qui est en auto-increment
				try { $this->infos->update($key, $val); $retour .= "Mise à jour de $key effectuée !<br />"; }
				catch (Exception $e) { return $e->getMessage(); }
			}
			return $retour;
		}
	}


	public function save () {											// Sauvegarde d'un NOUVEAU MATOS
		$verifInfo = $this->infos->getInfo();							// Check si on a bien tout ce qu'il faut avant de sauvegarder en BDD
		if ( !$verifInfo['label'] || !$verifInfo['ref'] || !$verifInfo['Qtotale'] || !$verifInfo['tarifLoc'] || !$verifInfo['categorie'] || $verifInfo['valRemp'] == '')
			throw new Exception (Matos::MANQUE_INFO) ;

		$this->infos->save()  ;
		return Matos::MATOS_OK ;
	}


	public function deleteMatos () {
		$nb = $this->infos->delete( Matos::ID_MATOS, $this->id ) ;
		return $nb ;
	}


	public function current() { return $this->infos->current(); }
	public function next()	  {	$this->infos->next() ;  	}
	public function rewind()  { $this->infos->rewind() ; }
	public function valid()   { if ( $this->infos->valid() === false  ) return false ; else return true ; }
	public function key()     { return $this->infos->key(); }
}


?>
