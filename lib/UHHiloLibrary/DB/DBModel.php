<?php
namespace UHHiloLibrary\DB;

use PDO;

require_once ('_voyager.php');

/**
* Basic DB Model Layer.
* 
* This Class will house how the UI will manipulate the voyager database system.
*   1. Ability to get the entire row selection.
*   2. Ability to get all rows if needed.
*   3. Be able to switch tables.
*/
class DBModel
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
	public $id_field = '';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = '';

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
        $dbh = new PDO("oci:dbname=".$dbname, $user, $pwd);
    	if (!$dbh) {
        	die("NO VOYAGER CONNECTION");
    	}
    	$this->db_conn = $dbh;
	}//__construct()

	/**
	 *  Function that checks if their is an active connection to the database.
	 *  @return true | false
	 * */
	public function checkConnection()
    {
		if ($this->db_conn) {return 'Working';}
		return false;
	}//checkConnection()

    /**
     * Destructor Function: Auto-called to destruct the connection of PDO. (Auto Called, No Return) 
     *      Terminates the PDO connection by setting it equal to NULL. 
     **/
	public function __destruct()
    {
		$this->db_conn = NULL;
	}

    /**
     * Allows the return of the last-query that was done to the MySQL db.
     **/
    public function getLastQuery()
    {
        return $this->last_query;
    }//getLastQuery()


    /**
     * Returns all the rows, of the table in the database.
     **/
	public function getAll()
    {
        $rs = $this->select();
        if($rs){return $rs;}
        return false;
    }//getAll()

    /**
     *  Instead of working with the query string directly. Use a function to build the SELECT SQL String.
     *  @param - string - $where - default: null, this shall contain the Where part of the SQL.
     *  @param - string - $order_by - default: null, this shall contain how the records will be orderd.
     *  @param - array - $options - default: empty, this shall contain the where actual information that shall be binded into the where statement.
     * */
	public function select($where = '', $order_by = '', array $options = array())
    {
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

//======================================================================================

    /**
     *  This function actually queries the database, before it actually does the action, it shall output any errors to the sql and bind all information on the the $query string.
     *  @param - string - $query - the sql statement that will be queried.
     *  @param - array - $bound_variables - any parameters the the sql needs (normally in the where statement), the information is not directly injected on $query.
     *  @return - PDOobject - Returns the object of the query excutions which will have the results.
     **/
	protected function run_query($query, array $bound_variables = array())
    {
        
        $q      = $this->db_conn->prepare($query);
        //$return = $q->execute(); - Testing without bounded variables.
        $return = $q->execute($bound_variables);
        foreach ($bound_variables as $variable => $value) {
            //$query = preg_replace('/\\b'.$variable.'\\b/', $value, $query); // avoid replacing :contact in :contact_phone ---- unsure if this is the corect way to replace.
            $query = preg_replace('/:'.$variable.'/', $value, $query);
        }

        echo $this->last_query = $query;
        return $q;
    } //run_query()
	
}
?>