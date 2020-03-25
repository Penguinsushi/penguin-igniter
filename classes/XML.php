<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $XML = XML::parse($xml);
// echo $XML->RootContainer->ChildContainer->contents;
// =============================================================================================================

class XML
{
    
    // PROPERTIES - children will be dynamically defined as additional properties
    public $tag = '';
    public $contents = '';
    public $attr = array(); // associative array of tag attributes
    public $parent = ''; // parent object reference
    
    // STATIC METHODS
    
    public function __toString() {
        return $this->contents;
    }
    
    public static function parse($file_or_string)
    {
        if(strpos($file_or_string,'<') !== FALSE)
        {
            $xml = $file_or_string;
        }
        elseif (is_file($file_or_string))
        {
            $xml = file_get_contents($file_or_string);
        }
        if (!empty($xml))
        {
            // =================================================================
            // filter out CDATA chunks
            $matches=array();
            preg_match_all('/'.preg_quote('<![CDATA[').'(.*?)'.preg_quote(']]>').'/s', $xml, $matches);
            if (!empty($matches[0]))
            {
                foreach($matches[0] AS $key => $string)
                {
                    $xml = str_replace($string,'CDATA: '.trim(str_replace(array('<','>'),array('&lt;','&gt;'),$matches[1][$key])),$xml);
                }
            }
            // =================================================================
            $xmlchunks = explode('<',$xml);
            foreach($xmlchunks AS $xc_key => $chunk)
            {
                if (!empty($chunk))
                {
                    // if ending tag
                    if (substr($chunk,0,1) == '/')
                    {
                        $PARENT = $PARENT->parent;
                    }
                    // if starting tag
                    else
                    {
                        $NEW = new self();
                        $bisect = explode('>',$chunk);
                        if (substr($bisect[0],-1,1) == '/') {$selfcontained=TRUE;} else {$selfcontained=FALSE;}
                        $tagend = strpos($bisect[0],' ');
                            if (empty($tagend)) {$tagend=strlen($bisect[0]);}
                        $TAG = substr($bisect[0],0,$tagend);
                        $NEW->tag = $TAG;
                        $attrstring = substr($bisect[0],$tagend+1);
                        $attrchunks = explode('=',$attrstring);
                        $label='';
                        foreach($attrchunks AS $ac_key => $achunk)
                        {
                            if (empty($label))
                            {
                                $label = trim($achunk);                    
                            }
                            else
                            {
                                $fromquotes = strrpos($achunk, '"');
                                if (!empty($fromquotes))
                                {
                                    $sachunk = substr($achunk,0,$fromquotes+1);
                                    $nqsachunk = str_replace('"','',$sachunk);
                                    $NEW->attr[$label] = trim($nqsachunk);
                                    $label = substr($achunk,$fromquotes+2);
                                }
                            }
                        }
                        if (!empty($bisect[1]))
                        {
                            $NEW->contents = $bisect[1];
                        }
                        // define heirarchy
                        if(empty($PARENT))
                        {
                            if (empty($XML))
                            {
                                $XML = new self();
                            }
                            $PARENT = $XML;
                        }
                        else 
                        {
                            $NEW->parent = $PARENT;   
                        }
                        $TON = $NEW->tag;
                        if (empty($PARENT->$TON)) 
                        {
                            $PARENT->$TON = $NEW;
                        }
                        else
                        {
                            if (!is_array($PARENT->$TON))
                            {
                                $PARENT->$TON = array($PARENT->$TON);
                            }
                            array_push($PARENT->$TON,$NEW);
                        }
                        if (strpos($xml,"</".$TAG.">") AND !$selfcontained)
                        {
                            if (is_array($PARENT->$TON))
                            {
                                $PARENT = end($PARENT->$TON);
                            }
                            else
                            {
                                $PARENT = $PARENT->$TON;   
                            }
                        } 
                    } 
                 }  
            }
            return $XML;
        }
    }
    
}


?>