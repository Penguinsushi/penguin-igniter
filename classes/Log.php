<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $log = new Log();
// $log->writeEntry('write this message to the log');
// =============================================================================================================

class Log extends TextFile
{
    
    // PROPERTIES
    
    public $linestart = ''; // set by constructor
    public $info = ''; // set by constructor
    public $linereturn = "\n\n";        
    
    // CONSTRUCTOR
    
    public function __construct($path=NULL)
    {
        if (empty($path))
        {
            $path = 'pi_log_'.date('Ymd').'.log';
        }
        $this->linestart = '# ['.date("Y-m-d H:i:s").'] ['.$_SERVER['REQUEST_URI'].']';
//        $details = json_decode(file_get_contents("http://ipinfo.io/{$_SESSION['session']->ip}/json"));
        $details = json_decode(file_get_contents("http://api.ipstack.com/{$_SESSION['session']->ip}?access_key=cda377e4694ac81027c01111de7a09b2"));
        if (!empty($details->city)) {$geoip = $details->city.' ';} else {$geoip='';}
        if (!empty($details->region_name)) {$geoip.= $details->region_name.' ';}
        if (!empty($details->country_name)) {$geoip.= $details->country_name.' ';}
        $this->info = "IP(".$_SESSION['session']->ip.") GEO(".$geoip.") HOST(".$_SESSION['session']->host.") AGENT(".$_SESSION['session']->agent.") SESSION(".$_SESSION['session']->id."(%SESSAGE%)) POST(%POST%)";
        parent::__construct($path);
    }
    
    // PUBLIC METHODS
    
    public static function site() {
        if (!Config::val('log')) {
            return false;
        }
        if (empty($GLOBALS['pi_log'])) {
            $GLOBALS['pi_log'] = new self(Config::val('log'));
        }
        return $GLOBALS['pi_log'];
    }
    
    public static function entry($message = null, $info = true) {
        $site = self::site();
        if (!$site) {
            return;
        }
        $site->writeEntry($message, $info);
    }
    
    public static function logPageLoad() {
        if (    
            self::site() &&
            (
                Config::val('log_pageloads') === TRUE 
                OR (strstr(Config::val('log_pageloads'),'PAGE('.URI::page().')'))
                OR (
                    strstr(Config::val('log_pageloads'),'POST') AND
                    !empty($_POST)
                ) OR (
                   strstr(Config::val('log_pageloads'),'QUERY') AND
                   URI::current()->query_string
                ) OR (
                   strstr(Config::val('log_pageloads'),'SESSION') AND
                   Session::current()->new AND 
                   (
                        Session::current()->bot == FALSE OR 
                        !strstr(Config::val('log_pageloads'),'NOBOTS')
                   )
                )
            )
        ) {
            Log::entry();
        }
    }
    
    public function writeEntry($message=NULL,$include_info=TRUE)
    {
        $write = $this->linestart;
        // some info generated here so that if include_info == false process time is not wasted
        if ($include_info)
        {
            $post='NONE';
            if (!empty($_POST))
            {
                $post='';
                foreach($_POST AS $var => $val)
                {
                    if(strtolower($var) == 'password' OR strtolower($var) == 'pass') {$val = '[hashed:'.hash('sha256',$val).']';}
                    $post.= " $var=$val ";
                }
                $post.='';
            }
            $string = array(
                                '%SESSAGE%',
                                '%POST%'
                            );
            $replace = array(
                                $_SESSION['session']->age(),
                                $post
                            );
            $this->info = str_replace($string,$replace,$this->info);
            $write.= ' ['.$this->info.']';
        }
        if (!empty($message))
        {
            $write.= ' : '.$message.'';
        }
        $this->writeLine($write,$this->linereturn);
    }
    
}

?>
