<?php

namespace UHHiloLibrary\Voyager;

/**
* Requires Voyager Class. 
* This class returns the status of 1 item.
*/
class Status extends Voyager
{
    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'ITEM_BARCODE';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'ITEM_STATUS_TYPE INNER JOIN (ITEM_BARCODE INNER JOIN ITEM_STATUS ON ITEM_BARCODE.ITEM_ID = ITEM_STATUS.ITEM_ID) ON ITEM_STATUS_TYPE=ITEM_STATUS.ITEM_STATUS';

    /**
    * @param: none. 
    * @return: returns an array of possible statuses on an item.
    **/
    public function getListOfStatus(){}

    /**
     * @param: $id - id - the barcode of the item.
     * @return: returns the array of statuses of the item. Otherwise return false.
     **/
	public function get($id){
		$rs = $this->select("NOT(ITEM_STATUS = 1 OR ITEM_STATUS = 11) AND $this->id_field = :$this->id_field",'' , array("$this->id_field" => $id));
        if ( $rs ) {
            return $rs;
        }
        return false;
	}// get()

    /**
     * @param: $itemInfo - The item's description. $status - The item's status. 
     * @return: 
     **/
    public function produceAlert($itemInfo = array(), $status = array())
    {
        $header = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h4><i class="icon fa fa-warning"></i> Alert - The item has a different status!</h4>';
        $content = "The following item \"".preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $itemInfo->TITLE_BRIEF)."\" with the barcode of $itemInfo->BARCODE has the status(es) listed below: <br/> <ol>";
        $footer = '</div>';
        foreach ($status as $key) {
            $content .= "<li>$key->ITEM_STATUS_DESC</li>";
        }
        $content .= "</ol>";
    }
}