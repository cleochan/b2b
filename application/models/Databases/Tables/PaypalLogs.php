<?php

class Databases_Tables_PaypalLogs extends Zend_Db_Table
{
    protected $_name = 'paypal_logs';
    var $user_id;
    var $params;
    
    function AddParams()
    {
        $data   =   array(
            'user_id'   =>   $this->user_id,
            'params'    =>   $this->params,
        );
        $this->insert($data);
    }
    
}