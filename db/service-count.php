<?php
/**
 * MySQL PDO Db Model.
 * TODO: Create an Interface and place this as a sub-class.
 *
 * This Class will house how the UI will manipulate the local database system.
 *   1. Ability to get the entire row selection.
 *   2. Add the ability to delete the row.
 */
class ServiceCount
{
    private $DB_HOST = 'localhost';
    private $DB_DB = 'servicecount';
    private $DB_U = 'serviceuser';
    private $DB_P = 'sigma-chi';

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
    public $table_name = 'view_sc_all';

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
        $dbh = new PDO("mysql:host=localhost;dbname=servicecount", "serviceuser", "sigma-chi");
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
            case 'category':
                $tbl[0] = "tbl_category";
                $tbl[1] = "id";
                break;

            case 'view':
                $tbl[0] = "view_sc_all";
                $tbl[1] = "fld_datetime";
                break;

            case 'view-count':
                $tbl[0] = "view_sc_count_hr";
                $tbl[1] = "fld_datetime";
                break;

            case 'tally':
            default:
                $tbl[0] = "tbl_tally";
                $tbl[1] = "id";
                break;
        }
        $this->table_name = $tbl[0];
        $this->id_field = $tbl[1];
    }//change_tbl()

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
            return $rs->fetchAll($this->fetch_style);
        }
        return false;
    } //select()


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
     *
     *
     **/
    public function tally($record = array()){
        $query = "INSERT INTO tbl_tally (fld_datetime, fld_cat) VALUE (:current_datetime, :cat_id)";
        return $this->run($query, $record);
    }

    /**
     *
     **/
    public function produceDropdown(){
        $this->change_tbl('category');
        $result= $this->getAll();

        foreach ($result as $key) {
            echo "<option value=\"$key->id\">$key->fld_specific</option>";
        }
    }//produceDropdown()

    /**
     *
     **/
    public function displayTable($datetime){
        $this->change_tbl('view-count');
        $result = $this->select("date_format(fld_datetime,'%Y-%m-%d %k')= date_format(:datetime,'%Y-%m-%d %k')", '', array('datetime'=>$datetime));
        
        echo "<table class=\"table table-condensed table-hover\">";
        echo "<thead><tr><th>Service</th><th>Count</th></tr><tbody>";

        foreach ($result as $key) {
            echo "<tr><td>$key->fld_specific</td><td>$key->count</td></tr>";
        }
        echo "</tbody></table>";
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