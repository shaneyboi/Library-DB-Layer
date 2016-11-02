<?php

namespace UHHiloLibrary\Circulation;

/**
* 
* 
* This class houses all items that do not have a barcode.
*   These items include:
*    - Current Periodicals
*    - Periodical Backfiles
*    - Government Documents
*    - Newspaper, etc.
*   
*  Allows the ability to insert or get a record from the db.
* 
*/
class NoBarcode extends InternalCount
{
    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'fld_recorded';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'tbl_item_nobarcode INNER JOIN tbl_location_stat ON fld_stat_id=fld_location_num';

    /**
     *  Public Var: Records last id retrieved.
     **/
    public $last_id = '';

    /**
     * @parama - array - record  - This record shoud have the array of the item.
     * @return - boolean - returns true if it is correct inserted otherwise false.
    **/
    public function insert($record = array()){
        $query = "INSERT INTO {$this->table_name} (fld_recorded, fld_stat_id) VALUE (:current_datetime, :locationId)";
        $this->last_id = $record['current_datetime'];
        return $this->run($query, $record);
    } //insert()

    /** Get the tally data from the previous insert.
     * 
     * @return - array of 1 result or false.
     **/
    public function getTallyInfo(){
        $rs = $this->select("fld_recorded = :fld_recorded",'' , array("fld_recorded" => $this->last_id));
        if($rs && count($rs)){
            return $rs[0];
        }
        return false;
    }//getTallyInfo
}
?>