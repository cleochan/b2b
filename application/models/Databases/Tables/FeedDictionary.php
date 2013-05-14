<?php

class Databases_Tables_FeedDictionary extends Zend_Db_Table
{
    protected $_name = 'feed_dictionary';
    
    function DumpAll($status=NULL)
    {
        if(0 === $status)
        {
            $data = $this->fetchAll("status=0", "column_name ASC");
        }elseif(1 == $status){
            $data = $this->fetchAll("status=1", "column_name ASC");
        }else{
            $data = $this->fetchAll(array(),  "column_name ASC");
        }
        
        return $data->toArray();
    }
    
    function ArrayForReplacement()
    {
        $rows = $this->fetchAll();
        
        $result = array();
        
        foreach($rows as $row)
        {
            $key = "<{".$row['column_param']."}>";
            $result[$key] = $row['column_product_mapping'];
        }
        
        return $result;
    }
}