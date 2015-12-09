<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);

$_CONFIG['host'] = "localhost";
$_CONFIG['user'] = "user";
$_CONFIG['pass'] = "pass";
$_CONFIG['dbname'] = "db";

$version="2.6.30 beta";
empty($_SERVER['SHELL']) && die('shell only please');
require_once("data.php");
require_once("todelete.php");
function inArchive(){
	$initial_directory="/media/arc/";
	$DirectoriesToScan  = array($initial_directory);
	$DirectoriesScanned = array();
	$inArchive = array();
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
							$now = date('U');
							if(($ext=="mp3" || $ext=="ogg" || $ext=="oga" || $ext=="php") && filemtime($RealPathName) < ($now -60) ){
								$inArchive[] = $RealPathName;
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
	array_multisort(array_map( 'filemtime', $inArchive ), SORT_NUMERIC, SORT_DESC, $inArchive);
	array_reverse($inArchive);
	return $inArchive;
}
function inDB(){
	$queryinDB="SELECT id, patch, lastmod FROM tags ORDER BY id DESC";
	$files_added=mysql_query($queryinDB);
	$inDB = array();
	while($fileinDB = mysql_fetch_array($files_added)){
		$inDB[] = $fileinDB['patch'];
		$lastmod = date('U', strtotime($fileinDB['lastmod']));
		$now = date('U');
		if(!file_exists($fileinDB['patch']) && strpos($fileinDB['patch'], "http://") === false){
			$toDelete = "DELETE FROM tags WHERE id=".$fileinDB['id']." limit 1";
#			echo $toDelete."\n";
			mysql_query($toDelete) or die("delete error");
		}elseif(strpos($fileinDB['patch'], "http://") === false){
			if($lastmod < filemtime($fileinDB['patch']) && filemtime($fileinDB['patch']) < ($now -60) ){
				$ext = substr($fileinDB['patch'], -3);
				if($ext!='php'){
					$data = new mp3Data($fileinDB['patch']);
					$title = mysql_escape_string($data->getTitle());
					$artist = mysql_escape_string($data->getArtist());
					$album = mysql_escape_string($data->getAlbum());
					$genre = mysql_escape_string($data->getGenre());
					$playtime_seconds = mysql_escape_string($data->getPlaytime_seconds());
					$img = $data->getArt();
					if($img==""){
						$cover="";
					}else{
						$cover_file = str_replace(" ", "-", $album); // Replaces all spaces with hyphens.
						$cover_file = preg_replace('/[^A-Za-z0-9\-]/', '', $cover_file); // Removes special chars.
						$cover_file = preg_replace('/-+/', '-', $cover_file);// Replaces multiple hyphens with single one.
						$cover = $cover_file.".jpg";
						if(!file_exists("/var/www/system/library/cover/".$cover)){
							imagejpeg($img, "/var/www/system/library/cover/".$cover);
							imagedestroy($img);
						}
					}
				}elseif($ext=="php"){
					$patch = mysql_escape_string($toAdd);
					$title = array_reverse(explode("/", $fileinDB['patch']));
					$title = $title[0];
					$artist = '';
					$album = 'script';
					$genre = 'system';
					$playtime_seconds = '0';
					$cover = '';
				}
				$toUpdate='UPDATE tags SET
					title="'.$title.'",
					artist="'.$artist.'",
					album="'.$album.'",
					genre="'.$genre.'",
					playtime="'.$playtime_seconds.'",
					albumart="'.$cover.'",
					lastmod="'.date ("Y-m-d H:i:s",filemtime($fileinDB['patch'])).'"
					WHERE id="'.$fileinDB['id'].'"';
	#			echo $toUpdate."\n";
				mysql_query($toUpdate) or die("update error");
			}
		}else{
			continue;
		}
	}
	return $inDB;
}
function not_inDB(){
	$inDB = inDB();
	$inArchive = inArchive();
	$not_inDB = array_diff($inArchive, $inDB);
	foreach($not_inDB as $toAdd){
		$ext = substr($toAdd, -3);
		if($ext!='php'){
			$data = new mp3Data($toAdd);
			$patch = mysql_escape_string($toAdd);
			$title = mysql_escape_string($data->getTitle());
			$artist = mysql_escape_string($data->getArtist());
			$album = mysql_escape_string($data->getAlbum());
			$genre = mysql_escape_string($data->getGenre());
			$playtime_seconds = mysql_escape_string($data->getPlaytime_seconds());
			$img = $data->getArt();
			if($img=="" ){
				$cover="";
			}else{
				$cover = $album.".jpg";
				if(!file_exists("/var/www/system/library/cover/".$cover)){
					imagejpeg($img, "/var/www/system/library/cover/".$cover);
					imagedestroy($img);
				}
			}
		}elseif($ext=="php"){
			$patch = mysql_escape_string($toAdd);
			$title = array_reverse(explode("/", $toAdd));
			$title = $title[0];
			$artist = '';
			$album = 'script';
			$genre = 'system';
			$playtime_seconds = '0';
			$cover = '';
		}
		$toInsert="INSERT INTO tags (
			patch,
			title,
			artist,
			album,
			genre,
			playtime,
			albumart,
			lastmod
			)
			VALUE (
			'".$patch."',
			'".$title."',
			'".$artist."',
			'".$album."',
			'".$genre."',
			'".$playtime_seconds."',
			'".$cover."',
			'".date ("Y-m-d H:i:s",filemtime($toAdd))."'
			)";
#		echo $toInsert."\n";
		mysql_query($toInsert) or die("insert error");
	}
}
function load(){
not_inDB();
$optimize="OPTIMIZE TABLE `tags`";
mysql_query($optimize) or die("insert error");
}
load();

if($D=='1' && $H =='01' && $M=='00' && $S=='00'){
	toDelete();
}
