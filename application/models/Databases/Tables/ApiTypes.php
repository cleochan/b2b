<?php

class Databases_Tables_ApiTypes extends Zend_Db_Table
{
    protected $_name = 'api_types';
    var $api_types_id;
    var $api_types_name;
    
    function GetType()
    {
        if($this->api_types_id)
        {
            $row = $this->fetchRow("api_types_id='".$this->api_types_id."'");
            $result = $row->toArray();
        }else{
            $result = array();
        }
        
        return $result;
    }
    
    function GetTypeId()
    {
        $result = array();
        
        if($this->api_types_name)
        {
            $row = $this->fetchRow("api_types_name='".$this->api_types_name."'");
            $result = $row->toArray();
        }
        
        return $result['api_types_id'];
    }
    
}