<?php



class Permission {

	const PERM_HIDDEN   =   0 ;
	const PERM_VIEW     =   1 ;
	const PERM_UPDATE   =   4 ;
	const PERM_ADMIN    =   8 ;

	
	private $data ;

	public function __construct (){
		$data["user_password"] = 4 ;
		
	}

}


?>
