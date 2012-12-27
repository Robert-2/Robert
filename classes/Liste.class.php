<?php

class Liste {
	
	const TABLE_INEXIST		= 'La table n\'existe pas !' ;					// erreur si la table n'existe pas
	const FILTRE_IMPOSSIBLE = 'Impossible de filtrer selon ce champ' ;		// erreur si le champ n'existe pas
	
	private $bddCx;
	private $table;
	private $what;
	private $tri;
	private $ordre;
	private $filtre_key;
	private $filtre;
	private $isFiltred = false;
	
	private $filtres ;
	private $filtreSQL ;

	private $listResult ;
	
	public function __construct () {
		$this->bddCx	= new PDO(DSN, USER, PASS, array(PDO::ATTR_PERSISTENT => false));
		$this->bddCx->query("SET NAMES 'utf8'");
		$this->bddCx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$this->filtres = array() ; 
	}
	
	public function __destruct () {
		$this->bddCx = null;
	}
	
	public function getListe ($table, $want = '*', $tri='id', $ordre='ASC', $filtre_key = false, $filtre_comp = '=', $filtre = false) {
		$this->what = $want;
		
		if (Liste::check_table_exist ($table))
			$this->table	= $table;
		else return false ;

		// pour chaque entrée dans $this->filtres
		if ( isset ( $this->filtres) && ! empty ( $this->filtres ) ) {
			$filtrage_multiple = '' ; 
			foreach ( $this->filtres as $f ) {
				$filtrage_multiple .= "($f)" . ' AND ';
			}
			$filtrage_multiple = substr( $filtrage_multiple, 0 , strlen($filtrage_multiple) - strlen(' AND ') ); 
		}

		
		$this->tri		= $tri;
		$this->ordre	= $ordre;
		if ($filtre_key && $filtre) {
			if (Liste::check_col_exist($filtre_key)) {
				$this->isFiltred = true;
				$this->filtre_key = $filtre_key;
				$this->filtre	  = addSlashes($filtre);
			}
			else return false ;
		}
		if ($this->isFiltred)
			$q = "SELECT $this->what FROM `$this->table` WHERE `$this->filtre_key` $filtre_comp '$this->filtre' ORDER BY `$tri` $ordre";
		elseif ( isset($filtrage_multiple) )
			$q = "SELECT $this->what FROM `$this->table` WHERE $filtrage_multiple ORDER BY `$tri` $ordre";
		elseif ( isset ( $this->filtreSQL ) )
			$q = "SELECT $this->what FROM `$this->table` WHERE $this->filtreSQL ORDER BY `$tri` $ordre";
		else
			$q = "SELECT $this->what FROM `$this->table` ORDER BY `$this->tri` $this->ordre";

//		echo "REQUETE : $q<p></p>" ;
		$q = $this->bddCx->prepare ($q) ; 

		$q->execute();

		if ($q->rowCount() >= 1) {
			$result = $q->fetchAll(PDO::FETCH_ASSOC) ;
			$retour = array();
			if ( strpos($this->what, ',') == false && $this->what != '*') {
				foreach ($result as $resultOK)
					$retour[] = $resultOK[$this->what];
			}
			else {
				foreach ($result as $resultOK) {
					unset($resultOK['password']);
					$retour[] = $resultOK;
				}
			}
			$this->listResult = $retour ;
			return $retour;
		}
		else return false;
	}
	

	// ajoute une condition ( AND ) à la requete //
	public function addFiltre($filtre_key = false, $filtre_comp = '=', $filtre = false , $logique = ' AND '){
		$filtre = addslashes($filtre);
		$this->filtres[] = "`$filtre_key` $filtre_comp '$filtre'" ;
	}

	public function setFiltreSQL( $filtre ){
		$this->filtreSQL = $filtre ;
	}

	public static function getMax ( $table, $champ){

		global $bdd;
		
		$q = $bdd->prepare("SELECT `$champ` from `$table` WHERE `$champ` = (SELECT MAX($champ) FROM `$table`)");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$result = $q->fetch(PDO::FETCH_ASSOC);
			return $result[$champ];
		}
		else return false;
		
	}

	// retourne l'index du prochain auto increment //
	public function getAIval ($table) {
		global $bdd;
		
		$q = $bdd->prepare("SHOW TABLE STATUS LIKE '$table'");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$result = $q->fetch(PDO::FETCH_ASSOC);
			$AIval = $result['Auto_increment'];
			return $AIval;
		}
		else return false;
	}

	// renvoi un tableau trié de la liste ou l'index est $wantedInd //
	// au lieu d'un index 0,1,2 .... 
	public function simplifyList($wantedInd = null ) {
	if ($this->listResult == null || empty ($this->listResult) ) {
		return false ;
	}
	
	if ( $wantedInd == null ) $wantedInd = 'id' ;
	
	$newTableau = array();
	foreach( $this->listResult as $entry){
		$ind = $entry[$wantedInd];
		$newTableau[$ind] = $entry ;
	}
	return $newTableau ;

	}

	private function check_table_exist ($table) {
		$q = $this->bddCx->prepare("SHOW TABLES LIKE '$table'");
		$q->execute();
		if ($q->rowCount() >= 1)
			return true;
		else return false;
	}
	
	
	private function check_col_exist ($champ) {
		$q = $this->bddCx->prepare("SELECT $champ FROM `$this->table`");
		$q->execute();
		if ($q->rowCount() >= 1)
			return true;
		else return false;
	}
}

?>
