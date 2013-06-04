<?php

class Databases_Tables_LogsBpay extends Zend_Db_Table
{
    protected $_name = 'logs_bpay';
    var $id;
    var $params;
    var $add_date;
    
    function AddParams()
    {
        $data   =   array(
            'id'   =>  $this->id,
            'params'    =>  $this->params,
            'add_date'    =>  $this->add_date,
        );
        $this->insert($data);
    }
    
}