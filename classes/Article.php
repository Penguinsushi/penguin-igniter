<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// Article::getRecent(5,'BLOG_ENTRY');
// Article::getForMonth('2012-12','BLOG_ENTRY');
// =============================================================================================================

class Article
{
    
    // PROPERTIES
    
    // db field properties
    public $ArticleRowID = 0;
    public $ArticleParentRowID = 0;
    public $ArticleType = '';
    public $ArticleDateTime = '';
    public $ArticleAuthor = '';
    public $ArticleTitle = '';
    public $ArticleContent = '';
    public $ArticleTags = '';
    public $ArticlePublished = FALSE;
    
    // additional/derived properties
    public $Tags = array();
    public $Parent = ''; // parent article
    public $Children = array(); // array of child articles
    public $DigitalAssets = array(); //array of digital assets
    public $URLID = '';
    
    public static $table = 'tblArticles';
    
    // CONSTRUCTOR
    
    public function __construct($setprop=array())
    {
        if (!empty($this->ArticleRowID))
        {
            if (empty($setprop) OR in_array('setURLID',$setprop)) {$this->setURLID();}
            if (empty($setprop) OR in_array('setChildren',$setprop)) {$this->setChildren();}
            if ((empty($setprop) OR in_array('setParent',$setprop)) AND !empty($this->ArticleParentRowID)) {$this->setParent();}
            if (empty($setprop) OR in_array('setDigitalAssets',$setprop)) {$this->setDigitalAssets();}
            if ((empty($setprop) OR in_array('setTags',$setprop)) AND !empty($this->ArticleTags)) {$this->setTags();}
        }
    }
    
    // STATIC METHODS
    
    public static function getAll($type=NULL,$unpublished=FALSE,$order='ArticleDateTime')
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->ORDER_BY($order);
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getRecent($num=5,$days=7,$type=NULL,$page=1,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $daysago = date('Y-m-d H:i:s',mktime(0, 0, 0, date("m")  , date("d")-$days, date("Y")));
        $now = date('Y-m-d H:i:s');
        $sql->WHERE('ArticleDateTime',$now,'<=');
        $sql->WHERE('ArticleDateTime',$daysago,'>=');
        $sql->ORDER_BY('ArticleDateTime','DESC');
        $sql->LIMIT((($page-1)*$num),$num);
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getMostRecent($num=5,$type=NULL,$page=1,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $now = date('Y-m-d H:i:s');
        $sql->WHERE('ArticleDateTime',$now,'<=');
        $sql->ORDER_BY('ArticleDateTime','DESC');
        $sql->LIMIT((($page-1)*$num),$num);
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getUpcoming($type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $now = date('Y-m-d H:i:s');
        $sql->WHERE('ArticleDateTime',$now,'>=');
        $sql->ORDER_BY('ArticleDateTime','ASC');
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
       
    public static function getForMonth($month=NULL,$type=NULL,$unpublished=FALSE)
    {
        if (empty($month)) {$month = date("Y-m");}
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->WHERE('ArticleDateTime',$month.'%','LIKE');
        $sql->ORDER_BY('ArticleDateTime','DESC');
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getForTag($tag=NULL,$type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->WHERE('ArticleTags','%'.$tag.'%','LIKE');
        $sql->ORDER_BY('ArticleDateTime','DESC');
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getForSearch($search,$type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->SEARCH(array('ArticleAuthor','ArticleTitle','ArticleContent'),$search);
        $sql->ORDER_BY('ArticleDateTime','DESC');
        return $GLOBALS['db']->createDataObjects($sql->query,'Article');
    }
    
    public static function getRandom($type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->ORDER_BY('RAND()');
        $sql->LIMIT(0,1);
        return $GLOBALS['db']->createDataObjects($sql->query,'Article',NULL,NULL,'single');
    }
    
    public static function getArchiveMonths($type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->SELECT("DISTINCT CONCAT(YEAR(ArticleDateTime),'-',MONTH(ArticleDateTime)) AS ArticleMonth");
        $sql->FROM(self::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $sql->ORDER_BY('ArticleDateTime','DESC');
        return $GLOBALS['db']->getDataRows($sql->query);
    }
    
    public static function getTags()
    {
        $sql = new SQLQuery();
        $sql->SELECT('ArticleTags');
        $sql->FROM(self::$table);
        $rows = $GLOBALS['db']->getDataRows($sql->query);
        $tags = array();
        foreach($rows AS $k => $r)
        {
            if (!empty($r['ArticleTags']))
            {
                $thesetags = explode(',',$r['ArticleTags']);
                $thesetags_trimmed = array_map('trim',$thesetags);
                foreach($thesetags_trimmed AS $tag)
                {
                    if (!empty($tag))
                    {
                        if (empty($tags[$tag])) {$tags[$tag]=0;}
                        $tags[$tag]++;
                    }
                }
            }
        }
        ksort($tags);
        return $tags;
    }
    
    public static function getTotalCount($type=NULL,$unpublished=FALSE)
    {
        $sql = new SQLQuery();
        $sql->SELECT('COUNT(ArticleRowID) AS COUNT');
        $sql->FROM(Article::$table);
        if (!empty($type)) {$sql->WHERE('ArticleType',$type);}
        if ($unpublished != TRUE) {$sql->WHERE('ArticlePublished',TRUE);}
        $c = $GLOBALS['db']->getDataRows($sql->query,'single');
        return $c['COUNT'];
    }
    
    public static function getByRowID($rowid,$children=TRUE)
    {
        $id = intval($rowid);
        $sql = new SQLQuery(self::$table,'ArticleRowID',$id);
        $setprop = array('setDigitalAssets','setURLID','setTags','setParent');
        if ($children) {$setprop[] = 'setChildren';}
        return $GLOBALS['db']->createDataObjects($sql->query,'Article',array($setprop),NULL,'single');
    }
    
    // Data Admin Methods    
    public static function insertArticle($fieldvalues)
    {
        return $GLOBALS['db']->dataInsert(self::$table,$fieldvalues);
    }
    
    public static function updateArticle($id,$fieldvalues)
    {
        return $GLOBALS['db']->dataUpdate(self::$table,'ArticleRowID',$id,$fieldvalues);
    }
    
    public static function deleteArticle($id)
    {
        return $GLOBALS['db']->dataDelete(self::$table,'ArticleRowID',$id);
    }
            
    // METHODS
    
    private function setParent()
    {
        $this->Parent = self::getByRowID($this->ArticleParentRowID,FALSE);
    }
    
    private function setChildren()
    {
        $sql = new SQLQuery();
        $sql->FROM(self::$table);
        $sql->WHERE('ArticleParentRowID',$this->ArticleRowID);
        $sql->ORDER_BY('ArticleDateTime','ASC');
        $setprop = array('setChildren','setDigitalAssets','setURLID','setTags');
        $c = $GLOBALS['db']->createDataObjects($sql->query,'Article',array($setprop));
        foreach($c AS $key => $child)
        {
            $this->Children[$child->ArticleType][] = $child;
        }
    }
    
    private function setDigitalAssets()
    {
        $this->DigitalAssets = ArticleDigitalAsset::getForArticle($this->ArticleRowID);
    }
    
    private function setURLID()
    {
        $this->URLID = $this->ArticleRowID.'_'.String::for_url_segment($this->ArticleTitle);
    }
    
    private function setTags()
    {
        $tags = explode(',',$this->ArticleTags);
        foreach($tags AS $key => $tag)
        {
            $this->Tags[] = trim(strtolower($tag));
        }
    }
    
}

?>
