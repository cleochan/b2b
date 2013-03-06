<?php

class Databases_Tables_ProductBrands extends Zend_Db_Table
{
    protected $_name = 'product_brands';
    
    function GetAllBrands()
    {
        $rows = $this->fetchAll();
        
        $result = array();
        
        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                $result[$row['brand_id']] = $row['brand_name'];
            }
        }
        
        return $result;
    }
}