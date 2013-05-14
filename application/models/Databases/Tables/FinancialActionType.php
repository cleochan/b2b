<?php

class Databases_Tables_FinancialActionType extends Zend_Db_Table
{
    protected $_name = 'financial_action_type';
    
    function GetType()
    {
        $data = $this->fetchAll();
        
        $result = array();
        
        foreach($data as $d)
        {
            $result[$d['type_key']] = $d['type_value'];
        }
        
        return $result;
    }
}