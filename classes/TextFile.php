<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $textfile = new TextFile('textfile.txt');
// $textfile->writeToFile("write this text to the file");
// $textfile->setContents;
// echo $textfile->contents;
// =============================================================================================================

class TextFile extends File
{
    
    // PROPERTIES
    
    public $contents = ""; // textual contents of file
    
    // CONSTRUCTOR
    
    public function __construct($path=NULL)
    {
        parent::__construct($path);
        if (is_file($this->path)) {$this->setContents();}
    }
    
    
    // METHODS
    
    public function setContents()
    {
        $this->contents = file_get_contents($this->path);
    }
    
    public function writeLine($message,$linereturn="\n")
    {
        $handle = fopen($this->path,'a');
        fwrite($handle,$message.$linereturn);
        fclose($handle);
        $this->size = filesize($this->path);
    }
    
    public function writeToFile($message,$method='a')
    {
        $handle = fopen($this->path,$method);
        fwrite($handle,$message);
        fclose($handle);
        $this->size = filesize($this->path);
    }
    
    public function findText($string,$return_surrounding=100)
    {
        $this->setContents();
        if (empty($this->contents)) {return FALSE;}
        $pos = strpos($this->contents,$string);
        if ($pos !== FALSE)
        {
            $return['pos'] = $pos;
            $return['in_context'] = substr($contents,($pos - $return_surrounding),(strlen($string)+$pos+$return_surrounding));
            return $return;
        }
        return FALSE;
    }
    
}

?>
