<?php

function autoload ($classname) {
	global $install_path;
	//echo $install_path; 
	if (file_exists ($file = $install_path.'classes/'.$classname.'.class.php'))

		require ($file);
}

spl_autoload_register ('autoload');

?>
