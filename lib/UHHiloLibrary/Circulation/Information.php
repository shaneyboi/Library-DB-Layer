<?php
namespace UHHiloLibrary\Circulation;

use PDO;

/**
* This class extends the Internal Count class.
* 
* 
* This Class houses the tbl_item table manipulation. 
*   1. Inserts the book information into this table record
*   2. Returns information when required.
*/
class Information extends InternalCount
{

	public $id_field = 'fld_barcode';

	public $table_name = 'tbl_item';

    /**
     * 
     * @param - array - record - This record should have the array of item informations to be stored.
     * @return - boolean - returns true if it correctly inserted.
     **/
    public function insert($record = array()){
        $query = "INSERT INTO {$this->table_name} VALUES (:fld_barcode, :fld_title, :fld_author, :fld_callno)";
        return $this->run($query, $record);
    }

    /**
     * Gets the information of the item.
     * @param - integer - Should be a UH Hilo Barcode Item.
     * @return - array return the array of rows or false.
     **/
    public function getInfo($barcode){
        $record = array("fld_barcode"=>$barcode);

        $query = "SELECT * FROM {$this->table_name} WHERE fld_barcode = :fld_barcode";

        $rs = $this->run($query ,$record);
        if($rs && count($rs)){
            return $rs;
        }
        return false;
    }   
}
?>