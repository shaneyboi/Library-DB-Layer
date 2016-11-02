<?php

namespace UHHiloLibrary\Guide;

/**
* 
*/
class BarcodeGuide
{	
	protected $libraries = array(
		"Mookini Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "UH Hilo"),
		"Hamilton Library" => array( "prefix" => 1, "digits" => 13, "affliate" => "UH Manoa"),
		"Sinclair Library" => array( "prefix" => 1, "digits" => 13, "affliate" => "UH Manoa"),
		"UHWO Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "UH West Oahu"),
		"Leeward Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "LCC"),
		"Honolulu Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "HonCC"),
		"Windward Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "WinCC"),
		"Kapiolani Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "KapCC"),
		"Kauai Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "KCC"),
		"Maui Library" => array( "prefix" => 8, "digits" => 13, "affliate" => "MauiC"),
		"Law Library" => array( "prefix" => 1, "digits" => 13, "affliate" => "UH Manoa")

	);

	protected $current_library = $libraries["Mookini Library"];

	function __construct(argument)
	{
		# code...
	}

	/*
	* @param: $name - string - should be the index name of the libraries array.
	* @return: no return.
	*/
	public function switchLibrary($name)
	{
		$this->current_library = $libraries[$name];
	}//switchLibrary(0)

	public function getCurrentLibrary() 
	{
		foreach ($this->libraries as $key => $value) {
			if ($value == $this->current_library) {
				return $key;
			}
		}

		return false;
	}//getCurrentLibrary()

	public function getPrefix()
	{
		return $this->current_library["prefix"];
	}//gerPrefix()

	public function getMaxDigits()
	{
		return $this->current_library["digits"];
	}//getMaxDigits()

	/**
	 * @param: $item - barcode # of the item.
	 * @return: boolean - false if the barcode is incorrect, otherwise it returns true.
	 **/
	public function verifyBarcode($item)
	{
		if ( strlen($item) != $this->getMaxDigits() || substr($item, 0, 1) != $this->getPrefix() ) {
    		$message = "Item Barcode is incorrect, please scan item again. There may not be enough digits or it is not a UHH Library Item.";
    		return false
		} 
		return true;
	}

}