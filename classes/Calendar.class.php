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

class Calendar implements Iterator, arrayaccess {

	const CAL_cSTART = 'date_start';
	const CAL_cEND = 'date_end';

	private $index_object ;
	private $index ;
	private $Plans ;
	private $sousPlans;
	private $calendarStart ;
	private $calendarEnd ;

	private $TekosBusy;
	private $MatosBusy;
	private $Packs;


	public function __construct (){
		$this->calendarEnd = -1 ;
		$this->calendarStart = -1 ;
		$this->sousPlans = array();
		$this->TekosBusy = array();
		$this->MatosBusy = array();
	}

	// lit les differents plans filtrés entre les timeStamp $CalendarStart $calendarEnd
	// et pour chaque crée un nouvel objet Infos //
	public function InitPlans ( $cS = -1 , $cE = -1, $excludePlanId = NULL ){
		$this->Plans = array() ;
		if ( $cS != -1 ) $this->calendarStart = $cS ;
		if ( $cE != -1 ) $this->calendarEnd = $cE ;
		if ($excludePlanId == NULL) $excludePlanId = 0;

		// recupère les plans concernés par les dates de debut et dates de fin
		$this->index_object = new Liste () ;
		if ( $this->calendarStart != -1 && $this->calendarEnd != -1){
			$this->index_object->setFiltreSQL ( "((`".Calendar::CAL_cSTART."` >= $this->calendarStart AND `".Calendar::CAL_cEND."` <= $this->calendarEnd)
											  OR ( `".Calendar::CAL_cEND."` >= $this->calendarStart AND `".Calendar::CAL_cSTART."` <= $this->calendarEnd ))
											  AND NOT ( `".Plan::PLAN_cID."` = $excludePlanId )" );
			$this->index = $this->index_object->getListe( TABLE_PLANS, Plan::PLAN_cID, Calendar::CAL_cSTART, 'ASC' );
		}
		elseif ( $this->calendarStart != -1 && $this->calendarEnd == -1){
			// on a une date de debut mais pas de date de fin -> affiche tt les plans > date de debut
			$this->index = $this->index_object->getListe ( TABLE_PLANS, Plan::PLAN_cID, 'id', 'ASC', Calendar::CAL_cSTART, '>=' , $this->calendarStart  );
		}
		elseif ( $this->calendarStart == -1 && $this->calendarEnd != -1){
			// on a une date de fin mais pas une date de debut -> affiche tt les plans jusqu'a date de fin
			$this->index = $this->index_object->getListe ( TABLE_PLANS, Plan::PLAN_cID, 'id', 'ASC', Calendar::CAL_cEND, '<=' , $this->calendarEnd  );
		}
		else
			$this->index = $this->index_object->getListe ( TABLE_PLANS, Plan::PLAN_cID);

		if ( ! $this->index  ) return 0 ;

		$nb = 0 ;
		foreach ( $this->index as $k => $v ){
			try {
				$tmpPlan = new Plan ( TABLE_PLANS ) ;
				$tmpPlan->load( Plan::PLAN_cID, $v ) ;
			}
			catch (Exception $e){
				unset ($tmpPlan) ;
				continue ;
			}
			$this->Plans[$nb] = $tmpPlan ;
			$this->createMatosBusy($nb);
			$nb ++ ;
			unset ($tmpPlan) ;
		}
		$this->initSousPlans();
		return $nb ;
	}



	// retourne un tableau des differents ID de plans retournés par InitPlans //
	public function getIndexes (){
		if ( ! isset ($this->index) ) return false ;
		return $this->index ;
	}


	// initialise les tableaux des disponibilités,
	// en fonction des sous plans qui sont compris entre calendarStart ET calendarEnd
	// ET qui sont confirmés (donc pas de simples devis)
	private function initSousPlans () {
		foreach ($this->Plans as $p) {
			$planName = $p->getPlanTitre();
			while ($p->whileTestSousPlan() ) {
				$timeSp = $p->getSousPlanDate();
				$idSp   = $p->getSousPlanId()  ;
				$StampSousPlan = $p->getSousPlanDate();
				$teks = $p->getSousPlanTekos();
				if (!($timeSp <= $this->calendarStart) && !($timeSp >= $this->calendarEnd)) {
					$this->createTekosBusy ( $idSp, $teks, $StampSousPlan, $planName, $p->getPlanConfirm() );
				}
			}
		}
	}



	// cree un tableau avec index IdTekos => tableau des sousPlan et leurs détails
	private function createTekosBusy ( $sousPlanId , $tekosList, $stamp='', $titrePlan='', $confirm=0 ){
		$stamp = datefr(date('d-m-Y', $stamp)) ;
		foreach ( $tekosList as $tID ){
			$this->TekosBusy[$tID][] =  array(	 Plan::PLAN_cDETAILS_ID      => $sousPlanId,
												 Plan::PLAN_cDETAILS_SUBDATE => $stamp ,
												 Plan::PLAN_cTITRE           => $titrePlan,
												 'confirm' => $confirm) ;
		}

	}


	private function createMatosBusy( $index ){
		if ( !isset ($this->Plans[$index]) || empty ( $this->Plans[$index] ) ) return ;
		$matostmp  = $this->Plans[$index]->getPlanMatos() ;
		$ownerPlan = $this->Plans[$index]->getPlanCreateur() ;
		$titrePlan = $this->Plans[$index]->getPlanTitre() ;
		$confirm   = $this->Plans[$index]->getPlanConfirm();

		if (!is_array($matostmp)) return;

		foreach ( $matostmp as $id => $qte ){
			if ( ! isset ($this->MatosBusy[$id]) ) {
				$this->MatosBusy[$id]["QteConfirm"] = 0 ;
				$this->MatosBusy[$id]["QteAttente"] = 0 ;
			}
			$this->MatosBusy[$id]["planInfo"][$index]["ownerPlan"] = $ownerPlan;
			$this->MatosBusy[$id]["planInfo"][$index]["titrePlan"] = $titrePlan;
			$this->MatosBusy[$id]["planInfo"][$index]["confirmDate"] = $confirm;
			if ( $confirm ) {
				$this->MatosBusy[$id]["QteConfirm"] += $qte;
				$this->MatosBusy[$id]["planInfo"][$index]["qteC"] = $qte;
				$this->MatosBusy[$id]["planInfo"][$index]["qteA"] = 0;
			}
			else {
				$this->MatosBusy[$id]["QteAttente"] += $qte;
				$this->MatosBusy[$id]["planInfo"][$index]["qteA"] = $qte;
				$this->MatosBusy[$id]["planInfo"][$index]["qteC"] = 0;
			}
		}
	}


	// Retourne un tableau des techniciens, contenant un tableau des sousPlans sur lesquels il est pris
	public function checkTekosBusy ( $tekID ) {
		if ( !isset ( $this->TekosBusy[$tekID]) ) return false ;
		return $this->TekosBusy[$tekID];
	}
	// Retourne un tableau des matos, contenant un tableau des sousPlans sur lesquels il est pris
	public function checkMatosBusy ( $matID ) {
		if ( !isset ( $this->MatosBusy[$matID]) ) return false ;
		return $this->MatosBusy[$matID];
	}


	// Retourne un tableau des tekos qui sont pris à la date $stamp
	public function getTekosAtDate ($stamp = 'now') {
		if ( $stamp == 'now') $stamp = time() ;
		if ( $this->getNBPlans() == 0 ) return false ;

		$busyTekos = array();
		foreach ( $this->Plans as $ind => $tmpPlan ){
			$d = $tmpPlan->getPlanStartDate() ;
			$e = $tmpPlan->getPlanEndDate()  ;
			$t = $tmpPlan->getTekosIds();
			if ( $d <= $stamp && $e >= $stamp ) {
				if ($t !== false) $busyTekos[$ind] = $t;
			}
		}
		if (count ( $busyTekos >= 1 ) ) return $busyTekos ;
		else return null ;
	}


	// Retourne le nombre de plans contenus dans le calendrier
	public function getNBPlans () {
		if ( count ( $this->Plans ) == 0 ) return 0 ;
		return count ( $this->Plans ) ;
	}



	public function initPacks( $arrayPacks = NULL ){
		if ( $arrayPacks != NULL && is_array($arrayPacks) && ! empty( $arrayPacks) ) {
			$this->Packs = $arrayPacks ;
			foreach ( $this->Packs as $pack => $data) {
				$this->Packs[$pack]["detail"] = json_decode( $data["detail"], true ) ;
			}
		}
	}


// on passe a cette fonction l'id de chaque matos et sa quantité disponible
// elle remplit les packs
	public function createPack ( $idMatos, $QteDispo ){
		foreach ( $this->Packs as $id => $dataPack ){
			foreach( $dataPack["detail"] as $packDetailId => $qte ){
				if ( $idMatos == $packDetailId ){
					$this->Packs[$id]["Dispo"][$idMatos] = floor ( $QteDispo / $qte );
				}
				$this->Packs[$id]['qteMatDispo'][$idMatos] = $QteDispo ;
			}
		}
	}

	public function countPacks (){
		foreach ( $this->Packs as $pack => $datapack ){
			$packComplets = -1 ;
			if (is_array($datapack["Dispo"])) {
				foreach ( $datapack["Dispo"] as $qte ){
					if ( $packComplets == -1 ) $packComplets = $qte ;
					if ( $packComplets > $qte ) $packComplets = $qte ;
				}
			}
			if ( $packComplets < 0 ) $packComplets = 0 ;
			$this->Packs[$pack]["QTE"] = $packComplets ;
		}
		return $this->Packs;
	}

	// Méthodes Iterator et ArrayAccess
	public function current() { return $this->infos->current(); }
	public function next()	  {	$this->infos->next() ;  	}
	public function rewind()  { $this->infos->rewind() ; }
	public function valid()   { if ( $this->infos->valid() === false  ) return false ; else return true ; }
	public function key()     { return $this->infos->key(); }

    public function offsetExists($offset) { return isset($this->Plans[$offset]); }
    public function offsetUnset($offset) {  unset($this->Plans[$offset]); }
    public function offsetGet($offset) { return isset($this->Plans[$offset]) ? $this->Plans[$offset] : null; }
    public function offsetSet($offset, $value) {
		if ( ! get_class($value) == 'Plan' ) return false ;
		if ( is_null($offset) )
			$this->Plans[] = $value ;
		else
			$this->Plans[$offset] = $value ;
    }


	// Sécu
	public function __destruct (){
		if ( empty($this->Plans) ) return;
		foreach ( $this->Plans as $p ) { unset ($p); }
	}

}



?>
