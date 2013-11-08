<?php

class Databases_Tables_LogsOrders extends Zend_Db_Table
{
    protected $_name = 'logs_orders';
    var $start_date;
    var $end_date;
    var $user_id;
    var $main_order_id;
    var $order_status;
    var $p_query_order = "issue_time|DESC";
    var $p_qty_per_page = 50; //50 data / page
    var $p_current_page = 1; // start from the first page
    
    //Params for place order
    var $merchant_ref;
    var $shipping_first_name;
    var $shipping_last_name;
    var $shipping_company;
    var $merchant_company;
    var $shipping_address_1;
    var $shipping_address_2;
    var $shipping_suburb;
    var $shipping_state;
    var $shipping_postcode;
    var $shipping_country;
    var $shipping_phone;
    var $shipping_fax;
    var $shipping_email;
    var $supplier_sku;
    var $merchant_sku;
    var $quantity;
    var $shipping_method;
    var $shipping_instruction;
    var $tracking_number;
    var $serial_no;
    var $comments;
    var $product_name;
    var $order_amount;
    var $issue_time;
    var $operator_id;
    var $ip;
    var $group_instance_balance_array;
    
    var $logs_orders_id;
    var $api_response;
    
    var $purchase_order_id;
    var $logs_order_ids;
    
    var $shipping_courier;
    var $shipping_date;
    var $item_status;
    
    function Pagination()
    {
        //Get amount page qty
        $select = $this->select();
        $select->from($this->_name, array("count(logs_orders_id) as ct"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->main_order_id)
        {
            $select->where("main_order_id = ?", $this->main_order_id);
            
            $cond[] = "main_order_id=".$this->main_order_id;
        }
        if($this->order_status)
        {
            $select->where("order_status = ?", $this->order_status);
            $cond[] = "order_status=".$this->order_status;
        }
        
        $result = $this->fetchRow($select);
        
        $total_page = ceil($result['ct'] / $this->p_qty_per_page);
        
        //Generate HTML
        if($total_page > 1)
        {
            if(1 == $this->p_current_page)
            {
                $cond[] = "p_current_page=".($this->p_current_page+1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-order-report/user_id/".$this->user_id."?".$params."'>Next >></a>";
            }elseif($total_page == $this->p_current_page){
                $cond[] = "p_current_page=".($this->p_current_page-1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-order-report/user_id/".$this->user_id."?".$params."'><< Previous</a>";
            }else{
                $cond[] = "p_current_page=".($this->p_current_page-1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-order-report/user_id/".$this->user_id."?".$params."'><< Previous</a>";
                $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $cond = array_pop($cond);
                $cond[] = "p_current_page=".($this->p_current_page+1);
                $params_next = implode("&", $cond);
                $html .= "<a href='/admin/admin-order-report/user_id/".$this->user_id."?".$params_next."'>Next >></a>";
            }
        }else{
            $html = "";
        }
        
        return $html;
    }
    
    function PushList()
    {
        //Get amount page qty
        $select = $this->select();
        $cond = array();
        if($this->start_date)
        {
            $select->where("issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->main_order_id)
        {
            $select->where("main_order_id = ?", $this->main_order_id);
            
            $cond[] = "main_order_id=".$this->main_order_id;
        }
        if($this->order_status)
        {
            $select->where("order_status = ?", $this->order_status);
            $cond[] = "order_status=".$this->order_status;
        }
        if($this->p_query_order)
        {
            $qorder = explode("|", $this->p_query_order);
            $select->order($qorder[0]." ".$qorder[1]);
        }
        if($this->p_qty_per_page && $this->p_current_page)
        {
            $select->limit($this->p_qty_per_page, $this->p_qty_per_page*($this->p_current_page-1));
        }
        
        $data = $this->fetchAll($select);
        $result = $data->toArray();
        
        return $result;
    }
    
    function PostXmlToPlaceOrder()
    {
        $row_qty = 50;
        
        $select = $this->select();
        $select->where("order_status = ?", 0); //Pending
        $select->where("api_trying_times < ?", 2); //no more than twice
        $select->order("issue_time ASC");
        $select->limit($row_qty);
        $data = $this->fetchAll($select);
        
        $count = 0;
        $api_model = new Algorithms_Core_Api();
        
        if(!empty($data))
        {
            foreach($data as $d)
            {
                $original_xml_array = array(
                    "b2b_order_id" => $d['logs_orders_id'],
                    "merchant_ref" => $d['merchant_ref'],
                    "merchant_id" => $d['user_id'],
                    "supplier_sku" => $d['supplier_sku'],
                    "merchant_sku" => $d['merchant_sku'],
                    "quantity" => $d['quantity'],
                    "order_amount" => $d['order_amount'],
                    "buyer_first_name" => $d['shipping_first_name'],
                    "buyer_last_name" => $d['shipping_last_name'],
                    "buyer_company" => $d['shipping_company'],
                    "buyer_address_1" => $d['shipping_address_1'],
                    "buyer_address_2" => $d['shipping_address_2'],
                    "buyer_suburb" => $d['shipping_suburb'],
                    "buyer_state" => $d['shipping_state'],
                    "buyer_postcode" => $d['shipping_postcode'],
                    "buyer_country" => $d['shipping_country'],
                    "buyer_phone" => $d['shipping_phone'],
                    "buyer_fax" => $d['shipping_fax'],
                    "shipping_method" => $d['shipping_method'],
                    "shipping_instruction" => $d['shipping_instruction'],
                    "serial_no" => $d['serial_no'],
                    "comments" => $d['comments']
                );
                
                $api_model->api_target = 1; //Internal Admin
                $api_model->api_type = 2; //PlaceOrder
                $api_model->original_xml_array = $original_xml_array;

                $api_model->Push();
                
                //update trying times
                $get_row = $this->fetchRow("logs_orders_id = '".$d['logs_orders_id']."'");
                $get_row->api_trying_times += 1;
                $get_row->save();
            }
        }
        
        return $count;
    }
    
    function GetResponseToUpdateOrder($type) //$type 1=Update Status, 2=Others
    {
        $result = FALSE;
        
        if(1 == $type)
        {
            /**
             * $this->logs_orders_id
             * $this->order_status
             * $this->api_response
             * $this->tracking_number
             */
            
            if($this->logs_orders_id && $this->order_status)
            {
                $row = $this->fetchRow("logs_orders_id = '".$this->logs_orders_id."'");
                $row->order_status = $this->order_status;
                $row->api_response = $this->api_response;
                $row->tracking_number = $this->tracking_number;
                $row->save();
                $result = TRUE;
            }
        }
        
        return $result;
    }
    
    function GetLogsOrderList()
    {
        $result =   FALSE;
        if($this->purchase_order_id)
        {
             $row    =   $this->fetchAll("purchase_order_id = '".$this->purchase_order_id."'");
             $result    =   $row->toArray();
        }
        
        return $result;
    }
    
    function UpdateLogsOrder()
    {
        if($this->logs_order_ids){
            foreach ($this->logs_order_ids as $logs_order_id)
            {
                
                $row = $this->fetchRow("logs_orders_id = '".$logs_order_id."'");
                $row->item_status       =   $this->item_status;
                if($this->api_response)
                {
                    $row->api_response  =   $this->api_response;
                }
                if($this->item_status == 2)
                {
                    $row->api_trying_times  =   $row->api_trying_times + 1;
                }
                $row->save();
            }
        }
    }
    
    function UpdateLogsOrderStatus()
    {
        if($this->purchase_order_id)
        {
            $db     =   $this->getAdapter(); 
            $data   =   array(
                'api_response'  =>  $this->api_response,
                'item_status'   =>  $this->item_status,
            );
            $where  =   $db->quoteInto("purchase_order_id = ?", $this->purchase_order_id); 
            $this->update($data, $where);
        }
    }
    
    function UpdateLogsOrderShippingInfo()
    {
        $result =   FALSE;
        if($this->purchase_order_id && $this->supplier_sku){
            $where  =   " purchase_order_id = '".$this->purchase_order_id."' and  supplier_sku ='".$this->supplier_sku."'";
            $row = $this->fetchRow($where);
            if($row->logs_orders_id){
                if($this->item_status){
                    $row->item_status       =   $this->item_status;
                }
                if($this->shipping_courier){
                    $row->shipping_courier  =   $this->shipping_courier;
                }
                if($this->shipping_date){
                    $row->shipping_date     =   $this->shipping_date;
                }
                if($this->tracking_number){
                    $row->tracking_number   =   $this->tracking_number;
                }
                $row->save();
                $result   =   "logs_orders_id: ".$this->logs_orders_id.' Shipping data update success at: '.date('Y-m-d H:i:s');
            }else{
                $result = FALSE;
            }
        }
        return $result;
    }
}