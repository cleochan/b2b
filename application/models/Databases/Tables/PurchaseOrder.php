<?php

class Databases_Tables_PurchaseOrder extends Zend_Db_Table
{
    protected $_name = 'purchase_order';
    var $user_id;
    var $main_db_order_id;
    var $purchase_order_id;
    var $ip;
    var $shipping_company;
    var $shipping_first_name;
    var $shipping_last_name;
    var $shipping_address_1;
    var $shipping_address_2;
    var $shipping_suburb;
    var $shipping_state;
    var $shipping_postcode;
    var $shipping_country;
    var $shipping_phone;
    var $shipping_fax;
    var $order_amount;
    var $order_amount_change_value;
    var $order_amount_action; //1=Plus 2=Deduct
    var $pickup;
    
    
    function AddPurchaseOrder()
    {
        if($this->user_id)
        {
            $data = array(
                "issue_time" => date("Y-m-d H:i:s"),
                "user_id" => $this->user_id,
                "operator_id" => $_SESSION["Zend_Auth"]["storage"]->user_id,
                "ip" => $this->ip,
                "shipping_first_name" => $this->shipping_first_name,
                "shipping_last_name" => $this->shipping_last_name,
                "shipping_company" => $this->shipping_company,
                "shipping_address_1" => $this->shipping_address_1,
                "shipping_address_2" => $this->shipping_address_2,
                "shipping_suburb" => $this->shipping_suburb,
                "shipping_state" => $this->shipping_state,
                "shipping_postcode" => $this->shipping_postcode,
                "shipping_country" => $this->shipping_country,
                "shipping_phone" => $this->shipping_phone,
                "shipping_fax" => $this->shipping_fax,
                "order_amount" => $this->order_amount
            );
            if($this->pickup)
            {
                $data['pickup'] = $this->pickup;
            }
            
            $purchase_order_id = $this->insert($data);
            
            return $purchase_order_id;
        }
    }
    
    function UpdatePurchaseOrder()
    {
        $result = FALSE;
        
        if($this->purchase_order_id)
        {
            $row = $this->fetchRow("purchase_order_id = '".$this->purchase_order_id."'");

            if(!empty($row))
            {
                //update main_db_order_id
                if($this->main_db_order_id)
                {
                    $row->main_db_order_id = $this->main_db_order_id;
                }
                
                //update order amount
                if(NULL !== $this->order_amount_change_value && $this->order_amount_action)
                {
                    if(1 == $this->order_amount_action)
                    {
                        $row->order_amount = $row->order_amount + $this->order_amount_change_value;
                    }elseif(2 == $this->order_amount_action)
                    {
                        $row->order_amount = $row->order_amount - $this->order_amount_change_value;
                    }
                }
                $row->save();
                $result = TRUE;
            }
        }

        
        return $result;
    }
}