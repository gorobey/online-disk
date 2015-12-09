<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

$version="0 alpha";
empty($_SERVER['SHELL']) && die('shell only please');
function toDelete(){
	$initial_directory="/media/stream_log/";
	$DirectoriesToScan  = array($initial_directory);
	$DirectoriesScanned = array();
	$toDelete = array();
	while (count($DirectoriesToScan) > 0){
		foreach ($DirectoriesToScan as $DirectoryKey => $startingdir){
			if ($dir = opendir($startingdir)){
				while (($file = readdir($dir)) !== false){
					if ($file != '.' && $file != '..' && $file != 'lost+found' && $file != '.Trash-1000' && $file != 'index.php'){
						$RealPathName = realpath($startingdir.'/'.$file);
						if (is_dir($RealPathName)){
							if (!in_array($RealPathName, $DirectoriesScanned)
							 && !in_array($RealPathName, $DirectoriesToScan)){
								$DirectoriesToScan[] = $RealPathName;
							}
						}elseif (is_file($RealPathName)){
							$ext = pathinfo($RealPathName, PATHINFO_EXTENSION);
							$_90 = date('U') - ((60*60*24)*91);
							if(($ext=="mp3" || $ext=="ogg" || $ext=="oga") && filemtime($RealPathName) < ($_90) ){
								$toDelete[] = $RealPathName;
							}
						}
					}
				}
				closedir($dir);
			}
			$DirectoriesScanned[] = $startingdir;
			unset($DirectoriesToScan[$DirectoryKey]);
		}
	}
	array_multisort(array_map( 'filemtime', $toDelete ), SORT_NUMERIC, SORT_DESC, $toDelete);
	array_reverse($toDelete);
	foreach ($toDelete as $toTrash){
		if(file_exists($toTrash)){
			@unlink($toTrash);
		}
	}	
}

toDelete();
?>
