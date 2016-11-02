<?php

namespace UHHiloLibrary\Circulation;

use PDO;

/**
* MySQL PDO Db Model. 
* TODO: Create an Interface and place this as a sub-class.
* 
* This Class will house how the UI will manipulate the local database system.
*   1. Ability to get the entire row selection.
*   2. Add the ability to delete the row.
*/
class Information extends InternalCount
{
    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'fld_datetime';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'tbl_item';

    /**
     * Allows the table to be changed to query a different table.
     * @param - string - choose which table the user wishes to view.
     **/
    public function change_tbl($table){
        switch ($table) {
            case 'collections':
                $tbl[0] = "tbl_location_stat"; 
                $tbl[1] = "fld_location_num";
                break;

            case 'view':
                $tbl[0] = "view_ic_all";
                $tbl[1] = "fld_datetime";
                break;

            case 'table':
                $tbl[0] = "view_circ_stats";
                $tbl[1] = "the_date";
                break;

            case 'tally':
                $tbl[0] = "tbl_item_nobarcode INNER JOIN tbl_location_stat ON fld_stat_id=fld_location_num";
                $tbl[1] = "fld_recorded";
                break;

            case 'scan':
            default:
                $tbl[0] = "tbl_item_scanned";
                $tbl[1] = "fld_id";
                break;
        }
        $this->table_name = $tbl[0];
        $this->id_field = $tbl[1];
    }//change_tbl()

    /**
     * @param - array - record - This record should have the array of item informations to be stored.
     * @return - boolean - returns true if it correctly inserted.
     **/
    public function insert($record = array()){
        $query = "INSERT INTO {$this->table_name} VALUES (:fld_barcode, :fld_title, :fld_author, :fld_callno)";
        return $this->run($query, $record);
    }

    /**
     * @param - array - array must contain the the id field.
     * @return - array return the array of rows or false.
     **/
    public function getRows($record = array()){
        $query = "SELECT *, COUNT(*) AS 'count' FROM {$this->table_name} WHERE fld_barcode = :fld_barcode GROUP BY DATE(fld_datetime), fld_barcode";

        $rs = $this->run($query ,$record);
        if($rs && count($rs)){
            return $rs;
        }
        return false;
    }//getRows()

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