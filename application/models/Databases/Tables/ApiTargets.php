<?php

class Databases_Tables_ApiTargets extends Zend_Db_Table
{
    protected $_name = 'api_targets';
    var $api_targets_id;
    
    function GetTarget()
    {
        if($this->api_targets_id)
        {
            $row = $this->fetchRow("api_targets_id='".$this->api_targets_id."'");
            $result = $row->toArray();
        }else{
            $result = array();
        }
        
        return $result;
    }
    
}