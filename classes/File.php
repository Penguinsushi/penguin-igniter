<?php 

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $file = File::handleUpload($_FILES['file'],'dir/');
// $file = new File('dir/file.ext');
// =============================================================================================================

class File

{
    
    // PROPERTIES
    
    public $dir = ""; // destination/permanent file directory
    public $name = ""; // destination/permanent filename
    public $size = ""; // file size (in bytes)
    public $ext = ""; // file extension (lower case)
    public $path = ""; // destination/permanent full file path
        
    // CONSTRUCTOR
    
    public function __construct($path=NULL)
    {
        if (!empty($path))
        {
            $this->path = $path;
            $pathchunks = explode("/",$path);
            $this->name = array_pop($pathchunks);
            $this->dir = implode("/",$pathchunks)."/";
            $namechunks = explode(".",$this->name);
            $this->ext = end($namechunks);
            $this->size = @filesize($this->path);
        }
    }
    
    // STATIC METHODS
    
    // move uploaded files to permanent directory, adjust permissions, return array of File objects just uploaded
    public static function handleUpload($upload,$dir,$rename=NULL)
    {
        // make some attempt to create directory if it does not exist (will fail if multiple non-existent levels)
        if (!is_dir($dir)) {mkdir($dir);}
        // if renaming, overwrite
        if (!empty($rename))
        {
            $newfile = TextString::valid_filename($rename);
        }
        // otherwise, add incremented prefix to keep files from overwriting
        else
        {
            $newfile = TextString::valid_filename($upload['name']);
            $inc=0;
            $incnewfile = $newfile;
            while(is_file($dir.$incnewfile))
            {
                $inc++;
                $incnewfile = $inc."_".$newfile;
            }
            $newfile = $incnewfile;
        }
        if (move_uploaded_file($upload['tmp_name'], $dir.$newfile))
        {
            chmod($dir.$newfile,0644);
            $file = new self($dir.$newfile);
            return $file;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function moveToDir($dir)
    {
        if (rename($this->path,$dir.$this->name))
        {
            $this->path = $dir.$this->name;
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function copyFile($name=NULL)
    {
        if (!empty($this->path))
        {
            if (empty($name))
            {
                $name = 'copy_of_'.$this->name;
            }
            $newfile = $this->dir.$name;
            copy($this->path,$newfile);
            $file = new self($newfile);
            return $file;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function deleteFile()
    {
        return unlink($this->path);
    }
    
    public function FTPPut($server,$user,$pass,$path,$mode=FTP_ASCII)
    {
        $return = FALSE;
        if ($conn_id = ftp_connect($server))
        {
            if ($login_result = ftp_login($conn_id, $user, $pass))
            {
                ftp_pasv($conn_id,TRUE);
                if (ftp_put($conn_id, $path, $this->path, $mode)) 
                {
                    $return = TRUE;
                }
            }
            ftp_close($conn_id);
        }
        return $return;
    }
    
}

?>
