<?php
namespace UHHiloLibrary\Circulation;

use UHHiloLibrary\DB\DBModel as DBModel;
use PDO;


/** 
* 
* This Class will house how the UI will manipulate the local database system.
*   1. Ability to get the entire row selection.
*/
class InternalCount extends DBModel
{
	public $id_field = 'fld_datetime';

	public $table_name = 'view_ic_all';

    /**
     * Overwrite base constructor function, Constructs the database conection via PDO. 
     * @return none | A die insert.
     **/
	public function __construct()
	{
        $dbh = require("_circulationLogin.php");
    	if (!$dbh) {
        	die("NO MYSQL CONNECTION");
    	}
    	$this->db_conn = $dbh;
	}

	/**
	 * 	Function that gets the result from id.
     *  @param - integer - $id - this should be the primary key number on the table. 
     *  @return array | false - returns an array of array which will only return the 1st row of the array. 
	 * */
	public function get($id)
    {
        $rs = $this->select("{$this->id_field} = :{$this->id_field}",'' , array("{$this->id_field}" => $id));
        if($rs && count($rs)){
			return $rs[0];
		}
		return false;
	}// get()

    /**
     *  @param: $barcode - integer - the item's barcode number.
     *  @return: an array of the item's count grouped by date.
     **/
    public function getViaBarcode($barcode)
    {
        $query = "SELECT *, COUNT(*) AS 'count' FROM {$this->table_name} WHERE fld_barcode = :fld_barcode GROUP BY DATE(fld_datetime), fld_barcode";

        $rs = $this->run($query, array('fld_barcode'=>$barcode));
        if($rs && count($rs)){
            return $rs;
        }
        return false;
    } //getViaBarcode()

    /**
     * @param - array - array must contain the the id field.
     * @return - array return the array of rows or false.
     **/
    public function getTotalByDay($id)
    {
        $query = "SELECT *, COUNT(*) AS 'count' FROM view_circ_stats WHERE the_date = :the_date GROUP BY DATE(fld_datetime), fld_barcode";

        $rs = $this->run($query ,array("the_date"=>$id));
        if($rs && count($rs)){
            return $rs;
        }
        return false;
    }

    /**
     *  @param - array - record - This record should have the required dates to query the db.
     *  @return - boolean/result - returns false if none otherwise it returns the result queried from db.
     **/
    public function getStatByDate($type, $record = array())
    {
        $query = "SELECT ";
        $from_where = "FROM view_circ_stats WHERE the_date BETWEEN :start AND :end";
        $sum = "SUM(IFNULL(AV,0)) AS 'Audio/Visual', SUM(IFNULL(Gen,0)) AS 'General', SUM(IFNULL(Hawn,0)) AS 'Hawaiian', SUM(IFNULL(Ref,0)) AS 'Reference', SUM(IFNULL(R_PB_NB,0)) AS 'Read, Paperback, and New Books', SUM(IFNULL(Maps,0)) AS 'Maps', SUM(IFNULL(CP,0)) As 'Current Periodicals', SUM(IFNULL(PBf,0)) AS 'Periodical Backfiles', SUM(IFNULL(Mfilmfiche,0)) AS 'Microfilm/Microfiche', SUM(IFNULL(Other,0)) AS 'Other', SUM(IFNULL(AV,0))+SUM(IFNULL(Gen,0))+SUM(IFNULL(Hawn,0))+SUM(IFNULL(Ref,0))+SUM(IFNULL(R_PB_NB,0))+SUM(IFNULL(Maps,0))+SUM(IFNULL(CP,0))+SUM(IFNULL(PBf,0))+SUM(IFNULL(Mfilmfiche,0))+SUM(IFNULL(Other,0)) AS Total ";
        $nosum ="IFNULL(AV,0) AS 'Audio/Visual', IFNULL(Gen,0) AS 'General', IFNULL(Hawn,0) AS 'Hawaiian', IFNULL(Ref,0) AS 'Reference', IFNULL(R_PB_NB,0) AS 'Read, Paperback, and New Books', IFNULL(Maps,0) AS 'Maps', IFNULL(CP, 0) As 'Current Periodicals', IFNULL(PBf,0) AS 'Periodical Backfiles', IFNULL(Mfilmfiche,0) AS 'Microfilm/Microfiche', IFNULL(Other,0) AS 'Other', IFNULL(AV,0)+IFNULL(Gen,0)+IFNULL(Hawn,0)+IFNULL(Ref,0)+IFNULL(R_PB_NB,0)+IFNULL(Maps,0)+IFNULL(CP,0)+IFNULL(PBf,0)+IFNULL(Mfilmfiche,0)+IFNULL(Other,0) AS Total ";
        if ($type == 'M') {
            $from_where .= " GROUP BY date";
            $query .= "date_format(the_date, '%b %Y') as 'date', {$sum} {$from_where}";
        }
        elseif ($type == 'Y') {
            $from_where .= " GROUP BY date";
            $query .= "date_format(the_date, '%Y') as 'date', {$sum} {$from_where}";
        }
        else{
            $query .= "date_format(the_date, '%a, %b %e %Y') as 'date', {$nosum} {$from_where}"; 
        }
        
        $order_by = " ORDER BY the_date";

        $rs = $this->run($query.$order_by , $record);
        if($rs){
            return $rs;
        }
        return false;
    }

    /**
     * Same as run_query() but not protected. This skips the select phase.
     * @param - query - string - This is the query string to run on local db.
     * @param - array - bounded variables - default: empty, contain the actual information that will be binded to the query statement.
     * @return - boolean/result - returns the result if there is any otherwise it will return false.
     **/
    public function run($query, array $bound_variables = array())
    {
        $rs = $this->run_query($query, $bound_variables);
        if ($rs) {
            return $rs->fetchAll($this->fetch_style);
        }
        return false;
    }
}
?>