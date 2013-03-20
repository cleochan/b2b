<?php

class Databases_Tables_Countries extends Zend_Db_Table
{
    protected $_name = 'countries';
    
    function CountriesArray()
    {
        $rows = $this->fetchAll();
        
        $result = array();
        
        foreach($rows as $row)
        {
            $result[$row['country_code']] = $row['country_name'];
        }
        
        return $result;
    }
}