<?php

class Databases_Joins_GetOrders
{
    var $start_date;
    var $end_date;
    var $user_id;
    var $main_order_id;
    var $item_status;
    var $p_query_order = "p.issue_time|DESC";
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
    var $item_amount;
    var $issue_time;
    var $operator_id;
    var $ip;
    var $pick_up;
    var $group_instance_balance_array;
    var $main_db_item_id;
    
    var $logs_orders_id;
    var $api_response;
    
    var $merchant_ref_pool;

    function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function Pagination()
    {
        //Get amount page qty
        $select = $this->db->select();
        $select->from("purchase_order as p", array("main_db_order_id", "issue_time", "user_id", "order_amount"));
        $select->joinLeft("logs_orders as o", "o.purchase_order_id=p.purchase_order_id", array("count(logs_orders_id) as ct", "merchant_ref", "item_status", "api_response", "item_amount", "supplier_sku", "merchant_sku", "quantity"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("p.issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("p.issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("p.user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->merchant_ref)
        {
            $select->where("o.merchant_ref = ?", $this->merchant_ref);
            
            $cond[] = "merchant_ref=".$this->merchant_ref;
        }
        if($this->item_status)
        {
            $select->where("o.item_status = ?", $this->item_status);
            $cond[] = "item_status=".$this->item_status;
        }
        
        $result = $this->db->fetchRow($select);
        
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
        $select = $this->db->select();
        $select->from("purchase_order as p", array("main_db_order_id", "issue_time", "user_id", "order_amount", "pickup"));
        $select->joinLeft("logs_orders as o", "o.purchase_order_id=p.purchase_order_id", array("merchant_ref", "item_status", "api_response", "item_amount", "supplier_sku", "merchant_sku", "quantity"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("p.issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("p.issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("p.user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->merchant_ref)
        {
            $select->where("o.merchant_ref = ?", $this->merchant_ref);
            
            $cond[] = "merchant_ref=".$this->merchant_ref;
        }
        if($this->item_status)
        {
            $select->where("item_status = ?", $this->item_status);
            $cond[] = "item_status=".$this->item_status;
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
        
        $result= $this->db->fetchAll($select);
        
        return $result;
    }
    
    function PlaceOrderCheck()
    {
        $result = array(1 => "Y",
                                2 => "Approved"
                                );
        
        if("Y" != $this->pick_up)
        {
            if(!trim($this->shipping_first_name))
            {
                $result = array(1 => "N",
                                        2 => "Shipping first name is required."
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_last_name))
            {
                $result = array(1 => "N",
                                        2 => "Shipping last name is required."
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_address_1))
            {
                $result = array(1 => "N",
                                        2 => "Shipping address 1 is required"
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_suburb))
            {
                $result = array(1 => "N",
                                        2 => "Shipping suburb is required"
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_state))
            {
                $result = array(1 => "N",
                                        2 => "Shipping state is required"
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_postcode))
            {
                $result = array(1 => "N",
                                        2 => "Shipping postcode is required"
                                        );
                $error = 1;
            }elseif(!trim($this->shipping_country))
            {
                $result = array(1 => "N",
                                        2 => "Shipping country is required"
                                        );
                $error = 1;
            }
        }
        
        if(!trim($this->supplier_sku))
        {
            $result = array(1 => "N",
                                    2 => "Supplier SKU is required"
                                    );
            $error = 1;
        }elseif(!trim($this->quantity))
        {
            $result = array(1 => "N",
                                    2 => "Quantity is required and above zero."
                                    );
            $error = 1;
        }elseif(!trim($this->operator_id))
        {
            $result = array(1 => "N",
                                    2 => "Operator ID is required"
                                    );
            $error = 1;
        }elseif(!trim($this->merchant_company))
        {
            $result = array(1 => "N",
                                    2 => "Merchant company is required"
                                    );
            $error = 1;
        }
        
        if(!$error) //passed all above then:
        {
            $users_extension_model = new Databases_Tables_UsersExtension();
            $users_extension_model->company = $this->merchant_company;
            $user_info = $users_extension_model->CheckCompanyInCsv();
            
            $params_model = new Databases_Tables_Params();
            $document_fee = $params_model->GetVal("document_fee");
            
            if($user_info['user_id'])
            {
                $result['user_id'] = $user_info['user_id'];
                $result['credit'] = $user_info['credit'];

                //calculate item price
                $product_filter_model = new Databases_Joins_ProductFilter();
                $prices = $product_filter_model->GetSkuPrices(trim($this->supplier_sku), $user_info['user_id']);
                
                if(NULL !== $prices['offer_price'])
                {
                    if("Y" == $this->pick_up)
                    {
                        if(NULL !== $this->group_instance_balance_array[$user_info['user_id']]) //has calculated document fee already
                        {
                            $order_amount = ( $prices['offer_price'] + $prices['handling_fee'] ) * trim($this->quantity);
                        }else{
                            $order_amount = ( $prices['offer_price'] + $prices['handling_fee'] ) * trim($this->quantity) + $document_fee;
                        }
                    }else{
                        $order_amount = ( $prices['offer_price'] + $prices['shipping'] ) * trim($this->quantity);
                    }
                    
                    $result['order_amount'] = $order_amount;

                    if(NULL !== $this->group_instance_balance_array[$user_info['user_id']])
                    {
                        $result['instant_balance'] = $this->group_instance_balance_array[$user_info['user_id']] - $order_amount;
                    }else{
                        $result['instant_balance'] = $user_info['balance'] - $order_amount;
                    }

                    if($result['credit'] < (0 - $result['instant_balance']))
                    {
                        $result[1] =  "N";
                        $result[2] =  "Out of balance";
                        $error = 1;
                    }
                }else{
                    $result[1] =  "N";
                    $result[2] =  "SKU is not found";
                    $error = 1;
                }

            }else{
                    $result[1] =  "N";
                    $result[2] =  "Merchant company is not found";
                    $error = 1;
            }
        }
        
        return $result;
    }
    
    function PlaceOrder()
    {
        $merchant_ref_pool = $this->merchant_ref_pool;
        $purchase_order_model = new Databases_Tables_PurchaseOrder();
        
        if(!$merchant_ref_pool[$this->merchant_ref])
        {
            //Step 1: Insert into purchase order table
            $purchase_order_model->user_id = $this->user_id;
            $purchase_order_model->ip = $this->ip;
            $purchase_order_model->shipping_first_name = $this->shipping_first_name;
            $purchase_order_model->shipping_last_name = $this->shipping_last_name;
            $purchase_order_model->shipping_company = $this->shipping_company;
            $purchase_order_model->shipping_address_1 = $this->shipping_address_1;
            $purchase_order_model->shipping_address_2 = $this->shipping_address_2;
            $purchase_order_model->shipping_suburb = $this->shipping_suburb;
            $purchase_order_model->shipping_state = $this->shipping_state;
            $purchase_order_model->shipping_postcode = $this->shipping_postcode;
            $purchase_order_model->shipping_country = $this->shipping_country;
            $purchase_order_model->shipping_phone = $this->shipping_phone;
            $purchase_order_model->shipping_fax = $this->shipping_fax;
            $purchase_order_model->order_amount = $this->order_amount;
            if("Y" == $this->pick_up)
            {
                $purchase_order_model->pickup = 1;
            }
            
            $purchase_order_id = $purchase_order_model->AddPurchaseOrder();
            
            $merchant_ref_pool[$this->merchant_ref] = $purchase_order_id;
        }else{ //update order amount
            $purchase_order_model->order_amount_change_value = $this->order_amount;
            $purchase_order_model->order_amount_action = 1; //Plus
            $purchase_order_model->UpdatePurchaseOrder();
        }
        
        //Step 2: Insert into logs_orders table
        $item_data = array(
            "purchase_order_id" => $merchant_ref_pool[$this->merchant_ref],
            "merchant_ref" => $this->merchant_ref,
            "item_amount" => $this->item_amount,
            "supplier_sku" => $this->supplier_sku,
            "merchant_sku" => $this->merchant_sku,
            "quantity" => $this->quantity,
            "shipping_method" => $this->shipping_method,
            "shipping_instruction" => $this->shipping_instruction,
            "serial_no" => $this->serial_no,
            "comments" => $this->comments
        );
        
        $logs_orders_id = $this->db->insert("logs_orders", $item_data);
        
        $result = array();
        $result['purchase_order_id'] = $merchant_ref_pool[$this->merchant_ref];
        $result['logs_orders_id'] = $logs_orders_id;
        $result['merchant_ref_pool'] = $merchant_ref_pool;
        
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
    
}