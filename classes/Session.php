<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// session_start()
// if (empty($_SESSION['session'])) {$_SESSION['session'] = new Session();}
// echo $_SESSION['session']->ip.' is your IP Address.';
// =============================================================================================================

class Session
{
    
    // PROPERTIES
    public $id = '';
    public $start_time = '';
    public $ip = '';
    public $host = '';
    public $agent = '';
    public $browser = '';
    public $browser_type = '';
    public $bot = FALSE;
    public $new = TRUE;
    
    public $values = [];
    
    public static $initialized = false;
    
    // CONSTRUCTOR
    
    public function __construct()
    {
        $this->id = session_id();
        $this->setTime();
        $this->setHost();
        $this->setBrowser();
        $this->setBot();
    }
    
    // PUBLIC METHODS
    
    public static function initialize() {
        if (self::$initialized) {
            return;
        }
        session_start();
        self::single(true);
        self::$initialized = true;
    }
    
    public static function current() {
        return self::single();
    }
    
    public static function val($tag, $value = null, $unset = false) {
        $current = self::current();
        if ($unset) {
            return $current->setVal($tag, null);
        } else {
            if (!empty($value)) {
                return $current->setVal($tag, $value);
            } else {
                return $current->getVal($tag);
            }
        }
    }
    
    public function age()
    {
        $age = time() - $this->start_time;
        return $age;
    }
    
    // PRIVATE METHODS
    
    private function getVal($tag) {
        return $this->values[$tag] ?? null;
    }
    
    private function setVal($tag, $value) {
        return $this->values[$tag] = $value;
    }
    
    private static function single($init = false) {
        if (empty($_SESSION['pi_session'])) {
            $_SESSION['pi_session'] = new self();
        } elseif ($init) {
            $_SESSION['pi_session']->new = false;
        }
        return $_SESSION['pi_session'];
    }
    
    private function setTime()
    {
        $this->start_time = time();
    }
    
    private function setHost()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->host = gethostbyaddr($this->ip);
    }
    
    // note that this will not necessarily be accurate 100% of the time, though I would imagine it to be at least 90%
    private function setBrowser()
    {
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
        if(strstr(strtolower($this->agent), 'msie'))
        {
            $this->browser = 'ie';
        }
        elseif(strstr(strtolower($this->agent), 'firefox'))
        {
            $this->browser = 'firefox';
        }
        elseif(strstr(strtolower($this->agent), 'chrome'))
        {
            $this->browser = 'chrome';
        }
        elseif(strstr(strtolower($this->agent), 'safari'))
        {
            $this->browser = 'safari';
        }
        else
        {
            $this->browser = 'other';
        }
        if (strstr(strtolower($this->agent), 'mobile'))
        {
            $this->browser_type = 'mobile';
        }
        else
        {
            $this->browser_type = 'desktop';
        }
    }
    
    // note that this is a *guess* - false positives are possible, as are missed bots
    private function setBot()
    {
        if (
                strstr(strtolower($this->agent), 'bot') OR 
                strstr(strtolower($this->agent), 'spider') OR 
                strstr(strtolower($this->agent), 'crawl') OR 
                strstr(strtolower($this->agent), 'fetcher') OR 
                
                strstr(strtolower($this->host), 'bot') OR 
                strstr(strtolower($this->host), 'crawl') OR 
                strstr(strtolower($this->host), 'spider') OR
                strstr(strtolower($this->host), 'fetcher')
           )
        {
            $this->bot = TRUE;
        }
    }
    
}

?>
