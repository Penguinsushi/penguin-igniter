<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $image = Image::handleUpload($_FILES['image'],'dir/');
// $image = new Image('dir/image.jpg');
// =============================================================================================================

class Image extends File
{

	// PROPERTIES
	
        public $height = ""; // image height (px)
        public $width = ""; // image width (px)
        public $longdim = ""; // width|height - longer dimension
        public $aspect = ""; // aspect ratio width/height
	
	// CONSTRUCTOR
	
	public function __construct($path=NULL)
	{
            if (!empty($path))
            {
                parent::__construct($path);
                if($sizes = @getimagesize($this->path)) // assignment in if statement to avoid necessity of additional file/disk reads
                {
                    $this->width = $sizes[0];
                    $this->height = $sizes[1];
                    if ($this->height > $this->width) {$this->longdim = "height";} else {$this->longdim = "width";}
                    $this->aspect = $this->width/$this->height;
                }
            }
            else
            {
                return FALSE;
            }
	}
	
        // STATIC METHODS
        
        public static function handleUpload($upload,$dir,$rename=NULL,$thumbnail=NULL,$resize=NULL)
        {
            $file = parent::handleUpload($upload,$dir,$rename);
            $image = new self($file->path);
            if (!empty($resize))
            {
                $image->resizeImage($resize);
            }
            if (!empty($thumbnail))
            {
                copy($image->path,$image->dir."thumb_".$image->name);
                $thumbnailfile = new self($image->dir."thumb_".$image->name);
                $thumbnailfile->resizeImage($thumbnail);
            }
            return $image;
        }
        
        // METHODS
        
	public function resizeImage($resize) 
	{
            if ($this->longdim == 'height')
            {
                if ($this->height > $resize) {$n_height = $resize;$n_width = $resize*$this->aspect;}
            }
            else
            {
                if ($this->width > $resize) {$n_width = $resize;$n_height = $resize/$this->aspect;}
            }
            if (empty($n_height) AND empty($n_width) AND strtolower($this->ext) != "jpg" AND strtolower($this->ext) != "jpeg") {$n_height = $this->height; $n_width = $this->width;}
            
            if (!empty($n_width) AND !empty($n_height))
            {
                $destimg=imagecreatetruecolor($n_width,$n_height);
                if (strtolower($this->ext) == "jpg" OR strtolower($this->ext) == "jpeg") {
                $srcimg=imagecreatefromjpeg($this->path);
                } elseif (strtolower($this->ext) == "png") {
                $srcimg=imagecreatefrompng($this->path);
                } elseif (strtolower($this->ext) == "gif") {
                $srcimg=imagecreatefromgif($this->path);
                } elseif (strtolower($this->ext) == "bmp") {
                $srcimg=imagecreatefromwbmp($this->path);
                }
                imagecopyresampled($destimg,$srcimg,0,0,0,0,$n_width,$n_height,imagesx($srcimg),imagesy($srcimg));	
                if (strtolower($this->ext) != "jpg" AND strtolower($this->ext) != "jpeg")
                {
                    $this->ext = "jpg";
                    $nn = explode(".",$this->name);
                    array_pop($nn);
                    $this->name = implode(".",$nn).'.'.$this->ext;
                    $this->path = $this->dir.$this->name;
                }
                imagejpeg($destimg,$this->path,90);
                chmod($this->path,0666);
                $this->width = $n_width;
                $this->height = $n_height;
            }
                    
	}

}

?>