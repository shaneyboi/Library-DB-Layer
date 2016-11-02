<?php

namespace UHHiloLibrary\Voyager;

use UHHiloLibrary\DB\DBModel as DBModel;

/**
* Voyager PDO Db Model. 
* TODO: Create an Interface and place this as a sub-class.
* 
* This Class will house how the UI will manipulate the voyager database system.
*   1. Ability to get the entire row selection.
*   2. Ability to get all rows if needed.
*   3. Be able to switch tables.
*/
class Voyager extends DBModel
{
    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'barcode';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'item_vw';

    //TODO: Combine the get function by adding a switch to choose between id, last, query, table_name, and id_field.

	/**
	 * 	Function that gets the result from id.
     *  @param - integer - $id - this should be the primary key number on the table. 
     *  @return array | false - returns an array of array which will only return the 1st row of the array. 
	 * */
	public function get($id)
	{
		//$rs = $this->select($this->id_field.'='.(int)$id);
        $rs = $this->select("{$this->id_field} = :{$this->id_field}",'' , array("{$this->id_field}" => $id));
		if($rs && count($rs)){
			return $rs[0];
		}
		return false;
	}// get()
	
}
?>