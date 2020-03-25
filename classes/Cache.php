<?php 

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $cache = new Cache('file');
// $cache->cacheSet('inf1','info to cache');
// $cache->cacheSet('inf2',array('array info','more array info'));
// echo $cache->cacheGet('inf1');
// foreach($cache->cacheGet('inf2') AS $key => $value){}
// $cache->cacheUnset('inf1');
// $cache->cacheClear();
// =============================================================================================================

class Cache
{
    
    // PROPERTIES
    
    public $type = '';
    
    public static $cachetypes = array('session','file'); // valid cache types (limited to session and file for now as host does not support memcache)
    public static $cachedir = 'cache/'; // directory for file caching
    
    // CONSTRUCTOR
    
    public function __construct($type=NULL)
    {
        if (empty($type) AND !empty($GLOBALS['config']['cachetype'])) {$type = $GLOBALS['config']['cachetype'];}
        if (empty($type) OR !in_array($type,self::$cachetypes)) 
        {
            $type = 'session';
        }
        $this->type = $type;
        if ($this->type == 'file' AND !is_dir(self::$cachedir))
        {
            mkdir(self::$cachedir);
        }
    }
    
    // METHODS
    
    public function cacheSet($var,$value)
    {
        if ($this->type == 'session')
        {
            $_SESSION['SITECACHE'][$var] = $value;
            $set = $_SESSION['SITECACHE'][$var];
        }
        elseif ($this->type == 'file')
        {
            $thandle = fopen(self::$cachedir.'SITECACHE_'.$var,'w');
            $serial = serialize($value);
            fwrite($thandle,$serial);
            $set = $value;
        }
        return $set;
    }
    
    public function cacheGet($var)
    {
        if ($this->type == 'session')
        {
            if (!empty($_SESSION['SITECACHE'][$var]))
            {
                $get = $_SESSION['SITECACHE'][$var];
            }
        }
        elseif ($this->type == 'file')
        {
            if (is_file(self::$cachedir.'SITECACHE_'.$var))
            {
                if (time() - filemtime(self::$cachedir.'SITECACHE_'.$var) < 86400) 
                {
                    $unserial = unserialize(file_get_contents(self::$cachedir.'SITECACHE_'.$var));
                    $get = $unserial;
                }
                else
                {
                    unlink(self::$cachedir.'SITECACHE_'.$var);
                }
            }
        }
        if (!empty($get))
        {
            return $get;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function cacheUnset($var)
    {
        $unset='';
        if ($this->type == 'session')
        {
            if (!empty($_SESSION['SITECACHE'][$var]))
            {
                unset($_SESSION['SITECACHE'][$var]);
                $unset = TRUE;
            }
            else
            {
                $unset = FALSE;
            }
        }
        elseif ($this->type == 'file')
        {
            if (is_file(self::$cachedir.'SITECACHE_'.$var))
            {
                unlink(self::$cachedir.'SITECACHE_'.$var);
                $unset = TRUE;
            }
            else
            {
                $unset = FALSE;
            }
        }
        return $unset;
    }
    
    public function cacheClear()
    {
        if ($this->type == 'session')
        {
            unset($_SESSION['SITECACHE']);
        }
        elseif ($this->type == 'file')
        {
            $td = dir(self::$cachedir);
            while (false !== ($file = $td->read())) {
                if (substr($file,0,10) == 'SITECACHE_')
                {
                    unlink(self::$cachedir.$file);
                }
            }
            $td->close();
        }
    }
    
}

?>