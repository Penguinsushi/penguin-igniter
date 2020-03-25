<?php 

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $db = new DatabaseConnection('mysql','localhost','username','password','db_name');
// $result = $db->query('SELECT * FROM table');
// while($row = $db->fetch_array($result)){}
// =============================================================================================================

class DatabaseConnection
{

	// PROPERTIES
	
	public $type = "";
	public $host = "";
        public $port = "";
        public $socket = "";
	public $user = "";
	public $pass = "";
	public $db = "";
	
	public $resource_link = "";

        private $tablefields = array();
        
	// CONSTRUCTOR
	
	public function __construct($type=NULL,$host=NULL,$user=NULL,$pass=NULL,$db=NULL,$port=NULL,$socket=NULL)
	{
            // use mysqli_ instead of deprecated/unpreferred mysql_
            if ($type == 'mysql') {$type = 'mysqli';}
            if (!empty($GLOBALS['config']['db_type']) AND $GLOBALS['config']['db_type'] == 'mysql') {$GLOBALS['config']['db_type'] = 'mysqli';}
            // check for config
            if (empty($type) AND !empty($GLOBALS['config']['db_type'])) {$type = $GLOBALS['config']['db_type'];}
            if (empty($host) AND !empty($GLOBALS['config']['db_host'])) {$host = $GLOBALS['config']['db_host'];}
            if (empty($user) AND !empty($GLOBALS['config']['db_user'])) {$user = $GLOBALS['config']['db_user'];}
            if (empty($pass) AND !empty($GLOBALS['config']['db_pass'])) {$pass = $GLOBALS['config']['db_pass'];}
            if (empty($db) AND !empty($GLOBALS['config']['db_name'])) {$db = $GLOBALS['config']['db_name'];}
            if (empty($port) AND !empty($GLOBALS['config']['db_port'])) {$port = $GLOBALS['config']['db_port'];}
            if (empty($socket) AND !empty($GLOBALS['config']['db_socket'])) {$socket = $GLOBALS['config']['db_socket'];}
            // define properties
            $this->type = $type;
            $this->host = $host;
            $this->user = $user;
            $this->pass = $pass;
            $this->db = $db;
            $this->port = $port;
            $this->socket = $socket;
            // connect
            $this->connect();
	}
	
	// GENERAL DB INTERFACE METHODS
        
	public function query($query)
	{
            if (!empty($GLOBALS['uri']->query_array['showqueries'])) {echo $query;}
            if ($this->type == 'mysqli')
            {
		return mysqli_query($this->resource_link,$query);	
            }
            else
            {
                $query_function = $this->type."_query";
		return $query_function($query,$this->resource_link);
            }
	}
	
	public function fetch_array($result)
	{
            if (empty($result)) {return FALSE;}
            if ($this->type == 'mysqli')
            {
                return mysqli_fetch_array($result);
            }
            else
            {
		$fetcharray_function = $this->type."_fetch_array";
		return $fetcharray_function($result);
            }
	}
	
	public function fetch_object($result,$obj=NULL,$params=NULL)
	{
            if (empty($result)) {return FALSE;}
            if ($this->type == 'mysqli')
            {
                if (!empty($obj))
                {
                    if (!empty($params))
                    {
                        $return = mysqli_fetch_object($result,$obj,$params);
                    }
                    else
                    {
                        $return = mysqli_fetch_object($result,$obj);
                    }
                }
                else
                {
                    $return = mysqli_fetch_object($result);
                }
                return $return;
            }
            else
            {
		$fetchobject_function = $this->type."_fetch_object";
		return $fetchobject_function($result,$obj,$params);
            }
	}
	
	public function num_rows($result)
	{
            if (empty($result)) {return FALSE;}
            if ($this->type == 'mysqli')
            {
                return mysqli_num_rows($result);
            }
            else
            {
		$numrows_function = $this->type."_num_rows";
		return $numrows_function($result);
            }
	}
	
	public function affected_rows()
	{
            if ($this->type == 'mysqli')
            {
                return mysqli_affected_rows($this->resource_link);
            }
            else
            {
		$affectedrows_function = $this->type."_affected_rows";
		return $affectedrows_function($this->resource_link);
            }
	}
        
    public function insert_id()
    {
        if ($this->type == 'mysqli')
        {
            return mysqli_insert_id($this->resource_link);
        }
        else
        {
            $insertid_function = $this->type."_insert_id";	
            return $insertid_function($this->resource_link);
        }
    }
    
    public function table_exists($table)
    {
    	if ($this->query("DESC $table") === FALSE)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    // DATA FETCH METHODS 
    // $query expects formatted sql query
    // $objectclass expects string denoting an object class to inject db data into
    // objectconstructparams expects parameters to pass the objectclass constructor
    // objectmethods expects multi-dimensional array outlining objectclass methods to execute for each object created
        // array('function' => '[method name]', 'params' => array('[method param]','[method param]'))
    // forcereturn expects 'single' or NULL - arrays are returned by default, 'single' forces a single-instance return
    
    public function createDataObjects($query,$objectclass=NULL,$objectconstructparams=NULL,$objectmethods=NULL,$forcereturn=NULL)
    {
        $result = $this->query($query);
        if (empty($objectclass)) {$objectclass='stdClass';}
        if (!empty($objectclass) AND class_exists($objectclass))
        {
            $return=array();
            while($row = $this->fetch_object($result,$objectclass,$objectconstructparams))
            {
                if (!empty($objectmethods))
                {
                    foreach($objectmethods AS $om_key => $om_array)
                    {
                        if (!is_array($om_array)) 
                        {
                            $function = $om_array;
                            $paramsarray = array();
                        }
                        else
                        {
                            $function = $om_array['function'];
                            if (!empty($om_array['params'])) 
                            {
                                $paramsarray = $om_array['params'];
                            }
                            else
                            {
                                $paramsarray = array();
                            }
                        }   
                        call_user_func_array(array($row, $function),$paramsarray);
                    }
                }
                $return[] = $row;
            }
            if (empty($return)) {return FALSE;}
            if ($forcereturn == 'single')
            {
                return $return[0];
            }
            else
            {
                return $return;
            }
        }
        else
        {
            return false;    
        }
    }

    public function getDataRows($query,$forcereturn=NULL)
    {
        $result = $this->query($query);
        $return = array();
        while($row = $this->fetch_array($result))
        {
            $return[] = $row;
        }
        if ($forcereturn == 'single')
        {
            return $return[0];
        }
        else
        {
            return $return;
        }
    }
    
    // DATA MANIPULATION METHODS
    
    // $table expects database table name
    // $fieldvalues expects array[[field]] = [value]
    // $index expects table unique identifier
    // $indexvalue expects value of $index to operate upon
    
    public function dataInsert($table,$fieldvalues) 
    {
        $fieldsquery="";
        $valuesquery="";
        foreach($fieldvalues AS $field => $value)
        {
        	if ($this->isFieldInTable($table,$field))
        	{
            	$fieldsquery.=",$field";
            	$valuesquery.=",'".String::db_sanitize($value)."'";
            }
        }
        $fieldsquery = substr($fieldsquery,1);
        $valuesquery = substr($valuesquery,1);
        $query = "INSERT INTO $table
                        ($fieldsquery)
                        VALUES
                        ($valuesquery)
                        ";
        if ($this->query($query))
        {
            // assuming query executed, return index value of new row
            return $this->insert_id();
        }
        else
        {
            return FALSE;
        }
    }
    
    public function dataUpdate($table,$index,$indexvalue,$fieldvalues) 
    {
        $updatequery="";
        foreach($fieldvalues AS $field => $value)
        {
        	if ($this->isFieldInTable($table,$field))
        	{
            	$updatequery.=",$field = '".String::db_sanitize($value)."'";
            }
        }
        $updatequery = substr($updatequery,1);
        $query = "UPDATE $table SET $updatequery WHERE $index = '$indexvalue'";
        return $this->query($query);
    }
    
    public function dataDelete($table,$index,$indexvalue)
    {
        $query = "DELETE FROM $table WHERE $index = '$indexvalue'";
        return $this->query($query);
    }
    
    // PRIVATE METHODS
    
    private function connect()
    {
        if ($this->type == 'mysqli')
        {
            $this->resource_link = mysqli_connect('p:'.$this->host,$this->user,$this->pass,$this->db,$this->port,$this->socket);
        }
        else
        {
            $connect_function = $this->type."_pconnect";
            if (!empty($this->socket)) {$suffix = ':'.$this->socket;} elseif (!empty($this->port)) {$suffix = ':'.$this->port;} else {$suffix = '';}
            $this->resource_link = $connect_function($this->host.$suffix, $this->user, $this->pass);	
            if (!empty($this->resource_link))
            {	
                    $select_function = $this->type."_select_db";	
                    $select_function($this->db, $this->resource_link);	
            }
        }
    }
    
    private function isFieldInTable($table,$field)
    {
        if (empty($this->tablefields[$table]))
        {
            $query = "DESC $table";
            $result = $this->query($query);
            while($row = $this->fetch_array($result))
            {
                $this->tablefields[$table][] = $row['Field'];
            }
        }
        if (!empty($field) AND in_array($field,$this->tablefields[$table]))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}

?>