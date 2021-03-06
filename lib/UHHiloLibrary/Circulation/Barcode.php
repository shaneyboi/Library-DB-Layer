<?php

namespace UHHiloLibrary\Circulation;

class Barcode extends InternalCount
{

	public $id_field = 'fld_id';

	public $table_name = 'tbl_item_scanned';

	/**
     * @param - array - record - This record should have the array of item informations to be stored.
     * @return - boolean - returns true if it is correctly inserted.
     **/
    public function insert($record = array()){
        $query="INSERT INTO {$this->table_name} (fld_date_scan, fld_item_scan, fld_location_code) VALUES (:current_datetime, :item, :location)";
        return $this->run($query, $record);
    }
}