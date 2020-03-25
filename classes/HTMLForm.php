<?php

// functional class to make html form creation easier / more concise

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// new HTMLForm('index.php','POST');
// HTMLForm::hiddenInput('hidden_name','value');
// HTMLForm::textInput('text_name',NULL,array('id' => 'element_id'));
// HTMLForm::close();
// =============================================================================================================

class HTMLForm
{
    
    // PROPERTIES
    
    // CONSTRUCTOR
    
    public function __construct($action=NULL,$method=NULL,$required=array(),$attr=array(),$multipart=FALSE)
    {
        if (empty($action))
        {
            $action = $_SERVER['REQUEST_URI'];
        }
        if (empty($method))
        {
            $method = 'POST';   
        }
        if (!empty($required))
        {
            $additional='';
            $reqstring = "onsubmit=\"if (";
                foreach($required AS $r_key => $reqid)
                {
                    if ($r_key > 0) {$reqstring.= " || ";}
                    $reqstring.= " document.getElementById('$reqid').value == '' ";
                }
                if (!empty($attr['onsubmit']))
                {
                    $additional=$attr['onsubmit'];
                    unset($attr['onsubmit']);
                }
                $reqstring.= '){alert(\'Please fill in all required fields.\');return false;}else{'.$additional.' return true;}';
                $reqstring.= "\"";
        }
        else
        {
            $reqstring = '';
        }
        if ($multipart)
        {
            echo "<form action=\"$action\" method=\"$method\" enctype=\"multipart/form-data\" $reqstring ".HTMLForm::assignAttr($attr).">";
            HTMLForm::hiddenInput('MAX_FILE_SIZE','999999999');
        }
        else
        {
            echo "<form action=\"$action\" method=\"$method\" $reqstring ".HTMLForm::assignAttr($attr).">";
        }
    }
    
    // METHODS
    
    public static function hiddenInput($name,$value,$attr=array())
    {
        echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function textInput($name,$value=NULL,$attr=array())
    {
        echo "<input type=\"text\" name=\"$name\" value=\"$value\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function passwordInput($name,$value=NULL,$attr=array())
    {
        echo "<input type=\"password\" name=\"$name\" value=\"$value\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function checkInput($name,$value,$checkval,$attr=array())
    {
        echo "<input type=\"checkbox\" name=\"$name\" value=\"$checkval\" ";
        if ($value == $checkval) { echo " CHECKED ";}
        echo " ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function radioInput($name,$value,$checkval,$attr=array())
    {
        echo "<input type=\"radio\" name=\"$name\" value=\"$checkval\" ";
        if ($value == $checkval) { echo " CHECKED ";}
        echo " ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function fileInput($name,$attr=array())
    {
        echo "<input type=\"file\" name=\"$name\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function formButton($name,$value,$attr=array())
    {
        echo "<input type=\"button\" name=\"$name\" value=\"$value\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function submitButton($name,$value,$attr=array())
    {
        echo "<input type=\"submit\" name=\"$name\" value=\"$value\" ".HTMLForm::assignAttr($attr).">";
    }
    
    public static function textArea($name,$value=NULL,$attr=array())
    {
        echo "<textarea name=\"$name\" ".HTMLForm::assignAttr($attr).">$value</textarea>";
    }
    
    public static function selectBox($name,$value=NULL,$options,$optionvalues=NULL,$attr=array())
    {
        if (empty($optionvalues)) {$optionvalues = $options;}
        echo "<select name=\"$name\" ".HTMLForm::assignAttr($attr).">";
            foreach($options AS $o_key => $o_value)
            {
                echo "<option "; 
                if ($value == $optionvalues[$o_key]) {echo "selected=\"SELECTED\"";}
                echo " value=\"".$optionvalues[$o_key]."\">$o_value";
            }
        echo "</select>";
    }
    
    public static function dateTimeInputs($name_prefix,$value=NULL,$attr=array(),$sets='both')
    {
        if (empty($value)){$value = date("Y-m-d H:i:s");} else {$value = date("Y-m-d H:i:s",strtotime($value));}
        if ($sets == 'both' OR $sets == 'date')
        {
            $month = date("m",strtotime($value));
            $day = date("d",strtotime($value));
            $year = date("Y",strtotime($value));
            $x = 0;
            while($x < 31){$x++;if(strlen($x) == 1){$x = "0$x";}$days[] = $x;}
            $y = 0;
            while($y < 12){$y++;if(strlen($y) == 1){$y = "0$y";}$month_nums[] = $y;$months[] = date("M",strtotime("2000-$y-01"));}
            if ($year-10 < date("Y")-10) {$z = $year-10;} else {$z = date("Y")-10;}
            if ($year+10 > date("Y")+10) {$zz = $year+10;} else {$zz = date("Y")+10;}
            while($z < $zz){$z++;$years[] = $z;}
            HTMLForm::selectBox($name_prefix."_month",$month,$months,$month_nums,$attr);
            HTMLForm::selectBox($name_prefix."_day",$day,$days,NULL,$attr);
            HTMLForm::selectBox($name_prefix."_year",$year,$years,NULL,$attr);
        }
        if ($sets == 'both' OR $sets == 'time')
        {
            $hour = date("h",strtotime($value));
            $min = date("i",strtotime($value));
            $ampm = date("a",strtotime($value));
            $a = 0;
            while($a < 12){$a++;if(strlen($a) == 1){$a = "0$a";}$hours[] = $a;}
            $b = 0;
            while($b <= 59){if(strlen($b) == 1){$b = "0$b";}$mins[] = $b;$b++;}
            HTMLForm::selectBox($name_prefix."_hour",$hour,$hours,NULL,$attr);
            HTMLForm::selectBox($name_prefix."_min",$min,$mins,NULL,$attr);
            HTMLForm::selectBox($name_prefix."_ampm",$ampm,array("am","pm"),NULL,$attr);
        }    
    }
    
    // for consistency
    public static function close()
    {
        echo "</form>";
    }
    
    // STATIC METHODS
    
    private static function assignAttr($array)
    {
        $attr_string='';
        foreach($array AS $attr => $value)
        {
            $attr_string.=" $attr=\"$value\" ";
        }
        return $attr_string;
    }
    
    
}



?>