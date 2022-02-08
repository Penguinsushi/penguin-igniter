<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// >> at http://www.site.com/test/page?var=val
// $uri = new URI();
// echo $uri->uri; // test/page
// echo $uri->query_string; // var=val
// echo $uri->query_array['var']; // val
// echo $uri->elements[0]; // test
// --
// URI::trimUnneededElements(2); // trims url elements past the third (index 2, e.g. blog/entry/100[/these/are/trimmed])
// =============================================================================================================

class URI
{
    
    public $request_uri = "";
    public $basedir = "";
    public $uri = "";
    public $query_string = "";
    public $query_array = array();
    public $elements = array();
    public $fake_ext = "";
    
    // CONSTRUCTOR
    public function __construct($uri=NULL)
    {
       if (empty($uri))
       {
            $uri = $_SERVER['REQUEST_URI'];
       }
       // ignore (any) query string
        $ri = explode('?',$uri);
        if (!empty($ri[1]))
        {
            $this->query_string = $ri[1];
            $this->setQueryArray();
        }
        $request_uri = $ri[0];
        // remove leading/trailing slashes from url
        $request_uri = trim($request_uri,'/');
        $this->uri = $this->request_uri = $request_uri;
    }
    
    // STATIC METHODS
    
    // if superflous elements exist, redirect to uri that has only the required *number* of elements
    public static function trimUnneededElements($last_index_needed=-1,$use_uri=NULL)
    {
        $uri = new self($use_uri);
        $uri->setElements();        
        $unneeded = $last_index_needed+1;
        if (!empty($uri->elements[$unneeded]))
        {
            header ('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$GLOBALS['config']['base_url'].implode('/',array_slice($uri->elements,0,$unneeded)));
            die;
        }
    }
    
    // PUBLIC METHODS
    
    public function baseDir($basedir)
    {
        $this->basedir = $basedir;
        if (
            !empty($this->basedir) AND
            strpos($this->uri,$this->basedir) !== FALSE
        ) 
        {
            $this->uri = substr($this->uri,strlen($this->basedir));
        }
    }
    
    public function directEmpty($touri=NULL,$redirect=TRUE)
    {
        if (empty($this->uri) AND !empty($touri))
        {
            if ($redirect == TRUE)
            {
                header('Location: '.$touri);
                die;
            }
            else
            {
                $this->__construct($touri);
            }
        }
    }
    
    public function redirectURIs($redirect_array)
    {
        if (!empty($redirect_array[$this->uri]))
        {
            if (substr($redirect_array[$this->uri],0,3) == '301')
            {
                header ('HTTP/1.1 301 Moved Permanently');
                $new_uri = substr($redirect_array[$this->uri],4);
            }
            else
            {
                $new_uri = $redirect_array[$this->uri];
            }
            header('Location: '.$new_uri);
            die;
        }
    }
    
    public function rewriteURIs($rewrite_array)
    {
        if (!empty($rewrite_array[$this->uri]))
        {
            $this->uri = $rewrite_array[$this->uri];
        }
    }
    
    public function fakeExt($fake_ext=NULL,$force=NULL)
    {
        $this->fake_ext = $fake_ext;
        if (!empty($fake_ext) AND substr($this->uri,-strlen($fake_ext)) == $fake_ext)
        {
                $this->uri = substr($this->uri,0,-strlen($fake_ext));
        }
        elseif(!empty($fake_ext) AND $force == TRUE)
        {
            header('Location: /'.$this->request_uri.$fake_ext);
            die;
        }
    }
    
    public function setElements()
    {
        $this->elements = explode("/",$this->uri);
    }
    
    // PRIVATE METHODS
    
    private function setQueryArray()
    {
        $qchunks = explode('&',$this->query_string);
        foreach($qchunks AS $q_key => $q_value)
        {
             if (!empty($q_value))
             {
                 $keyval = explode('=',$q_value);
                 preg_match('/(.+)\[(.*)\]/',$keyval[0],$matches);
                 if (!empty($matches))
                 {
                     if (!empty($matches[2]))
                     {
                         $this->query_array[$matches[1]][$matches[2]] = $keyval[1];
                     }
                     else
                     {
                         $this->query_array[$matches[1]][] = $keyval[1];
                     }
                 }
                 else
                 {
                    $this->query_array[$keyval[0]] = $keyval[1];
                 }
             }
        }
        $_GET = $this->query_array;
    }
    
}

?>