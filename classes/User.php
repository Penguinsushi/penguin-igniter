<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $_SESSION['user'] = User::userLogin('username','password');
// User::verifiyPermission($_SESSION['user'],3);
// =============================================================================================================

class User
{

	// PROPERTIES
	
	// db field properties from database table (if present)
	public $UserRowID = "";
	public $UserUsername = "";
	public $UserPasswordHash = "";
	public $UserLevel = "";
	
	// valid user/pass array (if not using database table) [username] => array('password' => [password], 'level' => [user level])
	public static $Users = array(
					'user' => array('passwordhash'=>'pass','level'=>'1')
					);
	
    // private
    protected static $table = "tblUsers";
    protected static $login_table = "tblUserLogins";
    protected static $send_messages_to = "shoe@penguinsushi.com";
    
	// CONSTRUCTOR
	
	public function __construct()
        {

        }
	
	// STATIC METHODS
	
	public static function userLogin($username,$passwordhash)
	{
            // USER LOGIN
            // if user table exists, login using its data
            if (!empty($GLOBALS['db']) AND $GLOBALS['db']->table_exists(self::$table))
            {
                $sql = new SQLQuery();
                $sql->FROM(self::$table);
                $sql->WHERE("UserUsername",$username);
                $sql->WHERE("UserPasswordHash",$passwordhash);
                $user = $GLOBALS['db']->createDataObjects($sql->query,'User',NULL,NULL,'single');
                if (!empty($user))
                {
                    
                    $return = $user;
                    $status = "SUCCESS";
                }
                else
                {
                    $return = FALSE;
                    $status = "FAILURE";
                }
            }
            // otherwise, login using data from static $Users array
            else
            {
         	if (self::$Users[$username]['passwordhash'] == $passwordhash)
         	{
         		$user = new self();
         		$user->UserUsername = $username;
         		$user->UserPasswordHash = self::$Users[$username]['passwordhash'];
         		$user->UserLevel = self::$Users[$username]['level'];
                        $status = "SUCCESS";
                        $return = $user;
         	}
         	else
         	{
         		$return = FALSE;
                        $status = "FAILURE";
         	}
            }
            // RECORD LOGIN INFO
            // if login table exists, record login attempt
            if (!empty($GLOBALS['db']) AND $GLOBALS['db']->table_exists(self::$login_table))
            {
                $logindata = array(
                                    'LoginUsername' => $username,
                                    'LoginIP' => $_SESSION['session']->ip,
                                    'LoginRemoteHost' => $_SESSION['session']->host,
                                    'LoginStatus' => $status,
                                    'LoginDateTime' => date("Y-m-d H:i:s"),
                                    'LoginSessionID' => $_SESSION['session']->id
                                    );
                $GLOBALS['db']->dataInsert(self::$login_table,$logindata);
            }
            if ($return == FALSE)
            {
                $yesterday = date('Y-m-d H:i:s',mktime(0, 0, 0, date("m"), date("d")-1,   date("Y")));
                $sql = new SQLQuery();
                $sql->FROM(self::$login_table);
                $sql->WHERE('LoginDateTime',$yesterday,'>=');
                $sql->WHERE('LoginStatus','FAILURE');
                $sql->ORDER_BY('LoginDateTime');
                $rows = $GLOBALS['db']->getDataRows($sql->query);
                $count = count($rows);
                if ($count % 5 == 0)
                {
                    $subject = "excessive login failures";
                    $message = "<b>Notice:</b> Penguinsushi.com reports $count login failures since $yesterday<br/><br/>";
                    $x=0;
                    foreach($rows AS $r_key => $r_value)
                    {
                        $x++;
                        $message.="<b>$x.</b> ".$r_value['LoginDateTime']." - as ".$r_value['LoginUsername']." from ".$r_value['LoginIP']."<br/>";
                    }
                    $message.="<br/>";
                    Email::send(self::$send_messages_to, $subject, $message, 'site@penguinsushi.com');
                }
            }
            return $return;
	}
        
        public static function verifyPermission($usr_obj,$required_access,$redirect=TRUE)
        {
            if (!is_numeric($required_access))
            {
                $admin_users = explode(',',strtolower($required_access));
            }
            if ($usr_obj instanceof self AND 
                    (
                        // access level is sufficient
                        (is_numeric($required_access) AND $usr_obj->UserLevel >= $required_access)
                        OR
                        // admin requires one of a specific user set
                        (!is_numeric($required_access) AND in_array(strtolower($usr_obj->UserUsername),$admin_users))
                    )
               )
            {
                return TRUE;
            }
            else
            {
            	if (strtolower($redirect) == TRUE)
            	{
	                header("Location: ".$GLOBALS['config']['access_restricted'].'/'.urlencode($GLOBALS['uri']->uri));
                    die;
	            }
	            elseif(strtolower($redirect) == FALSE)
	            {
	            	return FALSE;
	            }
                    else
                    {
                        header("Location: ".$GLOBALS['config']['base_url'].$redirect);
                    }
            }
        }

}

?>