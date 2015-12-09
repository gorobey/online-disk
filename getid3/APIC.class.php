<?php
/**
* An extension to ID3 which lets your write
* APIC raw data to an actual file. 
*
* @author Ronald Pompa <pompa ( at ) kth.se>
* @version 1.0
**/
class APIC extends ID3
{
	private $data;
	
	const DEBUG = false;
	
	/**
	* Calls on the parent class and the getFrame method with
	* an APIC frame to get the raw image data.
	*
	* @param $file_name		An mp3 file
	*/
	public function __construct($file_name)
	{
		parent::__construct($file_name);
		$this->data = parent::getFrame("APIC");
	}
	
	/**
	* Removes the pointer to $data field variable. 
	*
	*/
	public function __destruct()
	{
		$this->data = NULL;
	}
	
	/**
	* Writes the picture into a file.
	*
	* @param $destination	Where the file should be saved.
	* @param $picture_name	The name of the image file. Do not include an extension!
	*/
	public function writePicture($destination, $picture_name)
	{
		if (!$ext = $this->getExtension($this->data)) {
			if (self::DEBUG)
				printf("Could not get extension.");

			return false;
		}
		
		if (!$this->data = $this->removeExcessBytes($this->data)) {
			if (self::DEBUG)
				printf("Could not remove right amount of excess bytes.");

			return false;
		}
		
		if (!$im = imagecreatefromstring($this->data)) {
			if (self::DEBUG)
				printf("An error occured when trying to create image from string.");

			return false;
		}
				
		
		if (!$handle = fopen($destination . $picture_name . "." . $ext, "wb")) {
			if (self::DEBUG)
				printf("Could not open file.");
			
			return false;
		}
		
		if (fwrite($handle, $this->data) === FALSE) {
			if (self::DEBUG)
				printf("Could not write file.");
			
			return false;
    	}
		
		fclose($handle);
		
		return true;
	}
	
	/**
	* Retrieves the extension from the raw data
	*
	* @param $data	Raw APIC data retrieved by ID3.class.php
	*/
	private function getExtension($data)
	{
		if (preg_match("/jpeg/", $data)) {
			return "jpg";
		} 
		else if	(preg_match("/gif/", $data)) {
			return "gif";
		}
		else if (preg_match("/png/", $data) || preg_match("/x-png/", $data)) {
			return "png";
		}
		
		return false;
	}
	
	/**
	* Removes excess bytes from the raw data
	*
	* @param $data	Raw APIC data retrieved by ID3.class.php
	**/
	private function removeExcessBytes($data)
	{
		if (preg_match("/jpeg/", $data)) {
			return substr($data, 13);
		} 
		else if	(preg_match("/gif/", $data)) {
			return substr($data, 12);
		}
		else if (preg_match("/png/", $data)) {
			return substr($data, 12);
		} else if(preg_match("/x-png/", $data)) {
			return substr($data, 14);
		}
		
		return false;
	}
}
?>
