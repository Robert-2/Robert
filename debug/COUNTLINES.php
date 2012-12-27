<?php

$path = ('..');																// path to the root dir of the project
$type_files = array('php','js','html','css','py','sh');						// files accepted
$excludeDir = array('debug', 'BFlogs');										// dir to exclude


$counterPath = explode( '/', dirname($_SERVER["PHP_SELF"]));
$projectName = $counterPath[1];
$phpFiles = 0; $jsFiles = 0; $htmlFiles = 0; $cssFiles = 0; $pyFiles = 0; $shFiles = 0;
$phpLines = 0; $jsLines = 0; $htmlLines = 0; $cssLines = 0; $pyLines = 0; $shLines = 0;

function countLinesOfCode($path, $excludeDir = array(), $type_files = array()) {
	$rtrnExDirs = ''; $rtrnInDirs = '';
	global $phpLines; global $jsLines; global $htmlLines; global $cssLines; global $pyLines; global $shLines;
	global $phpFiles; global $jsFiles; global $htmlFiles; global $cssFiles; global $pyFiles; global $shFiles;

	$items = glob(rtrim($path, '/') . '/*');
	foreach($items as $item) {
		if (is_dir($item)) {
			if (in_array((pathinfo($item, PATHINFO_FILENAME )), $excludeDir, true)) {
				$rtrnExDirs .= '<li>'.$item .'</li>';
				continue;
			}
			$rtrnInDirs .= '<li>'.$item .'</li>';
			countLinesOfCode($item, $excludeDir, $type_files);
			continue;
		}
		elseif (is_file($item)) {
			$ext = pathinfo($item, PATHINFO_EXTENSION);
			if ($ext == 'htm') $ext = 'html' ;
			
			if (in_array($ext,$type_files,true)) {
				$var = $ext.'Files';
				$$var ++;
				
				$fileContents = file_get_contents($item);
				$totLineFile = substr_count($fileContents, PHP_EOL) + 1;
				
				if ($ext == 'php' || $ext == 'html' || $ext == 'htm') {
					$filePhpLine = 0; $filejsLine = 0;
					preg_match_all('/<\?(.*?)\?>/Uis', $fileContents, $phpMatches);
					foreach($phpMatches[0] as $matchPhp) {
						$filePhpLine += substr_count($matchPhp, PHP_EOL) + 1;
					}
					preg_match_all('/<script[^>]*>(.*)<\/script>/Uis', $fileContents, $jsMatches);
					foreach($jsMatches[0] as $matchJs) {
						$filejsLine += substr_count($matchJs, PHP_EOL);
					}
					$phpLines += $filePhpLine;
					$jsLines  += $filejsLine;
					
					$fileHtmLines = $totLineFile - $filePhpLine;
					$htmlLines += $fileHtmLines;
				}
				elseif ($ext == "js")
					$jsLines  += substr_count($fileContents, PHP_EOL);
				elseif ($ext == "css")
					$cssLines += substr_count($fileContents, PHP_EOL);
				elseif ($ext == "py")
					$pyLines  += substr_count($fileContents, PHP_EOL);
				elseif ($ext == "sh")
					$shLines  += substr_count($fileContents, PHP_EOL);
			}
		}
	}
	return array('includedDirs' => $rtrnInDirs,
				 'excludedDirs' => $rtrnExDirs,
				 'files' => array($phpFiles, $jsFiles, $htmlFiles, $cssFiles, $pyFiles, $shFiles),
				 'lines' => array($phpLines, $jsLines, $htmlLines, $cssLines, $pyLines, $shLines));
}

$countCode = countLinesOfCode($path,$excludeDir,$type_files);
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="robots" content="noindex,nofollow" />
</head>

<body>
	<h1>Analyse du code source du projet <u><?php echo $projectName; ?></u></h1>
	
	<div style="display: inline-block; vertical-align:top; padding-left:30px; font-size:1em; width:15%;">
		<?php 
		if ($countCode['includedDirs'] != '') {
			echo 'Répertoires INCLUS :
				<ul style="color:green;">
					'.$countCode['includedDirs'].'
				</ul>';
		}
		?>
	</div>
	<div style="display: inline-block; vertical-align:top; padding-left:30px; font-size:1em; width:15%;">
		<?php 
		if ($countCode['excludedDirs'] != '') {
			echo 'Répertoires EXCLUS :
				<ul style="color:red;">
					'.$countCode['excludedDirs'].'
				</ul>';
		}
		?>
	</div>
	<div style="display: inline-block; vertical-align:top; padding-left:30px; font-size:1em;">
		Résultats :
		<div style="font-size:1.5em;">
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			<br />
			<?php
				for($i = 0; $i < sizeof($type_files); $i++){
					$value = $type_files[$i];	
					echo "<div style='display:inline-block; width:100px;'><b>".strtoupper($value)."</b></div>
						<div style='display:inline-block; width:150px;'>".$countCode['files'][$i]." fichiers,</div>
						<div style='display:inline-block; width:150px;'>".$countCode['lines'][$i]." lignes</div><br />";
				}	
			?>
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		</div>
	</div>
</body>
</html>
