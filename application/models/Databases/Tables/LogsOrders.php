<?php

class Databases_Tables_LogsOrders extends Zend_Db_Table
{
    protected $_name = 'logs_orders';
    var $start_date;
    var $end_date;
    var $user_id;
    var $limit;
    var $offset;
    var $order_id;
    var $order_status;
    
    
    function PushList()
    {
        if($this->user_id)
        {
            $select = $this->select();
            $select->where("user_id = ?", $this->user_id);
            $select->order("issue_time DESC");
            $data = $this->fetchAll($select);
            $result = $data->toArray();
        }else{
            echo "Invalid Action.";
            die;
        }
        
        return $result;
    }
}