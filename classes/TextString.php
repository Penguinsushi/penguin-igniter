<?php

// functional class to manipulate character strings

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $string = "' OR User != '"; // SQL INJECTION!
// $string = TextString::db_sanitize($string); // sanatize for db queries
// $string = TextString::sterilize($string); // remove all non alpha-numeric
// =============================================================================================================

class TextString
{

    // PROPERTIES
    
    // STATIC METHODS
    
    // escape sql characters to prevent injection
    public static function db_sanitize($string)
    {
        $chars = array("\\","'","`");
        $replace = array("\\\\","\\'","\\`");
        return str_replace($chars,$replace,$string);
    }
    
    // strip all non alpha-numeric
    public static function sterilize($string)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $string);
    }

    public static function for_url_segment($string)
    {
        $string = str_replace(' ','_',$string);
        return preg_replace("/[^A-Za-z0-9\-_]/", "", $string);
    }
    
    // strip undesireable filename characters (all except alpha-num, '-', '_', '.')
    public static function valid_filename($string)
    {
        return preg_replace("/[^A-Za-z0-9\-_\.]/", "", $string);
    }

    // break string into array of words
    public static function words($string,$underspace=TRUE)
    {
        if ($underspace == TRUE) {$string = str_replace('_',' ',$string);} // convert underscores to spaces
        return explode(' ',$string);
    }
    
    public static function highlight($string,$substring,$style="color:inherit;background-color:yellow;")
    {
        if (is_array($substring))
        {
            $ss = $substring;
        }
        else
        {
            $ss[0] = $substring;
        }
        
        
            $highlighted = $string;
            foreach($ss AS $key => $term)
            {
                // pull out matches in tags
                preg_match_all('/(<[^<>]*'.$term.'[^<>]*>)/i', $highlighted, $matches);
                $x=0;
                $tagmatches=array();
                foreach($matches[0] AS $mkey => $tagmatch)
                {
                    $x++;
                    $key = 'PSTAGMATCH'.$x;
                    $highlighted = str_replace($tagmatch, $key, $highlighted);
                    $tagmatches[$key] = $tagmatch;
                }
                // replace matches not in tags
                $sp1 = '<span style="'.$style.'">'; $sp2='</span>';
                $highlighted = preg_replace('/('.$term.')/i', $sp1.'${1}'.$sp2, $highlighted);
                
                // re-enter tag matches
                foreach($tagmatches AS $tmkey => $tagmatch)
                {
                    $highlighted = str_replace($tmkey, $tagmatch, $highlighted);
                }
            }
        
        return $highlighted;
    }

}

?>