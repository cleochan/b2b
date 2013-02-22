<?php

class Databases_Tables_HelpdeskCategory extends Zend_Db_Table
{
    protected $_name = 'helpdesk_category';
    
    function GetCategory()
    {
        $data = $this->fetchAll();
        
        $result = array();
        
        foreach($data as $d)
        {
            $result[$d['h_key']] = $d['h_value'];
        }
        
        return $result;
    }
}