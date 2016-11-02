<?php

/**
*   Pre-Condition: The Sensource and MSSQL Server must be running to query the db system.
*   Post-Condition: Class should be instantiated in the pages.
* 
*   Server Setting changes made to SQL SERVER:
*   - Added new user for this class to use. 
*   - Set MSSQL Server to NEVER SLEEP!
*/
class ExitCount
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
	public $id_field = 'CreateDate';

    /**
     * Public Var: Stores the table being used the in the mysql DB.
     **/
	public $table_name = 'v_vt_SensorDataView';

    /**
     * Fields this table shall be using.
     **/
    protected $fetch_fields = "ServerDate, CreateDate, ValueA, ValueB";

    /**
     * Constructor Function, Constructs the database conection via PDO.
     * @return none | A die insert.
     **/
	public function __construct()
	{
        $dbh = odbc_connect(DSN, USR, PWD);
    	if (!$dbh) {
    		die("NO OBDC CONNECTION");
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
		odbc_close($this->db_conn);
	}

    /**
     * Function that returns the current traffic today. 
     * @return - integer
     **/
    public function getToday(){
        $today = date('Y-m-d');
        $query = "SELECT SUM(ValueA) FROM v_vt_SensorDataView WHERE CONVERT(Date, CreateDate)='".$today."' GROUP BY CONVERT(Date, CreateDate)";
        $result = $this->run_query($query);

        return ceil((odbc_result($result, 1) / 2));
    }

    /**
     * Function that return the traffic of a specified date.
     * @param - $date(required) - date - This date must be in the form of YYYY-MM-DD.
     * @return - integer
     **/
    public function get($date){
        $query = "SELECT SUM(ValueA) FROM v_vt_SensorDataView WHERE CONVERT(Date, CreateDate)='".$date."' GROUP BY CONVERT(Date, CreateDate)";
        $result = $this->run_query($query);

        return ceil((odbc_result($result, 1) / 2));
    }

    /**
     * Function to return the sum of the traffic count between specified dates.
     * @param - $start(required) - date - This date must be in the form of YYYY-MM-DD.
     * @param - $end(required) - date - This date must be in the form of YYYY-MM-DD.
     * @return - integer.
     **/
    public function getSumBetween($start, $end){
        $sum = 0;
        $query = "SELECT SUM(ValueA) FROM v_vt_SensorDataView WHERE CONVERT(Date, CreateDate) BETWEEN '".$start."' AND '".$end."' GROUP BY CONVERT(Date, CreateDate) ORDER BY CONVERT(Date, CreateDate)";
        $result = $this->run_query($query);

        while(odbc_fetch_row($result)){
            $sum +=  odbc_result($result, 1);
        }

        return ceil($sum / 2);
    }

    /**
     * Function ro return the array of the traffic count between specified dates. 
     * @param - $start(required) - date - This date must be in the form of YYYY-MM-DD.
     * @param - $end(required) - date - This date must be in the form of YYYY-MM-DD.
     * @return - array of dates with integers.
     **/
    public function getBetween($start, $end){
        $query = "SELECT CONVERT(Date, CreateDate) AS 'The_Date', SUM(ValueA) AS 'Count' FROM v_vt_SensorDataView WHERE CONVERT(Date, CreateDate) BETWEEN '".$start."' AND '".$end."' GROUP BY CONVERT(Date, CreateDate) ORDER BY CONVERT(Date, CreateDate)";
        $result = $this->run_query($query);

        if($result){
           return $result; 
        }

        return false;
    }

    /**
     * Function to return the results of the processed query.
     * @param - $sql (required) - the SQL Statment to pe queried. MUST BE IN THE FORM OF MSSQL SYNTAX.
     * @return - array of results.
     **/
    protected function run_query($sql){
        $result = odbc_exec($this->db_conn, $sql);
        $this->last_query = $sql;
        return $result;
    }

}