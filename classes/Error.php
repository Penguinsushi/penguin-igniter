<?php 

class Error
{
    
    // PROPERTIES
    
    // db field properties from database table (if present)
    public $ErrorRowID = '';
    public $ErrorType = '';
    public $ErrorTitle = '';
    public $ErrorMessage = '';
    public $ErrorHTTPCode = '';
    
    // errors type/message array (if not using database table) [type] => [message]
    public static $Errors = array(
                                    '404' => array('message'=>'document not found','httpcode'=>'HTTP/1.0 404 Not Found'),
                                    'content_error' => array('message'=>'content error on this page','httpcode'=>'HTTP/1.1 200 OK'),
                                    'invalid_login' => array('message'=>'invalid username and password','httpcode'=>'HTTP/1.1 401 Unauthorized'),
                                    'access_restricted' => array('message'=>'you do not have permission to access this resource','httpcode'=>'HTTP/1.1 403 Forbidden'),
                                    'unknown' => array('message'=>'the system encountered and unknown error','httpcode'=>'HTTP/1.1 200 OK')
                                    );
    
    // private
    private static $table = "tblErrors";
    
    // CONSTRUCTOR
    
    public function __construct()
    {
        
    }
    
    // STATIC METHODS
    
    public static function getError($type,$log_times=FALSE)
    {
        if (!empty($GLOBALS['db']) AND $GLOBALS['db']->table_exists(self::$table))
        {
            $sql = new SQLQuery();
            $sql->FROM(self::$table);
            $sql->WHERE('ErrorType',$type);
            $sql->ORDER_BY('RAND()');
            $error = $GLOBALS['db']->createDataObjects($sql->query,'Error',NULL,NULL,'single');
            if (empty($error))
            {
                $error = self::getError('unknown');
            }
            if ($log_times)
            {
                $GLOBALS['db']->query('UPDATE '.self::$table." SET ErrorShown = ErrorShown + 1 WHERE ErrorRowID = '".$error->ErrorRowID."'");
            }
            return $error;
        }
        else
        {
            $error = new self();
            if (!in_array($type,self::$Errors )){$type = 'unknown';}
            $error->ErrorType = $type;
            $error->ErrorTitle = ucwords(str_replace('_',' ',$type));
            $error->ErrorMessage = self::$Errors[$type]['message'];
            $error->ErrorHTTPCode = self::$Errors[$type]['httpcode'];
            return $error;
        }
    }
    
}

?>