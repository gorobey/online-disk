<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);
	if (!class_exists("getID3") && file_exists(dirname(__FILE__)."/getid3/getid3.php")) { require_once(dirname(__FILE__)."/getid3/getid3.php"); }
	if (!class_exists("getID3")) { die("Missing Required library, getid3"); }
	// Requires getid3 - http://getid3.sourceforge.net/
	// Allows retrieval of MP3 ID3 information, including Album Artwork via this simple interface.
	// Sorry its not documented.
	// @version 0.2

	class mp3Data
	{
		protected $getid3;
		protected $analyzed = FALSE;
		public $info;
		protected $error;
		public function __construct($filename = NULL)
		{
			$this->getid3 = new getID3;
			$this->getid3->encoding = 'UTF-8';
			$this->error = array();
			if ($filename !== NULL) { $this->analyze($filename); }
		}
		public function analyze($filename)
		{
			if(!file_exists($filename)) { $this->setError("File Doesn't Exist!");return FALSE; }
			if($this->getid3->Analyze($filename))
			{
				getid3_lib::CopyTagsToComments($this->getid3->info);
			}
			$this->analyzed = TRUE;
			$this->info = $this->getid3->info;
			return TRUE;
		}
		public function getArt($id = 0)
		{
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (!isset($this->getid3->info["comments"]["picture"]["0"]["data"])) { $this->setError("No Attached Picture with ID $id");return FALSE; }
			$img = imagecreatefromstring($this->getid3->info["comments"]["picture"]["0"]["data"]);
			return $img;
		}
		public function getInfo()
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			return $this->info;
		}
		public function getRawInfo()
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			return $this->getid3->info;
		}
		public function getTitle($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["title"][$id])) {return $this->info["comments"]["title"][$id];}else{return $empty;}
		}
		public function getArtist($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["artist"][$id])) {return $this->info["comments"]["artist"][$id];}else{return $empty;}
		}
		public function getAlbum($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["album"][$id])) {return $this->info["comments"]["album"][$id];}else{return $empty;}
		}
		public function getYear($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["year"][$id])) {return $this->info["comments"]["year"][$id];}else{return $empty;}
		}
		public function getGenre($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["genre"][$id])) {return $this->info["comments"]["genre"][$id];}else{return $empty;}
		}
		public function getTrack($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"]["track"][$id])) {return $this->info["comments"]["track"][$id];}else{return $empty;}
		}
		public function getPlaytime_seconds()
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["playtime_seconds"])) {return round($this->info["playtime_seconds"]);}else{return $empty;}
		}
		public function getComment($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["comments"][$id])) {return $this->info["comments"][$id];}else{return $empty;}
		}
		public function getLyric($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["unsynchronised_lyric"][$id])) {return $this->info["unsynchronised_lyric"][$id];}else{return $empty;}
		}
		public function getLyrics()
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["unsynchronised_lyric"])) {return $this->info["unsynchronised_lyric"];}else{return $empty;}
		}
		public function getBand($id = 0)
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["band"][$id])) {return $this->info["band"][$id];}else{return $empty;}
		}
		public function getBands()
		{	$empty="";
			if (!$this->analyzed) { return $this->notAnalyzed(); }
			if (isset($this->info["band"])) {return $this->info["band"];}else{return $empty;}
		}
		private function notAnalyzed()
		{
			$this->setError("Not Analyzed");
			return FALSE;
		}
		protected function setError($string)
		{
			$this->error[] = $string;
		}
		public function getError()
		{
			if (!count($this->error)) { return FALSE; }
			return $this->error[count($this->error)-1];
		}
		public function getErrors()
		{
			if(!count($this->error)) { return FALSE; }
			return $this->error;
		}
	}

$data = new mp3Data("/media/arc/music/MP3/ABBA  -  Mamma Mia.mp3");

?>
