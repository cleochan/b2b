<?php

class Databases_Tables_PurchaseOrder extends Zend_Db_Table
{
    protected $_name = 'purchase_order';
    var $user_id;
    var $main_db_order_id;
    var $purchase_order_id;
    
    
    function AddPurchaseOrder()
    {
        if($this->user_id)
        {
            $data = array(
                "user_id" => $this->user_id,
                "issue_time" => date("Y-m-d H:i:s")
            );
            
            $purchase_order_id = $this->insert($data);
            
            return $purchase_order_id;
        }
    }
    
    function UpdatePurchaseOrder()
    {
        $result = FALSE;
        
        if($this->main_db_order_id && $this->purchase_order_id)
        {
            $row = $this->fetchRow("purchase_order_id = '".$this->purchase_order_id."'");

            if(!empty($row))
            {
                $row->main_db_order_id = $this->main_db_order_id;
                $row->save();
                $result = TRUE;
            }
        }
        
        return $result;
    }
}