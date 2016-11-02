<?php
/**
* Voyager PDO Db Model. 
* TODO: Create an Interface and place this as a sub-class.
* 
* This Class will house how the UI will manipulate the voyager database system.
*   1. Ability to get the entire row selection.
*   2. Ability to get all rows if needed.
*   3. Be able to switch tables.
*/
class Voyager
{
    /**
     * Protected Var: The constructor will automatically fill this var.
     * It shall hold the PDO Connection to the Voyager Db. 
     **/
    protected $db_conn = '';

    /**
     * Protected Var: Stores the last query that was made.
     **/
	protected $last_query = '';

    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'barcode';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'item_vw';

    /**
     * Public Var: Stores the fetching Styles of the Database.
     **/
	public $fetch_style = PDO::FETCH_OBJ;

    /**
     * Constructor Function, Constructs the database conection via PDO.
     * @return none | A die insert.
     **/
	public function __construct()
	{
        $dbh = new PDO("oci:dbname=".VG_DB, VG_U, VG_P);
    	if (!$dbh) {
        	die("NO VOYAGER CONNECTION");
    	}
    	$this->db_conn = $dbh;
	}

	/**
	 *  Function that checks if their is an active connection to the database.
	 *  @return true | false
	 * */
	public function check_connection(){
		if ($this->db_conn) {return true;}
		return false;
	}

    /**
     * Destructor Function: Auto-called to destruct the connection of PDO. (Auto Called, No Return) 
     *      Terminates the PDO connection by setting it equal to NULL. 
     **/
	public function __destruct(){
		$this->db_conn = NULL;
	}

    //TODO: Figure out how to change tabling system. Should I develop sub-classes for each table?
    /**
     * Allows the table to be changed to query a different table.
     * @param - string - choose which table the user wishes to view.
     **/
    public function change_tbl($table){
        switch ($table) {
            case 'info':
                $tbl[0] = "BIB_TEXT INNER JOIN (BIB_ITEM INNER JOIN ITEM_VW ON BIB_ITEM.ITEM_ID=ITEM_VW.ITEM_ID) ON BIB_TEXT.BIB_ID=BIB_ITEM.BIB_ID"; 
                $tbl[1] = "BARCODE";
                break;

            case 'status':
                $tbl[0] = "ITEM_STATUS_TYPE INNER JOIN (ITEM_BARCODE INNER JOIN ITEM_STATUS ON ITEM_BARCODE.ITEM_ID = ITEM_STATUS.ITEM_ID) ON ITEM_STATUS_TYPE=ITEM_STATUS.ITEM_STATUS"; 
                $tbl[1] = "ITEM_BARCODE";
                break;
        }
        $this->table_name = $tbl[0];
        $this->id_field = $tbl[1];
    }//change_tbl()

    //TODO: Combine the get function by adding a switch to choose between id, last, query, table_name, and id_field.
    /**
     * Allows the return of the last-query that was done to the MySQL db.
     **/
    public function get_last_query(){return $this->last_query;}//get_last_query()

	/**
	 * 	Function that gets the result from id.
     *  @param - integer - $id - this should be the primary key number on the table. 
     *  @return array | false - returns an array of array which will only return the 1st row of the array. 
	 * */
	public function get($id){
		//$rs = $this->select($this->id_field.'='.(int)$id);
        $rs = $this->select("$this->id_field = :$this->id_field",'' , array("$this->id_field" => $id));
		if($rs && count($rs)){
			return $rs[0];
		}
		return false;
	}// get()

    /**
     * Returns all the rows, of the table in the database.
     **/
	public function getAll(){
        $rs = $this->select();
        if($rs){return $rs;}
        return false;
    }// getSelected()


    /**
     *  Instead of working with the query string directly. Use a function to build the SELECT SQL String.
     *  @param - string - $where - default: null, this shall contain the Where part of the SQL.
     *  @param - string - $order_by - default: null, this shall contain how the records will be orderd.
     *  @param - array - $options - default: empty, this shall contain the where actual information that shall be binded into the where statement.
     * */
	public function select($where = '', $order_by = '', array $options = array()){
		$settings = array_merge(array(
            'from'   => $this->table_name,
            'limit'  => '',
            'select' => '*',
        ), $options);
        $query    = "SELECT {$settings['select']} FROM {$settings['from']}";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order_by) {
            $query .= ' ORDER BY '.$order_by;
        }
        if ($settings['limit']) {
            $query .= ' LIMIT '.$settings['limit'];
        }

        $rs = $this->run_query($query, $options);
        if ($rs) {
            //var_dump($rs);
            return $rs->fetchAll($this->fetch_style);
        }
        return false;
	} //select()

    
    public function status($id){
        $this->change_tbl('status');
        $rs = $this->select("NOT(ITEM_STATUS = 1 OR ITEM_STATUS = 11) AND $this->id_field = :$this->id_field",'' , array("$this->id_field" => $id));
        $this->change_tbl('info');
        if ( $rs ) {
            return $rs;
        }
        return false;
    }
//======================================================================================

    /**
     *  This function actually queries the database, before it actually does the action, it shall output any errors to the sql and bind all information on the the $query string.
     *  @param - string - $query - the sql statement that will be queried.
     *  @param - array - $bound_variables - any parameters the the sql needs (normally in the where statement), the information is not directly injected on $query.
     *  @return - PDOobject - Returns the object of the query excutions which will have the results.
     **/
	protected function run_query($query, array $bound_variables = array()){
        
        $q      = $this->db_conn->prepare($query);
        //$return = $q->execute(); - Testing without bounded variables.
        $return = $q->execute($bound_variables);
        foreach ($bound_variables as $variable => $value) {
            //$query = preg_replace('/\\b'.$variable.'\\b/', $value, $query); // avoid replacing :contact in :contact_phone ---- unsure if this is the corect way to replace.
            $query = preg_replace('/:'.$variable.'/', $value, $query);
        }

        $this->last_query = $query;
        return $q;
    } //run_query()
	
}
?>