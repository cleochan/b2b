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
    
    function PlaceOrderCheck()
    {
        $result = array(1 => "Y",
                                2 => "Approved"
                                );
        
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
        }elseif(!trim($this->supplier_sku))
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
            
            if($user_info['user_id'])
            {
                $result['user_id'] = $user_info['user_id'];
                $result['credit'] = $user_info['credit'];

                //calculate item price
                $product_filter_model = new Databases_Joins_ProductFilter();
                $prices = $product_filter_model->GetSkuPrices(trim($this->supplier_sku), $user_info['user_id']);
                
                if(NULL !== $prices['offer_price'])
                {
                    $order_amount = ( $prices['offer_price'] + $prices['shipping'] ) * trim($this->quantity);
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
        //Step 1: Insert into local db
        $data = array(
            "merchant_ref" => $this->merchant_ref,
            "order_amount" => $this->order_amount,
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
            "supplier_sku" => $this->supplier_sku,
            "merchant_sku" => $this->merchant_sku,
            "quantity" => $this->quantity,
            "shipping_method" => $this->shipping_method,
            "shipping_instruction" => $this->shipping_instruction,
            "serial_no" => $this->serial_no,
            "comments" => $this->comments
        );
        
        $logs_orders_id = $this->insert($data);
        
        return $logs_orders_id;
        
        //Step 2: Send XML via API
//        unset($data['ip']);
//        unset($data['operator_id']);
//        unset($data['issue_time']);
//        unset($data['order_amount']);
//        unset($data['product_name']);
//        unset($data['order_status']);
//        unset($data['shipping_email']);
//        
//        $data['b2b_ref'] = $logs_orders_id;
//        
//        $api_model = new Algorithms_Core_Api();
//        $api_model->api_target = 1; //Internal Admin
//        $api_model->api_type = 2; //PlaceOrder
//        $api_model->original_xml_array = $data;
//        
//        return $api_model->Push();
    }
    
    function UpdateOrder($type)
    {
        
    }
}