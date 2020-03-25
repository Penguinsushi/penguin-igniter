<?php 

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $enc = Encryption::encrypt('encrypt this','enc_key');
// echo $enc->encrypted;
// $dec = Encryption::decrypt('[encrypted string]','enc_key');
// echo $dec->decrypted;
// =============================================================================================================

class Encryption
{
    
    // PROPERTIES
    
    public $key = ""; // Note: for best results, vary keys used
    public $string = "";
    
    public $prefix = "";
    public $suffix = "";
    public $noise = "";
    
    public $encrypted = ""; // encrypted string
        public $stripped_noise = ""; // string containing only the direct encodings (no noise characters)
    public $decrypted = ""; // decrypted string
    
    public $charskey = ""; // determines which charskey to use
    
    private $dir = "f"; // expects f or b
    
    private $noiseinterval = 0; // set by setNoiseInterval() - indicates how often to insert random noise characters
    
    // IMPORTANT NOTE: this string is a secondary key as well as a character reference. 
    // if these character strings change in any way, encryptions made on the old strings will become undecryptable unless it is reverted
    // make (any) alterations to the string BEFORE real use!  
    // you may want to make sure you have a copy of these strings in case this file is ever corrupted, otherwise your encrypted data will be unreadable!
    static $charskey1 = "!@#\$%^&*()-_=+|[{]};:'\",<.>/\\?`~ aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ1234567890";
    static $charskey2 = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 !@#\$%^&*()-_=+|[{]};:'\",<.>/\\?`~";
    static $charskey3 = "Nq]I2M}aZVp;n:r'1\"soYwQ3bOH0LJmU,Wv<t.xP4E>DluKcC/\\RXyBT5?e`~Akd!6z\$@Fj#f%^&7i*g(SG8)h-_=+|9[{ ";
    
    static $defaultkey = "ps_enc.key";
    
    // METHODS
    
    public static function encrypt($string,$key=NULL)
    {
        if (empty($key)) {$key = self::$defaultkey;}
        $encrypt = new self();
        $encrypt->setCharsKey();
        $encrypt->key = $key;
        $encrypt->string = $string;
        $encrypt->decrypted = $string;
        $encrypt->createPrefixSuffix();
        $encrypt->setCharsKey();
        $encrypt->encryptString();
        return $encrypt;
    }
    
    public static function decrypt($string,$key=NULL)
    {
        if (empty($key)) {$key = self::$defaultkey;}
        $decrypt = new self();
        $decrypt->setCharsKey();
        $decrypt->key = $key;
        $decrypt->string = $string;
        $decrypt->encrypted = $string;
        $decrypt->stripPrefixSuffix();
        $decrypt->setCharsKey();
        $decrypt->decryptString();
        return $decrypt;
    }
            
    // PRIVATE METHODS
    
    private function createPrefixSuffix()
    {
        // random number between 1 and strlen($chars)
        $p_randnum = rand(1,(strlen($this->charskey)));
        $s_randnum = rand(1,(strlen($this->charskey)));
        // get $randnum character
        $p_randchar = substr($this->charskey,($p_randnum-1),1);
        $s_randchar = substr($this->charskey,($s_randnum-1),1);
        // get num pos for first & last character of key
        $p_keyrandnum = strpos($this->charskey,substr($this->key,0,1))+1;
        $s_keyrandnum = strpos($this->charskey,substr($this->key,-1,1))+1;
        // get number of random characters to generate: keyrandnum - charrandnum
        if ($p_randnum >= $p_keyrandnum) {$p_numrandchars = $p_randnum - $p_keyrandnum;} else {$p_numrandchars = $p_keyrandnum - $p_randnum;}
        if ($s_randnum >= $s_keyrandnum) {$s_numrandchars = $s_randnum - $s_keyrandnum;} else {$s_numrandchars = $s_keyrandnum - $s_randnum;}
        $p_numrandchars+= 3;
        $s_numrandchars+= 3;
        // prefix begins with randchar
        $this->prefix = $p_randchar;
        $x=1;
        while($x < $p_numrandchars)
        {
            $x++;
            $this->prefix.= substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);
        }
        $y=1;
        while($y < $s_numrandchars)
        {
            $y++;
            $this->suffix.= substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);
        }
        // suffix ends with randchar
        $this->suffix.= $s_randchar;
    }
    
    private function stripPrefixSuffix()
    {
        // get string character position
        $p_charpos = strpos($this->charskey,substr($this->string,0,1))+1;
        $s_charpos = strpos($this->charskey,substr($this->string,-1,1))+1;
        // get num for first & last character of key
        $p_keyrandnum = strpos($this->charskey,substr($this->key,0,1))+1;
        $s_keyrandnum = strpos($this->charskey,substr($this->key,-1,1))+1;
        if ($p_charpos >= $p_keyrandnum) {$p_numrandchars = $p_charpos - $p_keyrandnum;} else {$p_numrandchars = $p_keyrandnum - $p_charpos;}
        if ($s_charpos >= $s_keyrandnum) {$s_numrandchars = $s_charpos - $s_keyrandnum;} else {$s_numrandchars = $s_keyrandnum - $s_charpos;}
        $p_numrandchars+= 3;
        $s_numrandchars+= 3;
        // set prefix/suffix
        $this->prefix = substr($this->string,0,($p_numrandchars));
        $this->suffix = substr($this->string,-($s_numrandchars));
        //strip pos characters from beginning and end
        $this->string = substr($this->string,($p_numrandchars),-($s_numrandchars));
    }
    
    private function encryptString()
    {
        if (!empty($this->key) AND !empty($this->string))
        {
            $this->setNoiseInterval();
            $strpos=0;
            $keypos=0;
            $noisepos=0;
            while($strpos < strlen($this->string))
            {
                // change offset direction
                if ($this->dir == 'f') {$this->dir = 'b';} else {$this->dir = 'f';}
                // if noisepos == $noiseinterval, add noise;
                if ($noisepos == $this->noiseinterval)
                {
                    // add random noise character(s)
                    $intnoise = substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);
                    if (strpos($this->charskey,$intnoise) > (strlen($this->charskey) / 2)) {$intnoise.= substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);}
                    $this->encrypted.= $intnoise;
                    $this->noise.= $intnoise;
                    $noisepos=0;
                }
                // if end of the key has been reached, reset to beginning
                if ($keypos >= strlen($this->key)) 
                {
                    $keypos=0;
                    // add random noise character(s)
                    $noise = substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);
                    if (strpos($this->charskey,$noise) > (strlen($this->charskey) / 2)) {$noise.= substr($this->charskey,rand(0,(strlen($this->charskey)-1)),1);}
                    $this->encrypted.= $noise;
                    $this->noise.= $noise;
                }
                #echo "strpos = $strpos<br>";
                #echo "keypos = $keypos<br>";
                // get string character
                $strchar = substr($this->string,$strpos,1);
                #echo "strchar = $strchar<br>";
                // get key character
                $keychar = substr($this->key,$keypos,1);
                #echo "keychar = $keychar<br>";
                // find this string character's position in $this->charskey
                $string_posinchars = strpos($this->charskey,$strchar);
                #echo "strchar $strchar pos in self::chars $string_posinchars<br>";
                // find this key character's position in $this->charskey
                $key_posinchars = strpos($this->charskey,$keychar);
                #echo "keychar $keychar pos in self::chars $key_posinchars<br>";
                if($string_posinchars !== FALSE AND $key_posinchars !== FALSE)
                {
                    // get offset character for character in string by character in key
                    if ($this->dir == 'f') 
                    {
                        $offset = $string_posinchars + ($key_posinchars+1); 
                    } 
                    else 
                    {
                        $offset = $string_posinchars - ($key_posinchars+1); 
                    }
                    #echo "offset: $offset<br>";
                    if ($offset > (strlen($this->charskey)-1)) 
                    {
                        $offset-= strlen($this->charskey);
                    }
                    if ($offset < 0)
                    {
                        $offset+= strlen($this->charskey);
                    }
                    #echo "adjusted offset: $offset<br>";
                    #echo "self::chars offset: ".($key_posinchars+1)." away from $strchar ($string_posinchars) in self::chars: ".substr($this->charskey,$offset,1)."<br><br>";
                    $this->encrypted.= substr($this->charskey,$offset,1);
                    $this->stripped_noise.= substr($this->charskey,$offset,1);
                }
                else
                {
                    $this->encrypted.= $strchar;
                }
                
                $strpos++;
                $keypos++;
                $noisepos++;
            }
            $this->encrypted = $this->prefix.$this->encrypted.$this->suffix;
            return $this->encrypted;
        }
    }
    
    private function decryptString()
    {
        if (!empty($this->string) AND !empty($this->key))
        {
            $this->setNoiseInterval();
            $strpos=0;
            $keypos=0;
            $noisepos=0;
            while($strpos < strlen($this->string))
            {
                // change offset direction
                if ($this->dir == 'f') {$this->dir = 'b';} else {$this->dir = 'f';}
                // if noisepos == $noiseinterval, add noise;
                if ($noisepos == $this->noiseinterval)
                {
                    // skip random noise character(s)
                    $intnoise = substr($this->string,$strpos,1);
                    if (strpos($this->charskey,$intnoise) > (strlen($this->charskey) / 2)) {$strpos++; $intnoise.= substr($this->string,$strpos,1);}
                    $this->noise.= $intnoise;
                    $strpos++;  
                    $noisepos=0;
                }
                // if end of the key has been reached, reset to beginning
                if ($keypos >= strlen($this->key)) 
                {
                    $keypos=0;
                    // skip random noise character(s)
                    $noise = substr($this->string,$strpos,1);
                    if (strpos($this->charskey,$noise) > (strlen($this->charskey) / 2)) {$strpos++; $noise.= substr($this->string,$strpos,1);}
                    $this->noise.= $noise;
                    $strpos++;             
                }
                // get string character
                $strchar = substr($this->string,$strpos,1);
                $this->stripped_noise.= $strchar;
                // get key character
                $keychar = substr($this->key,$keypos,1);
                // find this string character's position in $this->charskey
                $string_posinchars = strpos($this->charskey,$strchar);
                // find this key character's position in $this->charskey
                $key_posinchars = strpos($this->charskey,$keychar);
                if($string_posinchars !== FALSE AND $key_posinchars !== FALSE)
                {
                    // get offset character for character in string by character in key
                    if ($this->dir == 'f') 
                    {
                        $offset = $string_posinchars - ($key_posinchars+1); 
                    } 
                    else 
                    {
                        $offset = $string_posinchars + ($key_posinchars+1); 
                    }
                    if ($offset > (strlen($this->charskey)-1)) 
                    {
                        $offset-= strlen($this->charskey);
                    }
                    if ($offset < 0)
                    {
                        $offset+= strlen($this->charskey);
                    }
                    $this->decrypted.= substr($this->charskey,$offset,1);
                }
                $strpos++;
                $keypos++;
                $noisepos++;
            }
            return $this->decrypted;
        }
    }
    
    private function setCharsKey()
    {
        if (empty($this->prefix))
        {
            $this->charskey = self::$charskey1;
        }
        else
        {
            $keyfrom = substr($this->prefix,-1);
            $pos = strpos(self::$charskey1,$keyfrom);
            $len = strlen(self::$charskey1);
            if ($pos <= ($len/3))
            {
                $this->charskey = self::$charskey1;
            }
            elseif($pos <= 2*($len/3))
            {
                $this->charskey = self::$charskey2;
            }
            else
            {
                $this->charskey = self::$charskey3;
            }
        }
    }
    
    private function setNoiseInterval()
    {
        if (!empty($this->suffix)){$lastchar = substr($this->suffix,-1,1);}else{$lastchar = substr($this->string,-1,1);}
        $lastcharpos = strpos($this->charskey,$lastchar);
        $interval = $lastcharpos;
        while($interval > 16)
        {
            $interval = round($interval/2);
        }
        if ($interval < 2) {$interval = 2;}
        $this->noiseinterval = $interval;
    }
    
}

?>
