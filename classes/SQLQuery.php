<?php

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// $sql = new SQLQuery();
// $sql->SELECT('field1,field2');
// $sql->FROM('table');
// $sql->WHERE('field','value');
// echo $sql->query;
// =============================================================================================================

class SQLQuery
{

    // PROPERTIES
    
    public $query = "";
    
    private $select = array();
    private $from = "";
    private $joins = array();
    private $join_types = array();
    private $joins_on1 = array();
    private $joins_on2 = array();
    private $where = array();
    private $groupby = array();
    private $orderby = array();
    private $limit_offset = 0;
    private $limit_num = 0;
    
    // CONSTRUCTOR
    
    // constructor contains calls for simple SELECT query elements
    public function __construct($from=NULL,$where=NULL,$wherevalue=NULL)
    {
        if (!empty($from))
        {
            $this->from = $from;
            if (!empty($where) AND !empty($wherevalue))
            {
                $this->WHERE($where,$wherevalue);
            }   
            $this->setQuery();         
        }
    }
    
    // METHODS
    // SQL methods are upper case as a reference to the practice of putting SQL keywords in upper case within queries
    
    public function SELECT($select)
    {
        if (!empty($select))
        {
            $this->select[] = $select;
        } 
        $this->setQuery();
    }
    
    public function FROM($table)
    {
        if (!empty($table))
        {
            $this->from = $table;   
        }
        $this->setQuery();
    }
    
    public function JOIN($table,$on1,$on2,$type="INNER")
    {
        if (!empty($table) AND !empty($on1) AND !empty($on2))
        {
            $this->joins[] = $table;
            $this->join_types[] = $type;
            $this->joins_on1[] = $on1;
            $this->joins_on2[] = $on2;
        }
        $this->setQuery();
    }
    
    public function WHERE($where,$value=NULL,$op="=",$noenc=FALSE)
    {
        if (!empty($where))
        {
            $tw = "(";
            $tw.= $where;
            // allow custom WHERE clauses (for 'OR' mostly), but use value/op/noenc if defined
            if (!empty($value))
            {
                if ($noenc == TRUE) {$enc="";}else{$enc="'";}
                $tw.= " $op $enc".TextString::db_sanitize($value)."$enc";
            }
            $tw.= ")";
            $this->where[] = $tw;
        }
        $this->setQuery();
    }
    
    // specialized WHERE clause, filters rows by requiring records must match every piece of searchtext in at least one searchfield
    public function SEARCH($searchfields,$searchtext)
    {
        if (!empty($searchfields) AND !empty($searchtext))
        {
            $searchterms = explode('"',$searchtext);
            $searchquery='';
            $quotes = '';
            foreach($searchterms AS $s_key => $s_value)
            {
                if ($quotes == 'out') {$quotes = 'in';} else {$quotes = 'out';}
                $s_value = trim($s_value);
                if (!empty($s_value))
                {
                    if ($quotes == 'out')
                    {
                        $searchterms2 = TextString::words($s_value);
                        foreach($searchterms2 AS $s_key => $term)
                        {
                                $searchquery.=" AND (";
                                $withterms='';
                                foreach($searchfields AS $sf_key => $field)
                                {
                                        $withterms.=" OR $field LIKE '%".TextString::db_sanitize($term)."%'";
                                }
                                $withterms = substr($withterms,4);
                                $searchquery.= $withterms;
                                $searchquery.=")";
                        }
                    }
                    else
                    {
                        $searchquery.=" AND (";
                        $withterms='';
                        foreach($searchfields AS $sf_key => $field)
                        {
                                $withterms.=" OR $field LIKE '%".TextString::db_sanitize($s_value)."%'";
                        }
                        $withterms = substr($withterms,4);
                        $searchquery.= $withterms;
                        $searchquery.=")";
                    }
                }
            }
            $searchquery = substr($searchquery,5);
            $this->where[] = "(".$searchquery.")";
        }
        $this->setQuery();
    }
    
    public function GROUP_BY($fields)
    {
        if (!empty($fields))
        {
            $this->groupby[] = $fields;
        }
        $this->setQuery();
    }
    
    public function ORDER_BY($field,$dir='ASC')
    {
        if (!empty($field) AND !empty($dir))
        {
            $this->orderby[] = "$field $dir";
        }
        $this->setQuery();
    }
    
    public function LIMIT($offset,$num)
    {
        if (!empty($offset))
        {
            $this->limit_offset = intval($offset);
        }
        if (!empty($num))
        {
            $this->limit_num = intval($num);
        }
        $this->setQuery();
    }
    
    // PRIVATE METHODS
    
    private function setQuery()
    {
        if (!empty($this->from))
        {
            // set select
            if (!empty($this->select))
            {
                $SELECT="";
                foreach($this->select AS $s_key => $sel)
                {
                    $SELECT.=",$sel";         
                }
                $SELECT = substr($SELECT,1);
                $SELECT = " SELECT ".$SELECT." ";
            }
            else
            {
                $SELECT = " SELECT * ";
            }
            // set from
            $FROM = " FROM ".$this->from." ";
            // set joins
            if (!empty($this->joins))
            {
                foreach($this->joins AS $j_key => $join)
                {
                    $FROM.= " ".$this->join_types[$j_key]." JOIN $join ON ".$this->joins_on1[$j_key]." = ".$this->joins_on2[$j_key]." ";
                }
            }
            // set where
            if (!empty($this->where))
            {
                $WHERE="";
                foreach($this->where AS $w_key => $where)
                {
                    $WHERE.=" AND $where ";
                }
                $WHERE = substr($WHERE,4);
                $WHERE = " WHERE ".$WHERE." ";
            }
            else
            {
                $WHERE='';
            }
            // set group by
            if (!empty($this->groupby))
            {
                $GROUPBY="";
                foreach($this->groupby AS $g_key => $group)
                {
                    $GROUPBY.=",$group";
                }
                $GROUPBY = substr($GROUPBY,1);
                $GROUPBY = " GROUP BY ".$GROUPBY." ";
            }
            else
            {
                $GROUPBY = '';
            }
            // set order by
            if (!empty($this->orderby))
            {
                $ORDERBY="";
                foreach($this->orderby AS $o_key => $order)
                {
                    $ORDERBY.=",$order";
                }
                $ORDERBY = substr($ORDERBY,1);
                $ORDERBY = " ORDER BY ".$ORDERBY." ";
            }
            else
            {
                $ORDERBY = '';
            }
            // set limit
            if (!empty($this->limit_num))
            {
                $LIMIT = " LIMIT ".$this->limit_offset.",".$this->limit_num." ";
            }
            else
            {
                $LIMIT = '';
            }
            $this->query = "$SELECT $FROM $WHERE $GROUPBY $ORDERBY $LIMIT";
            return $this->query;
        } 
        else
        {
            return false;   
        }
    }

}

?>