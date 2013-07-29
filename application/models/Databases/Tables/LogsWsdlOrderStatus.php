<?php

class Databases_Tables_LogsWsdlOrderStatus extends Zend_Db_Table
{
    protected $_name = 'logs_wsdl_order_status';
    var $logs_id    =   '';
    var $issue_time =   '';
    var $status_message_response    =   '';
    var $status_resquest            =   '';
    
    function AddLogs()
    {
        $data   =   array(
            'issue_time'    =>  date("Y-m-d H:i:s"),
            'status_message_response'   =>  $this->status_message_response,
            'status_resquest'           =>  $this->status_resquest,
        );
        try{
            $logs_id = $this->insert($data);
        }  catch (Zend_Exception $exp){
            var_dump($exp->getMessage());
        }
    }
}