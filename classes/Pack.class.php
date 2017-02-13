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


class Pack implements Iterator {

	const UPDATE_ERROR_DATA = 'donnée invalide' ;					// erreur si une donnée ne correspond pas
	const UPDATE_OK			= 'donnée modifiée, OK !' ;				// message si une modif BDD a réussi
	const INFO_ERREUR		= 'Impossible de lire les infos en BDD';// erreur de la méthode Infos::loadInfos()
	const INFO_DONT_EXIST	= 'donnée inexistante' ;				// erreur si champs inexistant dans BDD lors de récup ou update d'info
	const INFO_FORBIDDEN	= 'donnée interdite' ;					// erreur si info est une donnée sensible
	const REF_MANQUE		= 'Il manque la référence !';			// erreur si la référence du pack n'est pas renseignée au __construct
	const MANQUE_INFO		= 'Pas assez d\'info pour sauvegarder';	// erreur si il manque des infos lors de la sauvegarde
	const MANQUE_MATOS		= 'Votre Pack ne contient pas de matériel au détail. Il n\'est pas sauvegardé';	// erreur si il manque des infos lors de la sauvegarde

	const PACK_OK          = true ;						// retour général, si la fonction a marché
	const PACK_ERROR       = false ;					// retour général, si la fonction n'a pas marché

	const REF_PACK			= 'ref';					// champ BDD où trouver la référence du pack
	const ID_PACK			= 'id';						// champ BDD où trouver la référence du pack

	private $infos  ;					// instance de Infos (pour récup et update)
	private $id;						// ID (ou autre champ BDD) du pack à construire
	private $baseInfo;					// tableau des infos au construct, pour comparaison lors d'un update
	private $basedetail;				// chaîne json du contenu du pack au construct, pour ajout / suppression de matos
	private $qtePackComplet;			// Nombre de packs que l'on peut avoir avec les quantités de son contenu, pour le matos dans le parc
	private $tarifPackComplet;			// Prix du pack selon les quantités de matos et leur prix au détail

	public function __construct ($champ='new', $id='') {
		$this->infos = new Infos( TABLE_PACKS ) ;						// Création de l'instance de 'Infos'
		if ( $champ == 'new' ) return 1 ;
		if ( $id == '' ) throw new Exception(Pack::REF_MANQUE) ; ;
		$this->id = $id;
		$this->loadFromBD( $champ, $this->id ) ;						// Récupération des données en BDD
	}


	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$this->baseInfo		= $this->infos->getInfo();
			$this->basedetail	= $this->infos->getInfo('detail');
		}
		catch (Exception $e) { throw new Exception(Pack::INFO_ERREUR) ; }
	}


	public function getPackInfos ($what='') {
		if ($what == '') {												// Récup toutes les infos
			try {
				$info = $this->baseInfo;
				$info['Qtotale']  = $this->countPacksInParc();
				$info['tarifLoc'] = $this->getTarifPack();
			}
			catch (Exception $e) { return $e->getMessage(); }
		}
		elseif ($what == 'Qtotale') {										// Récup de la qté totale possible
			$info = $this->countPacksInParc();
		}
		else {															// Récup une seule info
			try { $info = $this->infos->getInfo($what);	}
			catch (Exception $e) { return $e->getMessage(); }			// Si existe pas, récup de l'erreur
		}
		return $info;
	}


	public function setVals ($arrKeysVals) {							// (re)définit des infos du pack
		foreach ($arrKeysVals as $key => $val)
			$this->infos->addInfo ($key, $val);
	}


	public function addMatos ($ref, $qte) {								// Ajout d'un détail au pack
		$contenuBrut = $this->basedetail ;								// Récupère le contenu déjà en place
		$contenu		= json_decode($contenuBrut, true);
		$contenu[$ref]	= $qte;											// ajoute une entrée au tableau du contenu du pack
		$contenuOK		= json_encode($contenu);
		$retour = $this->updatePack('detail', $contenuOK);				// Sauvegarde en BDD du nouveau contenu
		return $retour;
	}


	public function modMatos ($ref, $qte) {								// Modification d'un détail du pack
		$contenuBrut = $this->basedetail ;								// Récupère le contenu déjà en place
		$contenu		= json_decode($contenuBrut, true);
		$contenu[$ref]	= $qte;											// modifie l'entrée du tableau du contenu du pack
		$contenuOK		= json_encode($contenu);
		$retour = $this->updatePack('detail', $contenuOK);				// Sauvegarde en BDD du nouveau contenu
		return $retour;
	}


	public function delMatos ($ref) {									// Suppression d'un détail du pack
		$contenuBrut = $this->basedetail ;								// Récupère le contenu déjà en place
		$contenu	= json_decode($contenuBrut, true);
		unset($contenu[$ref]);											// supprime l'entrée au tableau du contenu du pack
		$contenuOK	= json_encode($contenu);
		$retour = $this->updatePack('detail', $contenuOK);				// Sauvegarde en BDD du nouveau contenu
		return $retour;
	}


	public function updatePack ($typeInfo = false, $newInfo = false) {
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


	public function countPacksInParc () {
		$l = new Liste();
		$listeMatos = $l->getListe(TABLE_MATOS, 'id, panne, Qtotale', 'id');
		$contenuBrut = $this->basedetail ;
		$contenu = json_decode($contenuBrut, true);
		$nbPackComplet = 10000 ;
		foreach($contenu as $idM => $qteNeed) {
			foreach ($listeMatos as $matos) {
				if ($matos['id'] == $idM)
					$qteParc = $matos['Qtotale'];
			}
			$qtePossible = floor($qteParc / $qteNeed);
			if ($qtePossible < $nbPackComplet)
				$nbPackComplet = $qtePossible;
		}
		if ( $nbPackComplet < 0 ) $nbPackComplet = 0 ;
		$this->qtePackComplet = $nbPackComplet;
		return $this->qtePackComplet;
	}


	public function getTarifPack () {
		$l = new Liste();
		$listeMatos = $l->getListe(TABLE_MATOS, 'id, tarifLoc', 'id');
		$contenuBrut = $this->basedetail ;
		$contenu = json_decode($contenuBrut, true);
		$tarifPack = 0;
		foreach($contenu as $idM => $qteNeed) {
			foreach ($listeMatos as $matos) {
				if ($matos['id'] == $idM) {
					$tarifPack += $matos['tarifLoc'] * $qteNeed;
				}
			}
		}
		$this->tarifPackComplet = $tarifPack;
		return $this->tarifPackComplet;
	}


	public function save () {											// Sauvegarde d'un NOUVEAU PACK
		$verifInfo = $this->infos->getInfo();							// Check si on a bien tout ce qu'il faut avant de sauvegarder en BDD
		$nbMatosInPack = json_decode($verifInfo['detail'], true);

		if (count($nbMatosInPack) == 0){
			throw new Exception (Pack::MANQUE_MATOS) ;
		}
		/*}*/
		if ( !$verifInfo['label'] || !$verifInfo['ref'] || !$verifInfo['categorie'])
			throw new Exception (Pack::MANQUE_INFO) ;

		$this->infos->save()  ;
		return $nbMatosInPack ;
	}


	public function deletePack () {
		$nb = $this->infos->delete( Pack::ID_PACK , $this->id ) ;
		return $nb ;
	}


	public function current() { return $this->infos->current(); }
	public function next()	  {	$this->infos->next() ;  	}
	public function rewind()  { $this->infos->rewind() ; }
	public function valid()   { if ( $this->infos->valid() === false  ) return false ; else return true ; }
	public function key()     { return $this->infos->key(); }
}


?>
