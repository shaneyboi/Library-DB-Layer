<?php
/**
* MySQL PDO Db Model. 
* TODO: Create an Interface and place this as a sub-class.
* 
* This Class will house how the UI will manipulate the local database system.
*   1. Ability to get the entire row selection.
*   2. Add the ability to delete the row.
*/
class Local
{

    /**
     * Protected Var: The constructor will automatically fill this var.
     * It shall hold the PDO Connection to the MySql Db. 
     **/
    protected $db_conn = '';

    /**
     * Protected Var: Stores the last query that was made.
     **/
	protected $last_query = '';

    /**
     * Public Var: Stores the id used in the mysql table. 
     **/
	public $id_field = 'fld_datetime';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'view_ic_all';

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
        $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_DB."", DB_U, DB_P);
    	if (!$dbh) {
        	die("NO MYSQL CONNECTION");
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

            case 'info':
                $tbl[0] = "tbl_item"; 
                $tbl[1] = "fld_barcode";
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

    public function getViaBarcode($record){
        $query = "SELECT *, COUNT(*) AS 'count' FROM $this->table_name WHERE fld_barcode = :fld_barcode GROUP BY DATE(fld_datetime), fld_barcode";

        $rs = $this->run($query ,$record);
        if($rs && count($rs)){
            return $rs;
        }
        return false;
    }

    /**
     *  Function that gets the result from id.
     *  @param - integer - $id - this should be the primary key number on the table. 
     *  @return array | false - returns an array of array which will only return the 1st row of the array. 
     * */
    public function get_via_timestamp($id){
        $this->change_tbl('view');
        $rs = $this->select("fld_datetime = :fld_datetime",'' , array("fld_datetime" => $id));
        if($rs && count($rs)){
            return $rs[0];
        }
        return false;
    }// get_via_timestamp()

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
            return $rs->fetchAll($this->fetch_style);
        }
        return false;
	} //select()

    /**
     * Deletes the selected row on the table.
     * @param - integer - the primary key of the row being selected.
     * @return - boolean - returns true if it has deleted the row, otherwise returns false.
     **/
    public function delete($id){
        $settings = array_merge(array(
            'from' => $this->table_name),$options);

        $query = "DELETE FROM {$settings['from']} WHERE $this->id_field = :$this->id_field";
        $rs = $this->run_query($query, array("$this->id_field" => (int)$id));
        if($rs) {return true;}
        return false;
    }

    /**
     * Same as run_query() but not protected. This skips the select phase.
     * @param - query - string - This is the query string to run on local db.
     * @param - array - bounded vairables - default: empty, contain the actual information that will be binded to the query statement.
     * @return - boolean/result - returns the result if there is any otherwise it will return false.
     **/
    public function run($query, array $bound_variables = array()){
        $rs = $this->run_query($query, $bound_variables);
        if ($rs) {
            return $rs->fetchAll($this->fetch_style);
        }
        return false;
    }

    /**
     * @param - array - record - This record should have the array of item informations to be stored.
     * @return - boolean - returns true if it correctly inserted.
     **/
    public function insert_description($record = array()){
        $query = "INSERT INTO tbl_item VALUES (:fld_barcode, :fld_title, :fld_author, :fld_callno)";
        return $this->run($query, $record);
    }

    /**
     * @param - array - record - This record should have the array of item informations to be stored.
     * @return - boolean - returns true if it is correctly inserted.
     **/
    public function insert($record = array()){
        $query="INSERT INTO tbl_item_scanned (fld_date_scan, fld_item_scan, fld_location_code) VALUES (:current_datetime, :item, :location)";
        return $this->run($query, $record);
    }

    /**
     * @parama - array - record  - This record shoud have the array of the item.
     * @return - boolean - returns true if it is correct inserted otherwise false.
     **/
    public function tally($record = array()){
        $query = "INSERT INTO tbl_item_nobarcode (fld_recorded, fld_stat_id) VALUE (:current_datetime, :locationId)";
        return $this->run($query, $record);
    }
    
    /** Get the tally data with information on it.
     * @param - date - ensure it is in the form of the mysql datetime.
     * @return - array of 1 result or false.
     **/
    public function getTallyInfo($date){
        $this->change_tbl('tally');
        $rs = $this->select("fld_recorded = :fld_recorded",'' , array("fld_recorded" => $date));
        if($rs && count($rs)){
            return $rs[0];
        }
        return false;
    }

    /**
     *  @param - array - record - This record should have the required dates to query the db.
     *  @return - boolean/result - returns false if none otherwise it returns the result queried from db.
     **/
    public function getStatByDate($type ,$record = array()){
        $this->change_tbl('table');
        $query = "SELECT ";
        $from_where = "FROM $this->table_name WHERE $this->id_field BETWEEN :start AND :end";
        $sum = ", SUM(IFNULL(AV,0)) AS 'Audio/Visual', SUM(IFNULL(Gen,0)) AS 'General', SUM(IFNULL(Hawn,0)) AS 'Hawaiian', SUM(IFNULL(Ref,0)) AS 'Reference', SUM(IFNULL(R_PB_NB,0)) AS 'Read, Paperback, and New Books', SUM(IFNULL(Maps,0)) AS 'Maps', SUM(IFNULL(CP,0)) As 'Current Periodicals', SUM(IFNULL(PBf,0)) AS 'Periodical Backfiles', SUM(IFNULL(Mfilmfiche,0)) AS 'Microfilm/Microfiche', SUM(IFNULL(Other,0)) AS 'Other', SUM(IFNULL(AV,0))+SUM(IFNULL(Gen,0))+SUM(IFNULL(Hawn,0))+SUM(IFNULL(Ref,0))+SUM(IFNULL(R_PB_NB,0))+SUM(IFNULL(Maps,0))+SUM(IFNULL(CP,0))+SUM(IFNULL(PBf,0))+SUM(IFNULL(Mfilmfiche,0))+SUM(IFNULL(Other,0)) AS Total ";
        $nosum =", IFNULL(AV,0) AS 'Audio/Visual', IFNULL(Gen,0) AS 'General', IFNULL(Hawn,0) AS 'Hawaiian', IFNULL(Ref,0) AS 'Reference', IFNULL(R_PB_NB,0) AS 'Read, Paperback, and New Books', IFNULL(Maps,0) AS 'Maps', IFNULL(CP, 0) As 'Current Periodicals', IFNULL(PBf,0) AS 'Periodical Backfiles', IFNULL(Mfilmfiche,0) AS 'Microfilm/Microfiche', IFNULL(Other,0) AS 'Other', IFNULL(AV,0)+IFNULL(Gen,0)+IFNULL(Hawn,0)+IFNULL(Ref,0)+IFNULL(R_PB_NB,0)+IFNULL(Maps,0)+IFNULL(CP,0)+IFNULL(PBf,0)+IFNULL(Mfilmfiche,0)+IFNULL(Other,0) AS Total ";
        if ($type == 'M') {
            $from_where .= " GROUP BY date";
            $query .= "date_format(the_date, '%b %Y') as 'date'".$sum.$from_where;
        }
        elseif ($type == 'Y') {
            $from_where .= " GROUP BY date";
            $query .= "date_format(the_date, '%Y') as 'date'".$sum.$from_where;
        }
        else{
            $query .= "date_format(the_date, '%a, %b %e %Y') as 'date'".$nosum.$from_where; 
        }
        
        $order_by = " ORDER BY $this->id_field";
        
        $rs = $this->run($query.$order_by , $record);
        if($rs){
            return $rs;
        }
        return false;
    }

    /**
     * @param - array - array must contain the the id field.
     * @return - array return the array of rows or false.
     **/
    public function getRows($record = array()){
        $query = "SELECT *, COUNT(*) AS 'count' FROM $this->table_name WHERE $this->id_field = :$this->id_field GROUP BY DATE(fld_datetime), fld_barcode";

        $rs = $this->run($query ,$record);
        if($rs && count($rs)){
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