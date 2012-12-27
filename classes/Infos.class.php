<?php

require_once ($install_path . FOLDER_CONFIG  . 'common.inc' );
global $bdd;
class Infos implements Iterator {
	
	const ALL_DATAS			= '*' ;									// truc par défaut pour la récup de toutes les données
	const UPDATE_OK			= true;									// si un update a fonctionné, on renvoie cela
	const UPDATE_ERROR		= 'erreur SQL lors de la modif !';		// erreur, si un update n'a pas fonctionné
	const NO_INFO			= "Pas d'enregistrement";				// erreur, si aucun enregistrement trouvé dans la table
	const FILTRE_NON_UNIQUE	= "Choix du filtre dangereux : NON UNIQUE en BDD !";
	
	
	private $bddCx		;	// instance de PDO
	private $table      ;	// table de la BDD où travailler
	private $filtre     ;	// nom de la colonne de la table, pour la recherche
	private $filtre_key ;	// valeur à rechercher

	private $datas      ;	// tableau clé/valeur de tous les champs de la table
	private $loaded     ;   // definit si la BDD est lue (update ou insert -> cf. méthode save() )
	
	
	public function __construct( $table ) {
		$this->loaded = false ;
		$this->table = $table ;
		$this->datas = array();
	}
	
	
	// destruction de l'instance PDO
	public function __destruct () {
		$this->bddCx = null;
	}
	
	
	// définition de l'objet PDO si pas encore en mémoire
	private function initPDO () {
		$this->bddCx = null;
		$this->bddCx = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => true));
		$this->bddCx->query("SET NAMES 'utf8'");
		$this->bddCx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	
	// Charge toutes les infos de l'enregistrement en mémoire
	public function loadInfos ($filtre, $filtre_key ) {
		$this->initPDO();
		$this->filtre      = addslashes($filtre);
		$this->filtre_key  = $filtre_key;
		
		$sqlReq = "SELECT * FROM `$this->table` WHERE `$filtre` = '$filtre_key'";
		$q = $this->bddCx->prepare($sqlReq) ;
		$q->execute();
		$nbResults = $q->rowCount();
		// vérifie si un enregistrement a été trouvé
	    if ($nbResults == 0) { throw new Exception(Infos::NO_INFO) ; }
		elseif ($nbResults == 1) $result = $q->fetch(PDO::FETCH_ASSOC) ;
		elseif ($nbResults > 1) $result = $q->fetchAll(PDO::FETCH_ASSOC) ;
		foreach ( $result as $key => $val ){
			$this->addInfo ( $key, $val) ;
		}
		$this->loaded = true ;
		$this->bddCx = null;
	}
	
	// oblige à updater un champ lors de la sauvegarde au lieu de creer un enregistrement.				@OBSOLTETE : pas utilisé, mais conservé au cas ou
	public function forceLoaded ($etat){ $this->loaded = $etat ; }
	
	
	// Ajoute / modifie une info dans la mémoire
	public function addInfo ( $key , $val ){
		$this->datas[$key] = $val ;
	}
	
	
	// Compte le nombre d'infos en mémoire
	public function nbInfos (){
		return count ( $this->datas ) ; 
	}
	
	
	// Récupération d'info en mémoire
	public function getInfo ( $champ = Infos::ALL_DATAS ){
		if ( $champ == Infos::ALL_DATAS )       { return $this->datas ; }
		if ( ! isset ( $this->datas[$champ] ) ) { return false; }
		return $this->datas[$champ] ;
	}
	
	
	
	// MISE À JOUR d'un enregistrement dans la table courante
	public function save ( $filterKey = 'id', $filter='this' ) {
		$this->initPDO();
		// sauvegarde, si pas d'argument on va chercher l'id, si pas d'id dans les data, on met 0 pour éviter les 'Notices' (pas grave mais propre)
		if ( $filter == 'this') {
			if (isset($this->datas[$filterKey])) $filter = $this->datas[$filterKey];
			else $filter = 0 ;
		}
		// Vérifie si tous les champs existent, sinon crée le champ
		$this->updateBDD();
		// Construction de la chaine des clés et valeurs SQL pour la requête
		$keys   = ''; 	$vals   = '';  $up = '' ;
		foreach ( $this->datas as $k => $v ) {
			if ( is_array($v) ) continue ;	
			if ( is_string($v)) $v = addslashes($v);
			$keys .= "`$k`, " ;
			$vals .= "'$v', " ;
			$up   .= "$k='$v', ";
		}
		// suppression de la dernière virgule
		$keys = substr($keys, 0 , strlen($keys) -2 );
		$vals = substr($vals, 0 , strlen($vals) -2 );
		$up   = substr($up,   0 , strlen($up)   -2 );

		// Insertion ou Update de l'enregistrement
		if ( $this->loaded )
			$req = "UPDATE `$this->table` SET $up WHERE `$filterKey` LIKE '$filter'";
		else
			$req = "INSERT INTO `$this->table` ($keys) VALUES ($vals)";
//		echo $req;
		$q = $this->bddCx->prepare($req) ;
		try { $q->execute() ; }
		catch (Exception $e) {
			$msg = $e->getMessage();
			if ( strpos($msg, 'SQLSTATE[23000]: Integrity constraint violation',0 ) !== false  ){
				$keyOffset = strrpos( $msg, "'", -2) ;
				$key = substr( $msg, $keyOffset  );
				throw new Exception('ERREUR SQL de Infos::save() : Entree dupliquée ' . $key);
			}
				
			throw new Exception('ERREUR SQL de Infos::save() : ' . $e->getMessage());
		}
		
		$bad = $q->errorInfo() ;
		if ($bad[0] == 0 )
			return $req ;
		else
			throw new Exception('ERREUR SQL de Infos::save() : ' . $bad[2]) ;
	}


	// Lit tous les champs de la table, si une nouvelle valeur existe en mémoire on ajoute un champ
	private function updateBDD () {
		$this->initPDO();
		$q = $this->bddCx->prepare("SHOW COLUMNS FROM `$this->table`");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$colums = $q->fetchAll();
			foreach ( $this->datas as $k => $v ){			// Si nouvelle clé ds tableau, on ajoute un champ
				$exist = false ;
				foreach ( $colums as $c => $dataC ){
					if ( $k == $dataC["Field"]) { $exist = true ; break ; }
				}
				if (! $exist ) $this->addChamp ( $k, $v) ;
			}	
		}
	}
	
	// @OBSOLETE ? (enfin je crois) REMPLACÉ PAR SAVE() (à confirmer par Moutew)
	// MODIFICATEUR de donnée(s) dans la BDD (et, si le champs n'existe pas, création de la donnée)
	public function update ($row, $val) {
		if ($this->rowExist($row) == true) {
			if ($this->updateChamp($row, $val)) return Infos::UPDATE_OK ;
			else throw new Exception(Infos::UPDATE_ERROR . ' ::updateChamp() IMPOSSIBLE !') ;
		}
		else {
			if ($this->addChamp($row, $val) == true) {
				if ($this->updateChamp($row, $val)) return Infos::UPDATE_OK ;
				else throw new Exception(Infos::UPDATE_ERROR . ' ::updateChamp() IMPOSSIBLE !');
			}
			else throw new Exception(Infos::UPDATE_ERROR . ' ::addChamp() IMPOSSIBLE !');
		}
	}
	
	
	// Check si un champ existe dans la table courante
	private function rowExist ($row) {
		$this->initPDO();
		$sqlReq = "SELECT $row FROM `$this->table` WHERE `$this->filtre` = '$this->filtre_key'";
		$q = $this->bddCx->prepare($sqlReq);
		$q->execute();
		if ($q->rowCount() == 1) return true;
		else return false;
	}
	
	
	// Check si un champ est un index unique en BDD (CAD si le champ peut avoir plusieurs fois la même valeur)			// non utilisé pour le moment
	private function checkFiltreUnique ($champ) {
		$this->initPDO();
		$sqlReq = "SHOW INDEXES FROM ".$this->table ;
		$q = $this->bddCx->prepare($sqlReq);
		$q->execute();
		$result = $q->fetchAll(PDO::FETCH_ASSOC);
		$is_unique = false;
		foreach ($result as $index => $param) {
			if ($param['Column_name'] == $champ) {
				if ($param['Non_unique'] == 0) { $is_unique = true; break; }
			}
		}
		return $is_unique;
	}
	
	
	// Ajout d'un champ à la table courante
	private function addChamp ($row, $val) {
		$this->initPDO();
		if ((strpos('!', $val) !== false) && (strpos('\'', $val) !== false) && (strpos('?', $val) !== false) && (strpos('#', $val) !== false))
			return false;
//		echo "<p>ADD CHAMP $row -> $val </p>";
		$char = '' ;
		if (is_numeric($val)) {										// check du type de valeur du champ à ajouter
			$tailleNbre = strlen((string)$val);
			$tailleChamp = (int)$tailleNbre + 2;					// taille maxi de la valeur du champ
			if (ctype_digit($val))
				$typeRow = 'INT( '.$tailleChamp.' )';				// Si c'est un nombre entier
			else $typeRow = 'FLOAT( '.$tailleChamp.' )';			// Si c'est un nombre à virgule
		}
		elseif (is_string($val)) {
			$char = "CHARACTER SET utf8 COLLATE utf8_general_ci" ; 
			if (strlen($val) <= 64)
				$typeRow = 'VARCHAR(64)';							// Si c'est une petite chaîne
			else $typeRow = 'TEXT';									// Si c'est une grande chaîne
		}
		$sqlAlter = "ALTER TABLE `$this->table` ADD `$row` $typeRow $char NOT NULL" ;
		$a = $this->bddCx->prepare($sqlAlter);
		if ($a->execute()) return true; 
		else return false; 
	}
	
	
	// Mise à jour d'un champ dans la table courante
	private function updateChamp ($row, $val) {
		$this->initPDO();
		$sqlReq = "UPDATE `$this->table` SET `$row` = '$val' WHERE `$this->filtre` = '$this->filtre_key' " ;
		$q = $this->bddCx->prepare($sqlReq);
		if ($q->execute()) return true; 
		else return false; 
	}
	
	// Supprime une colonne d'une table de la base de données (fonction statique, peut être appellée sans créer d'instance de Infos)
	public static function removeChamp ($table = '', $row = '') {
		if ($table == '' && $row == '') return false;
		if ($row == 'id') return false;
		$pdoTmp = new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$pdoTmp->query("SET NAMES 'utf8'");
		$pdoTmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sqlReq = "ALTER TABLE `$table` DROP `$row`";
		$q = $pdoTmp->prepare($sqlReq);
		if ($q->execute()) {
			return true;
			unset($pdoTmp);
		}
		else {
			return false; 
			unset($pdoTmp);
		}
	}
	
	
	// efface un enregistrement de la BDD
	public function delete ( $filterKey = 'id', $filter='this', $filtrePlus = null ) {
		$this->initPDO();
		if ( $filter == 'this') $filter = $this->datas[$filterKey] ;
		
		$sqlReq = "DELETE FROM `$this->table` WHERE `$filterKey` = \"$filter\"";
		if ($filtrePlus != null) {
			$sqlReq .= " AND ".$filtrePlus;
		}
		$q = $this->bddCx->prepare( $sqlReq);
		$q->execute();
		$bad = $q->errorInfo() ;
		if ($bad[0] == 0 )
			return $q->rowCount() ; 
		else
			throw new Exception($bad[2]) ;
	}

	
	// Méthodes de l'iterator
	public function current() { return current ($this->datas); }
	public function key()     { return key ($this->datas) ; }
	public function next()	  {	next ( $this->datas );  	}
	public function rewind()  { reset ( $this->datas ); }
	public function valid()   { if ( current ($this->datas) === false  ) return false ; else 	return true ; }

	
}


?>
