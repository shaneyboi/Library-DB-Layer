<?php

/**
* Voyager PDO Db Model. 
* 
* 
* This Class will house how the UI will manipulate the voyager database system via Study Rooms Table.
*   1. Ability to get the entire row selection.
*   2. Ability to get all rows if needed.
*   3. Be able to switch tables.
*/

class StudyRoom
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
	public $id_field = 'value';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = "CIRCCHARGES_VW"; 

    /**
     * Public Var: Stores the fetching Styles of the Database.
     **/
	public $fetch_style = PDO::FETCH_OBJ;

	/**
	 *  Protected Var: Study Room ID. 
	 **/
	protected $SR = array( 3859169 => 219, 3859163 => 220, 3859167 => 314, 3859173 => 315 , 4409902 => 316, 3859164 => 320, 4420107 => 321, 3859155 => 332, 3859171 => 333, 4415211 => 364, 4421719 => 'Kit 320', 4421720=> 'Kit 321', 4421722=>'Kit 332', 4421723=>'Kit 333', 4420123=>'Kit 364');

	/**
	 *  Protected Var: Study Room Kit ID. 
	 **/
	protected $SRK = array( 320 => 4421719, 321 => 4421720, 332 => 4421722, 333 => 4421723, 364 => 4420123 );

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

    /**
     *  Return the array count of all study rooms and study room kit.
     *  @param - date - $date - requires the date in the form MM/DD/YYYY. 
     *  @return - array or false.
     **/
    public function get($date){
        $rs = $this->select(array("theDate"=>$date));

        if ($rs) {

            foreach ($this->SR as $key => $value) {
                $SR_Count[$value] = 0;
            }

            for ($i=0; $i < count($rs); $i++) { 
                $SR_Count[$this->SR[$rs[$i]->ITEM_ID]] = $rs[$i]->COUNT;
            }

            ksort($SR_Count);
            $SR = array();

            foreach ($SR_Count as $key => $value) {
                $temp['SR'] = $key;
                $temp['Count'] = $value;
                $SR[] = $temp;
            }
            return $SR;
        }
        
        return false;
    }

	/**
     *  Instead of working with the query string directly. Use a function to build the SELECT SQL String.
     *  @param - string - $where - default: null, this shall contain the Where part of the SQL.
     *  @param - string - $order_by - default: null, this shall contain how the records will be orderd.
     *  @param - array - $options - default: empty, this shall contain the where actual information that shall be binded into the where statement.
     * */
	public function select(array $options = array()){
		$settings = array_merge(array(
            'from'   => $this->table_name,
            'limit'  => '',
            'select' => 'ITEM_ID, COUNT(*) as COUNT',
        ), $options);

        $SR_ID = implode(", ",array_keys($this->SR));

        $query    = "SELECT {$settings['select']} FROM {$settings['from']} WHERE CHARGE_DATE_ONLY = to_date(:theDate,'MM/DD/YYYY') AND ITEM_ID IN ({$SR_ID}) GROUP BY ITEM_ID";
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
     *  This function actually queries the database, before it actually does the action, it shall output any errors to the sql and bind all information on the the $query string.
     *  @param - string - $query - the sql statement that will be queried.
     *  @param - array - $bound_variables - any parameters the the sql needs (normally in the where statement), the information is not directly injected on $query.
     *  @return - PDOobject - Returns the object of the query excutions which will have the results.
     **/
	protected function run_query($query, array $bound_variables = array()){
        
        $q      = $this->db_conn->prepare($query);
        //$return = $q->execute(); - Testing without bounded variables.
        $q->bindParam(':theDate', $bound_variables['theDate'], PDO::PARAM_STR);
        $q->bindParam(':value', $bound_variables['value'], PDO::PARAM_STR);
        $return = $q->execute();
        foreach ($bound_variables as $variable => $value) {
            //$query = preg_replace('/\\b'.$variable.'\\b/', $value, $query); // avoid replacing :contact in :contact_phone ---- unsure if this is the corect way to replace.
            $query = preg_replace('/:'.$variable.'/', $value, $query);
        }
        $this->last_query = $query;
        return $q;
    } //run_query()
}