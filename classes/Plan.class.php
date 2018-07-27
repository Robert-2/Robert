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


require_once 'initInclude.php' ;
require_once 'date_fr.php';

class Plan implements Iterator {

	const PLAN_cID           = 'id';
	const PLAN_cDATESTART    = 'date_start';
	const PLAN_cDATEEND      = 'date_end';
	const PLAN_cGROUPID	     = 'idGroup';
	const PLAN_cTITRE        = 'titre';
	const PLAN_cLIEU         = 'lieu';
	const PLAN_cCREATEUR     = 'createur';
	const PLAN_cBENEFICIAIRE = 'beneficiaire';
	const PLAN_cTECKOS       = 'techniciens';
	const PLAN_cMATOS		 = 'materiel';
	const PLAN_cCONFIRM		 = 'confirm';

	const PLAN_cDETAILS_ID      = 'id_plandetails' ;
	const PLAN_cDETAILS_PGROUPID  = 'id_plan' ;
	const PLAN_cDETAILS_SUBDATE = 'jour' ;
	const PLAN_cDETAILS_TECKOS  = 'techniciens' ;
	const PLAN_cDETAILS_MATOS   = 'materiel' ;
	const PLAN_cDETAILS_COMMENT = 'details_remarque' ;

	const PLAN_ERROR_INVALID_DATE        = 'Format de date non valide';
	const PLAN_ERROR_INVALID_INTERVALLE  = 'Date de fin avant date de debut ou inversement';

	const PLAN_PATHCONTENU		= 'PLANS_contenu/';	// chemin relatif à la racine !!!

	const PLAN_WARING_NOSUBPLAN          = 'Aucune information de détail de plan' ;

	private $loadedFromBDD ;

	private $infos ;
	private $hide_datas;

	private $sousPlans ;
	private $ssPlanIndex ;		// reproduit un foreach sur les sousPlan // ici la variable Index

	private $tekos ;
	private $tekosIndex ;		// reproduit un foreach sur les tekos // ici la variable Index


	public function __construct () {
		$this->infos = new Infos( TABLE_PLANS ) ;
		$this->hide_datas = array('id','idGroup', Plan::PLAN_cTECKOS) ;
		$this->ssPlanIndex = -1 ;
		$this->tekosIndex = -1 ;
		$this->loadedFromBDD = false ;
	}


	// charge les infos du plan et de ses sous plans associés
	public function load ($keyFilter , $value){
		$this->infos->loadInfos ( $keyFilter, $value );

		// charge l'idGroup pour charger les sous plans associés
		$groupid = $this->infos->getInfo (Plan::PLAN_cGROUPID );
		if ( $groupid == false ) return 0 ;
		$l = new Liste();
		$sousPlansList = $l->getListe(TABLE_PLANS_DETAILS , '*', Plan::PLAN_cDETAILS_ID, 'ASC',  Plan::PLAN_cDETAILS_PGROUPID , '=' , $groupid ) ;
		if ( $sousPlansList == false ) return ;
		unset($l);
		// charge les sous plans
		foreach( $sousPlansList as $v  ){

			try {
				$tmp = new Infos( TABLE_PLANS_DETAILS );
				$tmp->loadInfos( Plan::PLAN_cDETAILS_ID, $v[Plan::PLAN_cDETAILS_ID]);
				$this->sousPlans[] = $tmp ;
				// charge les tekos
				$tekosList = $tmp->getInfo( Plan::PLAN_cDETAILS_TECKOS ) ;
				if ( $tekosList ){
					$tekosArray = explode ( ' ' , $tekosList ) ;
					foreach ( $tekosArray as $idtek) {
						$this->loadTekosInfos($idtek);
					}
				}
				unset($tmp);
			}
			catch (Exception $e ){
				echo "Chargement de sous plan id :" . $v[Plan::PLAN_cDETAILS_PGROUPID] .' chargement impossible : ('. $e->getMessage() . " !! file = " . $e->getFile() .' :'.$e->getLine().")\n<br />" ;
				echo "traceBack : \n".$e->getTraceAsString()."\n\n";
				unset($tmp);
				continue ;
			}
		}
		$this->loadedFromBDD = true ;
	}


	// Ajoute une info en mémoire (sauf si c'est les dates -> traitement spécifique)
	public function addPlanInfo ( $k , $v) {
		if ( $k == Plan::PLAN_cDATESTART )	return ;
		if ( $k == Plan::PLAN_cDATEEND )	return ;
		if ( $k == Plan::PLAN_cCREATEUR )	{
			if (!is_numeric($v)) {
				$tmpUser = new Users();
				$tmpUser->loadFromBD(Users::USERS_PRENOM, $v);
				$v = $tmpUser->getUserInfos(Users::USERS_ID);
			}
		}

		// si la reservation est confirmée on enregistre le timestamp
		if ( $k == Plan::PLAN_cCONFIRM && $v == 1 ) $v = time();


		$this->infos->addInfo ( $k, $v );
	}


	// renvoie un tableau avec les ID des tekos du plan
	public function getTekosIds() {
		$list = $this->infos->getInfo( Plan::PLAN_cTECKOS ) ;
		if ( $list == false ) return false ;
		$list = explode(' ' , $list ) ;
		return $list ;
	}

	// getters de plan
	public function getPlanID ()		{ return ( $this->infos->getInfo( Plan::PLAN_cID )); }
	public function getPlanGroupID ()	{ return ( $this->infos->getInfo( Plan::PLAN_cGROUPID ) ); }
	public function getPlanTitre ()     { $tmp = $this->infos->getInfo( Plan::PLAN_cTITRE ) ; if ( ! $tmp  ) return null ; else return $tmp ;  }
	public function getPlanStartDate () { return ( $this->infos->getInfo( Plan::PLAN_cDATESTART )) ; }
	public function getPlanEndDate ()   { return ( $this->infos->getInfo( Plan::PLAN_cDATEEND )); }
	public function getPlanLieu ()      { return ( $this->infos->getInfo( Plan::PLAN_cLIEU )); }
	public function getPlanCreateur ($what = 'prenom')  {
		////////////////////////////////////////////////////////////// METHODE DE TRANSITION :
																	// On récupère l'ID du créateur, OU BIEN le prénom
																	// (selon ce qui a été enregistré)
																	// -> passage à l'enregistrement d'ID à partir de maintenant (27/12/12)
		$crea = $this->infos->getInfo( Plan::PLAN_cCREATEUR );		// @TODO : re-nettoyer en 2014
		if (is_numeric($crea)) {
			if ($what == 'id') return $crea;
			$tmpUser = new Users();
			$tmpUser->loadFromBD(Users::USERS_ID, $crea);
			return $tmpUser->getUserInfos(Users::USERS_PRENOM);
		}
		else {
			if ($what == 'prenom') return $crea;
			$tmpUser = new Users();
			$tmpUser->loadFromBD(Users::USERS_PRENOM, $crea);
			return $tmpUser->getUserInfos(Users::USERS_ID);
		}

//		return ( $this->infos->getInfo( Plan::PLAN_cCREATEUR ));	// Ligne a récupérer
	}
	public function getPlanBenef ()     { return ( $this->infos->getInfo( Plan::PLAN_cBENEFICIAIRE )); }
	public function getPlanMatos ()		{ return ( json_decode( $this->infos->getInfo( Plan::PLAN_cMATOS ), true) ); }
	public function getPlanMatosBrut ()	{ return ( $this->infos->getInfo( Plan::PLAN_cMATOS )); }
	public function getPlanTekos ()		{ return ( explode(' ', $this->infos->getInfo( Plan::PLAN_cTECKOS )) ); }
	public function getPlanTekosBrut ()	{ return ( $this->infos->getInfo( Plan::PLAN_cTECKOS )); }
	public function getPlanConfirm ()   { return ( $this->infos->getInfo( Plan::PLAN_cCONFIRM )); }


	// définit la date de début du plan
	public function setDateStart ( $jour, $mois, $annee, $heure = 0, $minutes = 0 , $secondes = 0  ) {
		$timeStamp = @mktime($heure,$minutes,$secondes,$mois,$jour, $annee) ;
		if ( $timeStamp == false ) throw new Exception ( Plan::PLAN_ERROR_INVALID_DATE ) ;
		$this->infos->addInfo( Plan::PLAN_cDATESTART , $timeStamp ) ;
		if ( $this->infos->getInfo(Plan::PLAN_cDATEEND) == false  ) $this->setDateEnd( $jour, $mois, $annee );
		return true ;
	}

	// définit la date de fin du plan
	public function setDateEnd ( $jour, $mois, $annee, $heure = 23, $minutes = 59 , $secondes = 59  ) {
		$timeStamp = @mktime($heure,$minutes,$secondes,$mois,$jour, $annee)  ;
		if ( $timeStamp == false && $this->infos->getInfo( Plan::PLAN_cDATESTART ) == false ) throw new Exception ( "Date fin de plan : " . Plan::PLAN_ERROR_INVALID_DATE ) ;
		$this->infos->addInfo( Plan::PLAN_cDATEEND , $timeStamp ) ;
		$this->createSubPlans();
		return true ;
	}


	// sauvegarde le plan.
	public function save() {
		// verifie les infos obligatoires : date_start, date_end, et titre
		if ( $this->infos->getInfo( Plan::PLAN_cDATESTART ) == false ) throw new Exception ( "Date début de plan : " . Plan::PLAN_ERROR_INVALID_DATE ) ;
		if ( $this->infos->getInfo( Plan::PLAN_cDATEEND )   == false ) throw new Exception ( "Date fin de plan : " . Plan::PLAN_ERROR_INVALID_DATE ) ;
		if ( $this->infos->getInfo( Plan::PLAN_cTITRE )     == false ) throw new Exception ( "Titre de plan non valide !") ;

		if ( ! $this->loadedFromBDD ){
			// verifie l'existance d'un plan avec les meme 'infos' pour ne pas en enregistrer 2.
			$existEnreg = new Liste() ;
			$existEnreg->addFiltre('titre', '=',      $this->getPlanTitre() );
			$existEnreg->addFiltre('lieu', '=',       $this->getPlanLieu() );
			$existEnreg->addFiltre('date_start', '=', $this->getPlanStartDate() );
			$existEnreg->addFiltre('date_end', '=',   $this->getPlanEndDate() );
			$existPlan = $existEnreg->getListe( TABLE_PLANS, 'titre, lieu, date_start, date_end', 'titre');
			if ( $existPlan != false ){ throw new Exception ( "Ce plan existe deja en base de donnée. Pour le modifier cliquez dessus sur le calendrier puis sur le bouton modifier.") ; }
		}

		try {
			$this->infos->save() ;									// sauve le plan
			foreach ( $this->sousPlans as $sp ) {					// sauve tous les sous plans
				$idSP = $sp->getInfo( Plan::PLAN_cDETAILS_ID );
//				echo "save $idSP" ;
				$sp->save(Plan::PLAN_cDETAILS_ID, $idSP) ;
			}
			return true ;
		}
		catch (Exception $e) { throw new Exception ( "Plans::save() -- ".$e->getMessage() ); }
	}


	// Supprime un plan, tout ses sous plans, son dossier de data et tout ses devis en BDD
	public function delete( $idPlan = 'this' ) {
		if ( $idPlan == 'this') $idPlan = $this->infos->getInfo( Plan::PLAN_cID );
		if ( $this->getNBSousPlans() ) {
			foreach ( $this->sousPlans as $v )
				$v->delete( Plan::PLAN_cDETAILS_ID );
		}
		$r = $this->infos->delete() ;
		if ($r > 0) {
			Devis::deleteAllDevisBDD($idPlan);
			rrmdir(INSTALL_PATH.FOLDER_PLANS_DATAS.$idPlan.'/');
		}
		return $r ;
	}

	// force l'état à chargé ou pas
	public function forceLoaded ($etat){ $this->infos->forceLoaded($etat); }


//////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////          METHODES SOUS PLANS                /////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

	//Decoupe un évènement de plusieurs jours en plusieurs évènements d'un jour avec la meme valeur d'idGroup
	public function createSubPlans() {
		// step entre chaque sous plan : 24h
		$stampJour = 3600 * 24  ;
		// récupère les stamp de début et de fin du plan, ajoute 12h (= midi) pour les sous plan
		$SPstamp  = $this->infos->getInfo( Plan::PLAN_cDATESTART ) + 3600 * 12 ;
		$endStamp = $this->infos->getInfo( Plan::PLAN_cDATEEND ) ;

		if (isset ($this->sousPlans) )unset ($this->sousPlans) ;
		// trouve le prochain idGroup disponible et le retient
		// ou charge lid group existant.
		$idGroupMax = $this->infos->getInfo( Plan::PLAN_cGROUPID );
		if ( ( $idGroupMax ) == false  ){
			$idGroupMax = Liste::getMax(TABLE_PLANS, Plan::PLAN_cGROUPID) +1 ;
			$this->infos->addInfo( Plan::PLAN_cGROUPID, $idGroupMax );
		}

		// met en mémoire les valeurs tekos et matos du plan (= par défaut)
		$tekIds = $this->infos->getInfo( Plan::PLAN_cTECKOS ) ;
		$matosList = $this->infos->getInfo( Plan::PLAN_cMATOS );

		// pour chaque jour compris entre plan_start et plan_end, ajoute au tableau des sousPlans
		while ( $SPstamp < $endStamp ) {
			$sPlan = new Infos( TABLE_PLANS_DETAILS );
			$sPlan->addInfo ( Plan::PLAN_cDETAILS_PGROUPID, $idGroupMax) ;
			$sPlan->addInfo ( Plan::PLAN_cDETAILS_SUBDATE,  $SPstamp ) ;
			$sPlan->addInfo ( Plan::PLAN_cDETAILS_TECKOS ,  $tekIds ) ;
			$sPlan->addInfo ( Plan::PLAN_cDETAILS_MATOS ,   $matosList ) ;
			$this->sousPlans[] = $sPlan ;
			unset ( $sPlan ) ;
			$SPstamp += $stampJour;
		}
	}


	// supprime tout les sous plans
	public function purgeSousPlans () {
		if (!isset($this->sousPlans) && empty($this->sousPlans)) return;
		foreach ($this->sousPlans as $sp) {
			$sp->delete( Plan::PLAN_cDETAILS_ID );
		}
	}


	// permet de tester l'existence d'un sous plan en utilisant une boucle while
	public function whileTestSousPlan (){
		// si aucun sous plan n'est chargé on quitte
		if ( empty ($this->sousPlans) ) return false ;
		// reset automatique à chaque nouvel appel de while
		if ( count( $this->sousPlans ) ==  $this->ssPlanIndex ) $this->ssPlanIndex = -1 ;
		// incrémentation de l'index
		$this->ssPlanIndex ++ ;
		if ( isset ($this->sousPlans[$this->ssPlanIndex] ) ) {
			return true ;
		}
		return false ;
	}

	// définit l'offset courant du tableau des sousPlans avec un id de sousPlan ($id = 'id_plandetails')
	public function setSousplanOffset ( $id ){
		if ( ! isset ($this->sousPlans) || ! is_array ($this->sousPlans) || empty($this->sousPlans) ) return false ;
		foreach ( $this->sousPlans as $i => $sp ) {
			if ( $sp->getInfo ( Plan::PLAN_cDETAILS_ID) == $id ) {
				$this->ssPlanIndex = $i ;
				return true;
			}
		}
		return false;
	}

	// définit l'offset courant du tableau des sousPlans avec un timestamp de sousPlan
	public function setSousplanOffsetByTimeStamp ( $timestamp ){
		if ( ! isset ($this->sousPlans) || ! is_array ($this->sousPlans) || empty($this->sousPlans) ) return false ;
		foreach ( $this->sousPlans as $i => $sp ) {
			if ( $sp->getInfo ( Plan::PLAN_cDETAILS_SUBDATE ) == $timestamp ) {
				$this->ssPlanIndex = $i ;
				return true;
			}
		}
		return false;
	}

	// retourne le nombre de sous plans associés au plan
	public function getNBSousPlans () {
		if ( !isset($this->sousPlans) ) return 0 ;
		else return count ( $this->sousPlans ) ;
	}

	// retourne l'index du sous plan courant
	public function getSousPlanIndex ()	{ return $this->ssPlanIndex; }
	// retourne l'ID du sous plan courant
	public function getSousPlanId ()	{ return $this->sousPlans[$this->ssPlanIndex]->getInfo( Plan::PLAN_cDETAILS_ID ); }
	// retourne le jour (timeStamp) du sous plan courant
	public function getSousPlanDate ()	{ return $this->sousPlans[$this->ssPlanIndex]->getInfo( Plan::PLAN_cDETAILS_SUBDATE ); }
	// retourne la remarque associée au sous plan courant
	public function getSousPlanComment(){ return $this->sousPlans[$this->ssPlanIndex]->getInfo( Plan::PLAN_cDETAILS_COMMENT ); }
	// retourne un tableau des tekos du sous plan courant (index idSousPlan => tableau des idTekos
	public function getSousPlanTekos ()	{ return explode (" ", $this->sousPlans[$this->ssPlanIndex]->getInfo( Plan::PLAN_cDETAILS_TECKOS )) ; }
	// retourne un tableau du matos du sous plan courant (tableau à 2 dimensions : idSousPlan => [idMatos, quantité] )
	public function getSousPlanMatos ()	{ return json_decode($this->sousPlans[$this->ssPlanIndex]->getInfo( Plan::PLAN_cDETAILS_MATOS ), true) ; }

	// défini l'id du sous plan (à utiliser seulement pour écrasement en bdd)
	public function setSousPlanId ($id)			  { $this->sousPlans[$this->ssPlanIndex]->addInfo( Plan::PLAN_cDETAILS_ID, $id ); }
	// défini le group_id du sous plan
	public function setSousPlanGroupId ($id)	  { $this->sousPlans[$this->ssPlanIndex]->addInfo( Plan::PLAN_cDETAILS_PGROUPID, $id ); }
	// défini la remarque du sous plan courant
	public function setSousPlanComment ($comment) { $this->sousPlans[$this->ssPlanIndex]->addInfo( Plan::PLAN_cDETAILS_COMMENT, $comment   ); }
	// défini la liste des tekos du sous plan courant (liste d'IDs séparés par des ' ')
	public function setSousPlanTekos ($tekosList) { $this->sousPlans[$this->ssPlanIndex]->addInfo( Plan::PLAN_cDETAILS_TECKOS,  $tekosList ); }
	// défini la liste du matos du plan courant (tableau à 2 dimensions : idSousPlan => [idMatos, quantité] )
	public function setSousPlanMatos ($matosList) { $matos = json_encode($matosList); $this->sousPlans[$this->ssPlanIndex]->addInfo( Plan::PLAN_cDETAILS_MATOS, $matos ); }

	// sauvegarde le sous plan COURANT
	public function saveSousPlan () {
		try {
			$id_detail = $this->getSousPlanId();
			$this->sousPlans[$this->ssPlanIndex]->save(Plan::PLAN_cDETAILS_ID, $id_detail);
		}
		catch (Exception $e) { echo 'ERREUR save SousPlan : '. $e->getMessage(); }
	}


//////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////            METHODES TEKOS                   /////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

	private function loadTekosInfos ( $tekID ){
		// tekos deja chargé on quitte
		if ( isset ( $this->tekos[$tekID] ) ) return true ;

		// charge le tekos depuis la bdd ds une instance de Infos ou renvoie false
		try { $t = new Tekos( $tekID ) ; }
		catch ( Exception $e ) { return false ; }

		$this->tekos[$tekID] = $t ;
		return true ;

	}

	public function getTekosName ( $id ) { return $this->tekos[$id]->getTekosInfos( 'surnom' ); }


//////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////     METHODE FACTURE PDF     //////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////


	public function createFacture () {
		echo "Facture créée ! Téléchargez pour imprimer.";
		echo "Impossible de créer la facture...";
	}


//////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////     ITERATOR                   //////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////


	public function key()     { return $this->infos->key(); }
	public function current() { return $this->infos->current(); }
	public function next()	  {	$this->infos->next() ;  	}
	public function rewind()  { $this->infos->rewind() ; }
	public function valid()   {
		while ( $this->infos->valid() ) {
			if ( in_array(  $this->infos->key() , $this->hide_datas) )
				$this->infos->next() ;
			else return true ;
		}
		return false ;
	}
}

?>
