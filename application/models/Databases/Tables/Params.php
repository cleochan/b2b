<?php

class Databases_Tables_Params extends Zend_Db_Table
{
    protected $_name = 'params';
    
    function GetVal($key)
    {
        $result = $this->fetchRow("ckey='".$key."'");
        return $result->cval;
    }
}