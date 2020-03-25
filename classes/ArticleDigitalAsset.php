<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $assets = ArticleDigitalAsset::getForArticle(101);
// echo 'thumbnail path: '.$assets['IMAGE'][0]->AssetThumbnailObject->path
// =============================================================================================================

class ArticleDigitalAsset
{
    
    // PROPERTIES
    
    public $AssetRowID = 0;
    public $AssetArticleRowID = 0;
    public $AssetDateTime = '';
    public $AssetType = ''; // IMAGE|DOC|AUDIO|VIDEO
    public $AssetTitle = '';
    public $AssetDescription = '';
    public $AssetNotes = '';
    public $AssetPath = ''; // accessible from server
    public $AssetURI = ''; // accessible from web
    public $AssetThumbnailURI = '';
    
    public $AssetFileObject = '';
    public $AssetThumbnailObject = '';
    
    public static $table = 'tblArticleDigitalAssets';
    public static $types = array('IMAGE','DOC','AUDIO','VIDEO');
    
    // CONSTRUCTOR
    
    public function __construct($setprop=array())
    {
        if (empty($setprop) OR in_array('setAssetFileObject',$setprop)) {$this->setAssetFileObject();}
    }
    
    // STATIC METHODS
    
    // returns var[AssetType] = object array
    public static function getForArticle($articlerowid)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        $sql->WHERE('AssetArticleRowID',$articlerowid);
        $sql->ORDER_BY('AssetDateTime','ASC');
        $da = $GLOBALS['db']->createDataObjects($sql->query,'ArticleDigitalAsset');
        $assets = array('IMAGE'=>array(),'DOC'=>array(),'AUDIO'=>array());
        foreach($da AS $key => $asset)
        {
            $assets[$asset->AssetType][] = $asset;
        }
        return $assets;
    }
    
    public static function getByRowID($rowid)
    {
        $id = intval($rowid);
        $sql = new SQLQuery(self::$table,'AssetRowID',$id);
        return $GLOBALS['db']->createDataObjects($sql->query,'ArticleDigitalAsset',NULL,NULL,'single');
    }
    
    public static function getByFilename($filename,$type=NULL)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        $sql->WHERE('AssetPath','%'.$filename,'LIKE');
        if (!empty($type)) {$sql->WHERE('AssetType',strtoupper($type));}
        return $GLOBALS['db']->createDataObjects($sql->query,'ArticleDigitalAsset',NULL,NULL,'single');
    }
    
    public static function insertAsset($fieldvalues)
    {
        return $GLOBALS['db']->dataInsert(self::$table,$fieldvalues);
    }
    
    public static function updateAsset($id,$fieldvalues)
    {
        return $GLOBALS['db']->dataUpdate(self::$table,'AssetRowID',$id,$fieldvalues);
    }
    
    public static function deleteAsset($id,$filedelete=TRUE)
    {
        if ($filedelete)
        {
            $asset = self::getByRowID($id);
            unlink($asset->AssetPath);
            if (!empty($asset->AssetThumbnailObject->path)) {unlink($asset->AssetThumbnailObject->path);}
        }
        return $GLOBALS['db']->dataDelete(self::$table,'AssetRowID',$id);
    }
    
    // create thumbnails for any files in directory that don't have them
    public static function ensureThumbnails($dir,$size=75)
    {
        if (substr($dir,-1,1) != '/') {$dir.='/';}
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
           if (substr($entry,0,10) != 'THUMBNAIL_')
           {
               if (is_file($dir.$entry) AND !is_file($dir.'THUMBNAIL_'.$entry))
               {
                   copy($dir.$entry,$dir.'THUMBNAIL_'.$entry);
                   $copy = new Image($dir.'THUMBNAIL_'.$entry);
                   $copy->resizeImage($size);
               }
           }
        }
        $d->close();
    }
    
    // PRIVATE METHODS
    
    private function setAssetFileObject()
    {
        if ($this->AssetType == 'IMAGE')
        {
            $this->AssetFileObject = new Image($this->AssetPath);
            $this->AssetThumbnailObject = new Image($this->AssetFileObject->dir.'THUMBNAIL_'.$this->AssetFileObject->name);
            $uri_chunks = explode('/',$this->AssetURI);
            $uri_file = array_pop($uri_chunks);
            $uri_path = implode('/',$uri_chunks);
            $this->AssetThumbnailURI = $uri_path.'/'.'THUMBNAIL_'.$uri_file;
        }
        else
        {
            $this->AssetFileObject = new File($this->AssetPath);
        }
    }
    
}

?>
