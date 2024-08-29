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
        $translate = array(
            "\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
            chr(0xc2).chr(0x82) => "'",
            
           "\xC2\x84" => '"', // U+0084⇒U+201E double low-9 quotation mark
            chr(0xc2).chr(0x84) => '"',
            
           "\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
            chr(0xc2).chr(0x8b) => "'",
            
           "\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
            chr(0xc2).chr(0x91) => "'",
            
           "\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
            chr(0xc2).chr(0x92) => "'",
            
           "\xC2\x93" => '"', // U+0093⇒U+201C left double quotation mark
            chr(0xc2).chr(0x93) => '"',
            
           "\xC2\x94" => '"', // U+0094⇒U+201D right double quotation mark
            chr(0xc2).chr(0x94) => '"',
            
           "\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark
            chr(0xc2).chr(0x9b) => "'",
            
           "\xC2\xAB" => '"', // U+00AB left-pointing double angle quotation mark
            chr(0xc2).chr(0xab) => '"',
            
           "\xC2\xBB" => '"', // U+00BB right-pointing double angle quotation mark
            chr(0xc2).chr(0xbb) => '"',
            
           "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
            chr(0xe2).chr(0x80).chr(0x98) => "'",
            
           "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
            chr(0xe2).chr(0x80).chr(0x99) => "'",
            
           "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
            chr(0xe2).chr(0x80).chr(0x9a) => "'",
            
           "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
            chr(0xe2).chr(0x80).chr(0x9b) => "'",
            
           "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
            chr(0xe2).chr(0x80).chr(0x9c) => '"',
            
           "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
            chr(0xe2).chr(0x80).chr(0x9d) => '"',
            
           "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
            chr(0xe2).chr(0x80).chr(0x9e) => '"',
            
           "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
            chr(0xe2).chr(0x80).chr(0x9f) => '"',
            
           "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
            chr(0xe2).chr(0x80).chr(0xb9) => "'",
            
           "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
            chr(0xe2).chr(0x80).chr(0xba) => "'",
            
            chr(0xe2).chr(0x80).chr(0x9a) => '\'', //SINGLE LOW-9 QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0x9e) => '"', //DOUBLE LOW-9 QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0xa6) => '...', //HORIZONTAL ELLIPSIS
            chr(0xe2).chr(0x80).chr(0x98) => '\'', //LEFT SINGLE QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0x99) => '\'', //RIGHT SINGLE QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0x9c) => '"', //LEFT DOUBLE QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0x9d) => '"', //RIGHT DOUBLE QUOTATION MARK
            chr(0xe2).chr(0x80).chr(0x93) => '-', //EN DASH
            chr(0xe2).chr(0x80).chr(0x94) => '-', //EM DASH
            chr(145) => "'",
            chr(146) => "'",
            chr(147) => '"',
            chr(148) => '"',
            chr(151) => '-',
            "\\" => "\\\\",
            "'" => "\\'",
            "`" => "\\`",
           
        );
        
        return  mb_convert_encoding(iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(array_keys($translate), $translate, $string)), 'HTML-ENTITIES', 'UTF-8');
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