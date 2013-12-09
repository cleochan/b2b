<?php

class MerchantController extends Zend_Controller_Action
{
    function init()
    {
        $this->db = Zend_Registry::get("db");
        $this->params = $this->_request->getParams();
        $this->params['user_id'] = $_SESSION["Zend_Auth"]["storage"]->user_id;
    }
	
    function preDispatch()
    {
        $auth = Zend_Auth::getInstance();
        $users = new Databases_Tables_Users();
        $user_info = $users->UserInfo();
        if(!$auth->hasIdentity() || !$user_info['user_type'])
        {
            $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
        }elseif(2 != $user_info['user_type']){
            $this->_redirect('/admin');
        }

        //get system title
        $get_title = new Databases_Tables_Params();
        $this->view->system_title = $get_title -> GetVal("system_title");
        $this->view->system_version = $get_title -> GetVal("system_version");
        $this->view->system_css     = $get_title->GetVal('system_css');
        $this->view->user_type      = $user_info['user_type'];
        
        //make top menu
        $menu = new Algorithms_Core_Menu();
        $top_menu = $menu->MenuForMerchant();
        $this->view->top_menu = $menu -> FormatMenu($top_menu, $this->getRequest()->getActionName());
    }
    
    /**
     * Dashboard of Merchant
     * show pending order of merchant
     */
    function indexAction()
    {
        $this->view->title = "Dashboard";        
        $get_user_info = new Databases_Joins_GetUserInfo();
        $this->view->user = $get_user_info->GetUserInfo($this->params['user_id']);
        /*Get Pending Order Info*/
        $getorders_model = new Databases_Joins_GetOrders();
        $getorders_model->item_status   =   0;
        $getorders_model->user_id      =    $this->params['user_id'];
        $this->view->list = $getorders_model->PushList();
    }
        
    /**
     * Financial Report
     * get params
     * PushList
     */
    function financialReportAction()
    {
        $this->view->title = "Financial Report";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Financial Report"));
        
        $logs_orders_model = new Databases_Tables_LogsFinancial();
        $logs_orders_model->user_id = $this->params['user_id'];
        if($this->params['start_date'])
        {
            $logs_orders_model->start_date = $this->params['start_date'];
            $this->view->start_date = $this->params['start_date'];
        }
        if($this->params['end_date'])
        {
            $logs_orders_model->end_date = $this->params['end_date'];
            $this->view->end_date = $this->params['end_date'];
        }
        if($this->params['action_type'])
        {
            $logs_orders_model->action_type = $this->params['action_type'];
            $this->view->action_type = $this->params['action_type'];
        }
        if($this->params['p_current_page'])
        {
            $logs_orders_model->p_current_page = $this->params['p_current_page'];
        }else{
            $logs_orders_model->p_current_page = 1; //from the first page
        }
        $this->view->list = $logs_orders_model->PushList();
        $this->view->pagination = $logs_orders_model->Pagination();
        
        $financial_action_type_model = new Databases_Tables_FinancialActionType();
        $financial_action_type = $financial_action_type_model -> GetType();
        $this->view->financial_action_type = $financial_action_type;
    }
    
    /**
     * Merchant Order Report
     */
    function orderReportAction()
    {
    	$this->view->title = "Order Report";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Order Report"));
        
        $getorders_model = new Databases_Joins_GetOrders();
        $getorders_model->user_id = $this->params['user_id'];
        if($this->params['start_date'])
        {
            $getorders_model->start_date = $this->params['start_date'];
            $this->view->start_date = $this->params['start_date'];
        }
        if($this->params['end_date'])
        {
            $getorders_model->end_date = $this->params['end_date'];
            $this->view->end_date = $this->params['end_date'];
        }
        if($this->params['merchant_ref'])
        {
            $getorders_model->merchant_ref = $this->params['merchant_ref'];
            $this->view->merchant_ref = $this->params['merchant_ref'];
        }
        if($this->params['p_current_page'])
        {
            $getorders_model->p_current_page = $this->params['p_current_page'];
        }else{
            $getorders_model->p_current_page = 1; //from the first page
        }
        if(is_numeric($this->params['item_status'])){
            $getorders_model->item_status   =   $this->params['item_status'];
            $this->view->item_status = $this->params['item_status'];
        }
        $this->view->list = $getorders_model->PushList();
        $this->view->pagination = $getorders_model->Pagination();
        $order_amount   =   $getorders_model->getAllOrderAmountTotal();
        $this->view->order_total    =   $order_amount['order_total'];
        
        if($this->params['notice'])
        {
            switch($this->params['notice'])
            {
                case "E1":
                    $this->view->notice = "<font color='red'>No order was added in the action, please contact the sytem administrator. E1</font>";
                    break;
                case "E2":
                    $this->view->notice = "<font color='red'>One or more orders were ignored from the action, please contact the administrator. E2</font>";
                    break;
                case "E3":
                    $this->view->notice = "<font color='red'>One or more orders were ignored from the action, please contact the administrator. E3</font>";
                    break;
                case "S1":
                    $this->view->notice = "<font color='green'>Orders have been placed successfully.</font>";
                    break;
                default :
                    $this->view->notice = "";
                    break;
            }
        }
    }
    
    /**
     * Help List
     */
    function helpAction()
    {
        $this->view->title = "Help";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help"));
        
        $helpdesk_model = new Databases_Tables_Helpdesk();
        
        //Documentation
        $helpdesk_model->category = 1;
        $helpdesk_model->h_status = 1;
        $this->view->documentation = $helpdesk_model->PushList();
        
        //FAQ
        $helpdesk_model->category = 2;
        $this->view->faq = $helpdesk_model->PushList();
        
        //Contact Us
        $helpdesk_model->category = 3;
        $helpdesk_model->helpdesk_id = 3;
        $ct = $helpdesk_model->PushList();
        foreach($ct as $ct_val)
        {
            $this->view->ct = $ct_val['h_contents'];
        }
    }
    
    /**
     * Help Details
     */
    function helpDetailsAction()
    {
        $helpdesk_model = new Databases_Tables_Helpdesk();
        $helpdesk_model->helpdesk_id = $this->params['helpdesk_id'];
        $help_info = $helpdesk_model->GetHelpInfo();
        
        $this->view->title = "Help Details - ".$help_info['h_subject'];
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help", "Help Details|".$this->params['helpdesk_id']));
        
        $this->view->help_info = $help_info;
    }
    
    /**
     * Recharge
     */
    function rechargeAction()
    {
        $this->view->title = "Recharge";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Recharge"));
        
        $user_info = new Databases_Joins_GetUserInfo();
        $this->view->user = $user_info->GetUserInfo($this->params['user_id']);
        
        $bpay_model = new Algorithms_Extensions_Bpay();
        $this->view->biller_code = $bpay_model->GetBillerCode();
    }
    
    /**
     * Place Order
     */
    function placeOrderAction()
    {
        $this->view->title = "Place Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Place Order"));
        
        if(!$_SESSION['place_order'])
        {
            $_SESSION['place_order'] = array();
        }
        
        //get countries
        $countries_model = new Databases_Tables_Countries();
        $this->view->countries = $countries_model->CountriesArray();
    }
    
    /**
     * place Order Item Form
     * get sku
     * set item session
     */
    function placeOrderItemFormAction()
    {
        if($this->params['supplier_sku'])
        {
			if(!is_array($_SESSION['place_order']['items']))
			{
				$_SESSION['place_order']['items'] = array();
			}

			$arr = array(
                "supplier_sku" => $this->params['supplier_sku'],
                "merchant_sku" => $this->params['merchant_sku'],
                "quantity" => $this->params['quantity'],
                "merchant_ref" => $this->params['merchant_ref'],
                "shipping_method" => $this->params['shipping_method'],
                "shipping_instruction" => $this->params['shipping_instruction'],
                //"tracking_number" => $this->params['tracking_number'],
                //"serial_no" => $this->params['serial_no'],
                "comments" => $this->params['comments']
            );

			// is numeric and bigger than 0 == edit, else add
			if(is_numeric($this->params['id']) && isset($_SESSION['place_order']['items'][$this->params['id']])) 
		    {
		        $_SESSION['place_order']['items'][$this->params['id']] = $arr;
		    }
			else
			{
            	$_SESSION['place_order']['items'][] = $arr;
			}

			array_splice($_SESSION['place_order']['items'], 0, 0);
        }
        
        $this->_redirect("/merchant/place-order");
    }
    
    /**
     * place Order Remove Item
     * unset session data
     */
    function placeOrderRemoveItemAction()
    {
        if(is_numeric($this->params['id']))
        {
            unset($_SESSION['place_order']['items'][$this->params['id']]);
			array_splice($_SESSION['place_order']['items'], 0, 0);
        }
        
        $this->_redirect("/merchant/place-order");
    }
    
    /**
     * Place Order Preview
     * get params
     * set order data array
     * start loop
     * validation
     * finish loop
     */
    function placeOrderPreviewAction()
    {
        $this->view->title = "Place Order Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Place Order", "Place Order Preview"));
        
        //make $data_array
        $data_array = array();
        $getorders_model = new Databases_Joins_GetOrders();
        $user_extension_model = new Databases_Tables_UsersExtension();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $user_info = $user_extension_model->UserInfo();
        $group_instance_balance_array = array();
        
        $system_params_model    =   new Databases_Tables_Params();
        /**
         * Get paypal info
         */
        $this->view->paypal_url         =   $system_params_model->GetVal('paypal_url');
        $this->view->paypal_account     =   $system_params_model->GetVal('paypal_account');
        $this->view->paypal_return_url  =   $system_params_model->GetVal('paypal_return_url');
        $quantity_array =   array();
        //save delivery
        if(!$_SESSION['place_order']['delivery'])
        {
            $_SESSION['place_order']['delivery'] = array();
        }
        
        $_SESSION['place_order']['delivery'] = array(
            "shipping_first_name" => $this->params['shipping_first_name'],
            "shipping_last_name" => $this->params['shipping_last_name'],
            "merchant_company" => $user_info['company'],
            "shipping_company" => $this->params['shipping_company'],
            "shipping_address_1" => $this->params['shipping_address_1'],
            "shipping_address_2" => $this->params['shipping_address_2'],
            "shipping_suburb" => $this->params['shipping_suburb'],
            "shipping_state" => $this->params['shipping_state'],
            "shipping_postcode" => $this->params['shipping_postcode'],
            "shipping_country" => $this->params['shipping_country'],
            "shipping_phone" => $this->params['shipping_phone'],
            "pickup" => $this->params['pickup']
        );
        
        if($_SESSION['place_order']['items'])
        {
            foreach($_SESSION['place_order']['items'] as $items)
            {
                $data_array[] = array(
                    "shipping_first_name" => $this->params['shipping_first_name'],
                    "shipping_last_name" => $this->params['shipping_last_name'],
                    "shipping_company" => $this->params['shipping_company'],
                    "merchant_company" => $user_info['company'],
                    "shipping_address_1" => $this->params['shipping_address_1'],
                    "shipping_address_2" => $this->params['shipping_address_2'],
                    "shipping_suburb" => $this->params['shipping_suburb'],
                    "shipping_state" => $this->params['shipping_state'],
                    "shipping_postcode" => $this->params['shipping_postcode'],
                    "shipping_country" => $this->params['shipping_country'],
                    "shipping_phone" => $this->params['shipping_phone'],
                    "supplier_sku" => $items['supplier_sku'],
                    "merchant_sku" => $items['merchant_sku'],
                    "quantity" => $items['quantity'],
                    "merchant_ref" => $items['merchant_ref'],
                    "shipping_method" => $items['shipping_method'],
                    "shipping_instruction" => $items['shipping_instruction'],
                    "comments" => $items['comments'],
                    "pick_up" => $this->params['pickup']
                );
            }
        }
        
        /**
         * after paypal success , get order info from session
         */
        if ($_SESSION['place_order'][$this->params['sessionid']]&& $this->params['sessionid'])
        {
            $data_array =   $_SESSION['place_order'][$this->params['sessionid']];
            
             foreach($data_array as $da_key => $da_val)
            {
                if(substr($da_val['supplier_sku'], -3) == '-TP'){
                   $da_val['supplier_sku']    =   substr($da_val['supplier_sku'], 0, -3);
                }
                $getorders_model->shipping_first_name = $da_val['shipping_first_name'];
                $getorders_model->shipping_last_name = $da_val['shipping_last_name'];
                $getorders_model->shipping_company = $da_val['shipping_company'];
                $getorders_model->merchant_company = $da_val['merchant_company'];
                $getorders_model->shipping_address_1 = $da_val['shipping_address_1'];
                $getorders_model->shipping_suburb = $da_val['shipping_suburb'];
                $getorders_model->shipping_state = $da_val['shipping_state'];
                $getorders_model->shipping_postcode = $da_val['shipping_postcode'];
                $getorders_model->shipping_country = $da_val['shipping_country'];
                $getorders_model->supplier_sku = $da_val['supplier_sku'];
                $getorders_model->quantity = $da_val['quantity'];
                $getorders_model->operator_id = $this->params['user_id'];
                $getorders_model->pick_up = $da_val['pick_up'];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];
                $getorders_model->shipping_phone    =   $da_val['shipping_phone'];
                $quantity_array[$da_val['supplier_sku']]    +=   $da_val['quantity'];
                $getorders_model->quantity_array    =   $quantity_array;
                
                $check_result = $getorders_model->PlaceOrderCheck();

                $data_array[$da_key]['result'] = $check_result[1];
                $data_array[$da_key]['reason'] = $check_result[2];
                $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                $data_array[$da_key]['credit'] = $check_result['credit'];
                $data_array[$da_key]['user_id'] = $check_result['user_id'];
                $product_info   =   $product_filter_model->getProductInfo($da_val['supplier_sku']);
                $data_array[$da_key]['product_name']    =   $product_info['product_name'];
                $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                $data_array[$da_key]['supplier_sku']    =   $product_info['supplier_sku'];
                //update instant balance
                if ($check_result[2]=="Out of balance"):
                    $this->view->ifpay=1;
                endif;
                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
            }
            $this->view->flat_paypal    =   1;
            $this->view->notice_paypal  =   "PayPal Rechange successfully.";
            $this->view->list = $data_array;
        }elseif(!empty($data_array))
        {
            /**
             * foreach to check orders
             */
            foreach($data_array as $da_key => $da_val)
            {
                $count_column = count($da_val);
                if(19 != $count_column) //Reject due to the column amount
                {
                    $data_array[$da_key]['result'] = "N";
                    $data_array[$da_key]['reason'] = "Column Amount Error.";
                }else{ //check contents
                    $getorders_model->shipping_first_name = $da_val['shipping_first_name'];
                    $getorders_model->shipping_last_name = $da_val['shipping_last_name'];
                    $getorders_model->shipping_company = $da_val['shipping_company'];
                    $getorders_model->merchant_company = $da_val['merchant_company'];
                    $getorders_model->shipping_address_1 = $da_val['shipping_address_1'];
                    $getorders_model->shipping_suburb = $da_val['shipping_suburb'];
                    $getorders_model->shipping_state = $da_val['shipping_state'];
                    $getorders_model->shipping_postcode = $da_val['shipping_postcode'];
                    $getorders_model->shipping_country = $da_val['shipping_country'];
                    $getorders_model->supplier_sku = $da_val['supplier_sku'];
                    $getorders_model->quantity = $da_val['quantity'];
                    $getorders_model->operator_id = $this->params['user_id'];
                    $getorders_model->pick_up = $this->params['pickup']?"Y":"N"; 
                    $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                    $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];
                    $quantity_array[$da_val['supplier_sku']]    +=   $da_val['quantity'];
                    $getorders_model->quantity_array    =   $quantity_array;
                    $getorders_model->shipping_phone    =   $da_val['shipping_phone'];
                    /**
                     * @todo PlaceOrderCheck
                     */
                    $check_result = $getorders_model->PlaceOrderCheck();

                    $data_array[$da_key]['pick_up']    =   $this->params['pickup']?"Y":"N";
                    $data_array[$da_key]['result'] = $check_result[1];
                    $data_array[$da_key]['reason'] = $check_result[2];
                    
                    $data_array[$da_key]['order_amount']    =   (float)$check_result['order_amount'];
                    $data_array[$da_key]['instant_balance'] =   (float)$check_result['instant_balance'];

                    $data_array[$da_key]['credit'] = $check_result['credit'];
                    $data_array[$da_key]['user_id'] = $check_result['user_id'];
                    
                    $product_info   =   $product_filter_model->getProductInfo($da_val['supplier_sku']);
                    $data_array[$da_key]['product_name']    =   $product_info['product_name'];
                    $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                    
                    //update instant balance
                    if ($check_result[3]==1 ):
                        $this->view->iferror=1;
                    endif;
                    //update instant balance
                    if ($check_result[2]=="Out of balance"):
                        $this->view->ifpay=1;
                    endif;
                    if($check_result['user_id'])
                    {
                        $group_instance_balance_array[$check_result['user_id']] = (float)$check_result['instant_balance'];
                    }
                }
            }
            
            $sessionId  =   base64_encode($this->params['user_id'].date('YmdHis'));
            $_SESSION['place_order'][$sessionId]   =   $data_array;
            
            $this->view->sessionId  =   $sessionId;
            $this->view->userId     =   $this->params['user_id'];
        }
        
        $this->view->list = $data_array;
    }
    
    /**
     * Place Order Confirm
     * start loop
     * validation
     * insert into orders
     * finish loop
     * get orders
     * start loop
     * get order items
     * operate_orders_model->placeorder
     */
    function placeOrderConfirmAction()
    {
        /**
         * start loop
         * validation
         * insert into orders
         * update financial table
         * finish loop
         */
        
        $this->view->title = "Place Order Confirmation";
        $params = $this->_request->getParams();
        //Algorithms_Extensions_Plugin::FormatArray($params);die;
        $getorders_model = new Databases_Joins_GetOrders();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $ip = $plugin_model->GetIp();
        $notice = "S1"; //success
        $users_extension_model = new Databases_Tables_UsersExtension();
        if(count($params['supplier_sku']))
        {
            $group_instance_balance_array = array();
            $merchant_ref_pool = array();
            
            foreach($params['supplier_sku'] as $loop_key => $supplier_sku)
            {
                //Validation
                $getorders_model->shipping_first_name = $params['shipping_first_name'][$loop_key];
                $getorders_model->shipping_last_name = $params['shipping_last_name'][$loop_key];
                $getorders_model->shipping_company = $params['shipping_company'][$loop_key];
                $getorders_model->merchant_company = $params['merchant_company'][$loop_key];
                $getorders_model->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                $getorders_model->shipping_suburb = $params['shipping_suburb'][$loop_key];
                $getorders_model->shipping_state = $params['shipping_state'][$loop_key];
                $getorders_model->shipping_postcode = $params['shipping_postcode'][$loop_key];
                $getorders_model->shipping_phone = $params['shipping_phone'][$loop_key];
                $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                $getorders_model->supplier_sku = $supplier_sku;
                $getorders_model->quantity = $params['quantity'][$loop_key];
                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $getorders_model->pick_up = $params['pick_up'][$loop_key];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                $users_extension_model->company = $params['merchant_company'][$loop_key];
                $user_info = $users_extension_model->CheckCompanyInCsv();
                $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];

                $check_result = $getorders_model->PlaceOrderCheck();
                
                if("Y" == $check_result[1]) //passed the validation
                {
                    $order_amount = $check_result['order_amount'];
                    $instant_balance = $check_result['instant_balance'];
                    $user_id = $check_result['user_id'];
                    //update instant balance
                    $group_instance_balance_array[$user_id] = $instant_balance;
                    //Insert Into Orders
                    $getorders_model->merchant_ref = $params['merchant_ref'][$loop_key];
                    $getorders_model->order_amount = $order_amount;
                    $getorders_model->user_id = $user_id;
                    $getorders_model->ip = $ip;
                    $getorders_model->shipping_first_name = $params['shipping_first_name'][$loop_key];
                    $getorders_model->shipping_last_name = $params['shipping_last_name'][$loop_key];
                    $getorders_model->shipping_company = $params['shipping_company'][$loop_key];
                    $getorders_model->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                    $getorders_model->shipping_address_2 = $params['shipping_address_2'][$loop_key];
                    $getorders_model->shipping_suburb = $params['shipping_suburb'][$loop_key];
                    $getorders_model->shipping_state = $params['shipping_state'][$loop_key];
                    $getorders_model->shipping_postcode = $params['shipping_postcode'][$loop_key];
                    $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                    $getorders_model->shipping_phone = $params['shipping_phone'][$loop_key];
                    //->shipping_fax = $params['shipping_fax'][$loop_key];
                    $getorders_model->supplier_sku = $params['supplier_sku'][$loop_key];
                    $getorders_model->merchant_sku = $params['merchant_sku'][$loop_key];
                    $getorders_model->quantity = $params['quantity'][$loop_key];
                    $getorders_model->shipping_method = $params['shipping_method'][$loop_key];
                    $getorders_model->shipping_instruction = $params['shipping_instruction'][$loop_key];
                    $getorders_model->serial_no = $params['serial_no'][$loop_key];
                    $getorders_model->comments = $params['comments'][$loop_key];
                    $getorders_model->pick_up = $params['pick_up'][$loop_key];
                    $getorders_model->merchant_ref_pool = $merchant_ref_pool;
                    $getorders_model->discount_amount   =   round($check_result['discount_amount'],2);
                    $getorders_model->shipping_cost   =   round($check_result['shipping_cost'],2);
                    $getorders_model->item_amount   =   $order_amount;
                    $sku_prices_info    =   $product_filter_model->GetSkuPrices($params['supplier_sku'][$loop_key], $user_id);
                    
                    if($params['flat_paypal'])
                    {
                        $getorders_model->payment_type_id   =   5;
                    }else
                    {
                        $getorders_model->payment_type_id   =   9;
                    }
                    
                    $getorders_model->expected_item_cost    =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_item_cost       =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_ship_cost       =   round($check_result['shipping_cost'],2);
                    $getorders_model->ship_cost             =   round($check_result['shipping_cost'],2);
                    $getorders_model->shipping_courier      =   trim($sku_prices_info['shipping_courier']);
                    $getorders_model->sc_class              =   trim($sku_prices_info['sc_class']);
                    try{
                        /**
                         * @todo PlaceOrder
                         */
                        $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                    }catch (Zend_Exception $exp){
                        var_dump($exp->getMessage());
                    }
                    //update merchant ref pool
                    $merchant_ref_pool = $place_order_return['merchant_ref_pool'];

                }else{
                    $notice = "E2";
                }
            }
            $purchase_order_ids =   implode(',',$merchant_ref_pool);
            $operate_orders_model   =   new Databases_Joins_OperateOrders();
            $operate_orders_model->purchase_order_ids   =   $purchase_order_ids;
            /**
             * @var $operate_orders_model Databases_Joins_OperateOrders
             * @todo PlaceOrder
             */
            $result = $operate_orders_model->PlaceOrder();
            
        }else{
            $notice = "E1";
        }
        //unset session
        //die();
        unset($_SESSION['place_order']);
        $this->_redirect("/merchant/order-report/notice/".$notice);
    }
    
    function importOrderAction()
    {
        $this->view->title = "Import Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Import Order"));
    }
    
    /**
     * Import Order Preview
     * get order data array
     * start loop
     * validation
     * finish loop
     */
    function importOrderPreviewAction()
    {
        /**
         *  Column A: $data[0] = Your Record #
         *  Column B: $data[1] = First Name
         *  Column C: $data[2] = Last Name
         *  Column D: $data[3] = Shipping Company
         *  Column E: $data[4] = Address 1
         *  Column F: $data[5] = Address 2
         *  Column G: $data[6] = Suburb
         *  Column H: $data[7] = State
         *  Column I: $data[8] = Postcode
         *  Column J: $data[9] = Country
         *  Column K: $data[10] = Phone
         *  Column L: $data[11] = Fax
         *  Column M: $data[12] = Supplier Item Code
         *  Column N: $data[13] = Topbuy SKU
         *  Column O: $data[14] = Qty
         *  Column P: $data[15] = Shipping Method
         *  Column Q: $data[16] = Shipping Instruction
         *  Column R: $data[17] = Tracking Number
         *  Column S: $data[18] = Serials No
         *  Column T: $data[19] = Comments
         *  Column U: $data[20] = Merchant Company  // REQUIRED AND IMPORTANT !!!
         *  Column V: $data[21] = Pick Up // Y or N
         */
        $valid_file_types = array('application/octet-stream',
                                  'text/csv',
                                  'application/vnd.ms-excel'
                            );
        
        $this->view->title = "Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Import Order", "Import Order Preview"));
        
        $group_instance_balance_array = array();
        $getorders_model = new Databases_Joins_GetOrders();
        $user_extension_model = new Databases_Tables_UsersExtension();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $user_info = $user_extension_model->UserInfo();
        $system_params_model    =   new Databases_Tables_Params();        
        $this->view->paypal_url         =   $system_params_model->GetVal('paypal_url');
        $this->view->paypal_account     =   $system_params_model->GetVal('paypal_account');
        $this->view->paypal_return_url  =   $system_params_model->GetVal('paypal_return_url');
        
        /**
         * after paypal success reload order info from session
         */
        if ($_SESSION['import_order'][$this->params['sessionid']]&& $this->params['sessionid'])
        {
            $data_array =   $_SESSION['import_order'][$this->params['sessionid']];
             foreach($data_array as $da_key => $da_val)
            {
                $count_column = count($da_val);
                if(substr($da_val[11], -3) == '-TP'){
                    $da_val[11]    =   substr($da_val[11], 0, -3);
                }
                $getorders_model->shipping_first_name = $da_val[1];
                $getorders_model->shipping_last_name = $da_val[2];
                $getorders_model->shipping_company = $da_val[3];
                $getorders_model->merchant_company = $da_val[17]; // REQUIRED AND IMPORTANT !!!
                $getorders_model->shipping_address_1 = $da_val[4];
                $getorders_model->shipping_suburb = $da_val[6];
                $getorders_model->shipping_state = $da_val[7];
                $getorders_model->shipping_postcode = $da_val[8];
                $getorders_model->shipping_country = $da_val[9];
                $getorders_model->shipping_phone    =   $da_val[10];
                $getorders_model->supplier_sku = $da_val[11];
                $getorders_model->quantity = $da_val[13];
                $getorders_model->operator_id = $this->params['user_id'];
                $getorders_model->pick_up = $da_val[18];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];
                $quantity_array[$da_val[11]]    +=   $da_val[13];
                $getorders_model->quantity_array    =   $quantity_array;
                if($user_info['user_id'])//free shiping for dealsdirect
                {
                    if($user_info['user_id']==8);
                    $params_array   =   array(
                        'free_shipping' => 1
                    );
                    $getorders_model->params_array  =   $params_array;
                }
                /**
                 * @var $getorders_model Databases_Joins_GetOrders
                 * @todo PlaceOrderCheck
                 */
                $check_result = $getorders_model->PlaceOrderCheck();

                $data_array[$da_key]['result'] = $check_result[1];
                $data_array[$da_key]['reason'] = $check_result[2];
                $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                $data_array[$da_key]['credit'] = $check_result['credit'];
                $data_array[$da_key]['user_id'] = $check_result['user_id'];
                $product_info   =   $product_filter_model->getProductInfo($da_val[11]);
                $data_array[$da_key]['product_name']    =   $product_info['product_name']; 
                $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                $data_array[$da_key][11]                =   $da_val[11];
                //update instant balance
                if ($check_result[2]=="Out of balance"):
                    $this->view->ifpay=1;
                endif;
                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
            }
            $this->view->flat_paypal    =   1;
            $this->view->notice_paypal  =   "PayPal Rechange successfully.";
            $this->view->list = $data_array;
        }
        else
        {
            if ($_FILES["csvf"]["error"] > 0)
            {
                
                $this->view->notice = $_FILES["csvf"]["error"];
            }
            else{
                /**
                 * no paypal load order info from csv file
                 */
                if( in_array($_FILES["csvf"]["type"], $valid_file_types) )
                {
                    //Action
                    $data_array = array();
                    if (($handle = fopen($_FILES["csvf"]["tmp_name"], "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                            $data_array[] = $data;
                        }
                        fclose($handle);
                        if(!empty($data_array))
                        {
                            //ignore title
                            unset($data_array[0]);

                            foreach($data_array as $da_key => $da_val)
                            {
                                $count_column = count($da_val);

                                if(19 != $count_column) //Reject due to the column amount
                                {
                                    $data_array[$da_key]['result'] = "N";
                                    $data_array[$da_key]['reason'] = "Column Amount Error.";
                                }else{ //check contents
                                    if(substr($da_val[11], -3) == '-TP'){
                                        $da_val[11]    =   substr($da_val[11], 0, -3);
                                    }
                                    $getorders_model->shipping_first_name = $da_val[1];
                                    $getorders_model->shipping_last_name = $da_val[2];
                                    $getorders_model->shipping_company = $da_val[3];
                                    $getorders_model->merchant_company = $da_val[17]; // REQUIRED$da_val[17] AND IMPORTANT !!!
                                    $getorders_model->shipping_address_1 = $da_val[4];
                                    $getorders_model->shipping_suburb = $da_val[6];
                                    $getorders_model->shipping_state = $da_val[7];
                                    $getorders_model->shipping_postcode = $da_val[8];
                                    $getorders_model->shipping_country = $da_val[9];
                                    $getorders_model->shipping_phone    =   $da_val[10];
                                    $getorders_model->supplier_sku = $da_val[11];
                                    $getorders_model->quantity = $da_val[13];
                                    $getorders_model->operator_id = $this->params['user_id'];
                                    $getorders_model->pick_up = $da_val[18];
                                    $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                                    $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];
                                    $quantity_array[$da_val[11]]    +=   $da_val[13];
                                    $getorders_model->quantity_array    =   $quantity_array;
                                   /**
                                    * @var $getorders_model Databases_Joins_GetOrders
                                    * @todo PlaceOrderCheck
                                    */
                                    $check_result = $getorders_model->PlaceOrderCheck();
                                   
                                    $data_array[$da_key]['result'] = $check_result[1];
                                    $data_array[$da_key]['reason'] = $check_result[2];
                                    $data_array[$da_key]['order_amount'] = (float)$check_result['order_amount'];
                                    $data_array[$da_key]['instant_balance'] = (float)$check_result['instant_balance'];
                                    $data_array[$da_key]['credit'] = $check_result['credit'];
                                    $data_array[$da_key]['user_id'] = $check_result['user_id'];
                                    $product_info   =   $product_filter_model->getProductInfo($da_val[11]);
                                    $data_array[$da_key]['product_name']    =   $product_info['product_name']; 
                                    $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                                    $data_array[$da_key][11]                =   $da_val[11];
                                    //update instant balance
                                    //print_r($check_result);
                                    
                                     if ($check_result[3]==1 ):
                                        $this->view->iferror=1;
                                      endif;
                                    
                                    if ($check_result[2]=="Out of balance"  ):
                                        $this->view->ifpay=1;
                                    endif;
                                    $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
                                }

                            }
                        }
                        $sessionId =   base64_encode($this->params['user_id'].date('YmdHis'));
                        $_SESSION['import_order'][$sessionId]   =   $data_array;
                        $this->view->sessionId  =   $sessionId;
                        $this->view->userId     =   $this->params['user_id'];
                        $this->view->list = $data_array;

                    }else{
                        $this->view->notice = "Error.";
                    }
                }else{
                    $this->view->notice = "File type is invalid.";
                    echo "<<<<< File Type: ".$_FILES["csvf"]["type"]." >>>>>";
                }
            }
        }
    }
    
    /**
     * paypal Success page
     */
    function paypalSuccessAction ()
    {
        $params =   $this->_request->getParams();
        $sessionId  =   $params['sessionid'];
        $this->view->sessionId  =   $sessionId;
        //$this->_redirect("/merchant/import-order-preview/sessionid/".$sessionId);
    }
    
    /**
     * Place Order paypal Success page
     */
    function paypalPlaceOrderAction ()
    {
        $params =   $this->_request->getParams();
        $sessionId  =   $params['sessionid'];
        $this->view->sessionId  =   $sessionId;
        //$this->_redirect("/merchant/place-order-preview/sessionid/".$sessionId);
    }
    /**
     * Import Order Confirm
     * start loop
     * validation
     * insert into orders
     * finish loop
     * get orders
     * start loop
     * get order items
     * WebServicePlaceOrder
     * finish loop
     * WebServiceSetOrderStatus
     * update financial table
     */
    function importOrderConfirmAction()
    {
        /**
         * start loop
         * validation
         * insert into orders
         * update financial table
         * finish loop
         */

        $this->view->title = "Order Import Confirmation";
        $params = $this->_request->getParams();
        //Algorithms_Extensions_Plugin::FormatArray($params);die;
        $getorders_model = new Databases_Joins_GetOrders();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $users_extension_model  =   new Databases_Tables_UsersExtension();
        $ip = $plugin_model->GetIp();
        $notice = "S1"; //success
        
        if(count($params['supplier_sku']))
        {
            $group_instance_balance_array = array();
            $merchant_ref_pool = array();
            
            foreach($params['supplier_sku'] as $loop_key => $supplier_sku)
            {
                //Validation
                $getorders_model->shipping_first_name = $params['shipping_first_name'][$loop_key];
                $getorders_model->shipping_last_name = $params['shipping_last_name'][$loop_key];
                $getorders_model->shipping_company = $params['shipping_company'][$loop_key];
                $getorders_model->merchant_company = $params['merchant_company'][$loop_key];
                $getorders_model->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                $getorders_model->shipping_suburb = $params['shipping_suburb'][$loop_key];
                $getorders_model->shipping_state = $params['shipping_state'][$loop_key];
                $getorders_model->shipping_postcode = $params['shipping_postcode'][$loop_key];
                $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                $getorders_model->shipping_phone = $params['shipping_phone'][$loop_key];
                $getorders_model->supplier_sku = $supplier_sku;
                $getorders_model->quantity = $params['quantity'][$loop_key];
                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $getorders_model->pick_up = $params['pick_up'][$loop_key];
                $users_extension_model->company = $params['merchant_company'][$loop_key];
                $user_info = $users_extension_model->CheckCompanyInCsv();
                $getorders_model->flat_rate_shipping    =   $user_info['flat_rate_shipping'];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                if($user_info['user_id'])//free shiping for dealsdirect
                {
                    if($user_info['user_id']==8);
                    $params_array   =   array(
                        'free_shipping' => 1
                    );
                    $getorders_model->params_array  =   $params_array;
                }
                /**
                 * @var $getorders_model Databases_Joins_GetOrders
                 * @todo PlaceOrderCheck
                 */
                $check_result = $getorders_model->PlaceOrderCheck();
                
                if("Y" == $check_result[1]) //passed the validation
                {
                    $order_amount = $check_result['order_amount'];
                    $instant_balance = $check_result['instant_balance'];
                    $user_id = $check_result['user_id'];
                    
                    //update instant balance
                    $group_instance_balance_array[$user_id] = $instant_balance;
                    //Insert Into Orders
                    $getorders_model->merchant_ref = $params['merchant_ref'][$loop_key];
                    $getorders_model->order_amount = $order_amount;
                    $getorders_model->user_id = $user_id;
                    $getorders_model->ip = $ip;
                    $getorders_model->shipping_first_name = $params['shipping_first_name'][$loop_key];
                    $getorders_model->shipping_last_name = $params['shipping_last_name'][$loop_key];
                    $getorders_model->shipping_company = $params['shipping_company'][$loop_key];
                    $getorders_model->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                    $getorders_model->shipping_address_2 = $params['shipping_address_2'][$loop_key];
                    $getorders_model->shipping_suburb = $params['shipping_suburb'][$loop_key];
                    $getorders_model->shipping_state = $params['shipping_state'][$loop_key];
                    $getorders_model->shipping_postcode = $params['shipping_postcode'][$loop_key];
                    $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                    $getorders_model->shipping_phone = $params['shipping_phone'][$loop_key];
                    $getorders_model->shipping_fax = $params['shipping_fax'][$loop_key];
                    $getorders_model->supplier_sku = $params['supplier_sku'][$loop_key];
                    $getorders_model->merchant_sku = $params['merchant_sku'][$loop_key];
                    $getorders_model->quantity = $params['quantity'][$loop_key];
                    $getorders_model->shipping_method = $params['shipping_method'][$loop_key];
                    $getorders_model->shipping_instruction = $params['shipping_instruction'][$loop_key];
                    $getorders_model->serial_no = $params['serial_no'][$loop_key];
                    $getorders_model->comments = $params['comments'][$loop_key];
                    $getorders_model->pick_up = $params['pick_up'][$loop_key];
                    $getorders_model->merchant_ref_pool = $merchant_ref_pool;
                    $getorders_model->discount_amount   =   round($check_result['discount_amount'],2);
                    $getorders_model->shipping_cost   =   round($check_result['shipping_cost'],2);
                    $getorders_model->item_amount   =   $order_amount;
                    $sku_prices_info    =   $product_filter_model->GetSkuPrices($params['supplier_sku'][$loop_key], $user_id);
                    
                    $getorders_model->expected_item_cost    =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_item_cost       =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_ship_cost       =   round($check_result['shipping_cost'],2);
                    $getorders_model->ship_cost             =   round($check_result['shipping_cost'],2);
                    //$getorders_model->item_amount           =   round($sku_prices_info['supplier_price'],2) + round($check_result['shipping_cost'],2);
                    $getorders_model->shipping_courier      =   trim($sku_prices_info['shipping_courier']);
                    $getorders_model->sc_class              =   trim($sku_prices_info['sc_class']);
                    if($params['flat_paypal'])
                    {
                        $getorders_model->payment_type_id   =   5;
                    }else
                    {
                        $getorders_model->payment_type_id   =   9;
                    }
                    /**
                     * @todo PlaceOrder
                     */
                    $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                    //update merchant ref pool
                    $merchant_ref_pool = $place_order_return['merchant_ref_pool'];
                }else{
                    $notice = "E2";
                }
            }
            
            $purchase_order_ids =   implode(',',$merchant_ref_pool);
            /**
             * @var $operate_orders_model Databases_Joins_OperateOrders
             * @todo PlaceOrder
             */
            $operate_orders_model   =   new Databases_Joins_OperateOrders();
            $operate_orders_model->purchase_order_ids   =   $purchase_order_ids;
            $result = $operate_orders_model->PlaceOrder();         
        }else{
            $notice = "E1";
        }
         $this->_redirect("/merchant/order-report/notice/".$notice);
    }
    
    /**
     * Order View
     * get params
     * GetPurchaseOrder
     */
    function orderViewAction()
    {
        $this->view->title      =   "Order View";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard", "Order Report","Order View"));
        $params =   $this->_request->getParams();
        $purchase_order_id  =   $params['order_id'];
        $product_info_model =   new Databases_Joins_ProductFilter();
        $purchase_order_model   =   new Databases_Tables_PurchaseOrder();
        $log_order_model        =   new Databases_Tables_LogsOrders();
        $purchase_order_model->purchase_order_ids    =   $purchase_order_id;
        $log_order_model->purchase_order_id         =   $purchase_order_id;
        $purchase_order_info    =   $purchase_order_model->GetPurchaseOrder();
        $user_id    =   $this->params['user_id'];
        if($purchase_order_info[0]['user_id'] != $user_id)
        {
             $this->view->notice = "<font color='red'>Order Error.</font>";
        }else{
            $logs_order_list        =   $log_order_model->GetLogsOrderList();
            foreach ($logs_order_list as $key => $logs_order)
            {
                $product_info   =   $product_info_model->getProductInfo($logs_order['supplier_sku']);
                $logs_order_list[$key]['product_name']  =   $product_info['product_name'];
                $logs_order_list[$key]['imageURL0']     =   $product_info['imageURL0'];
            }
            $this->view->list   =   $logs_order_list;
            $this->view->purchase_order =   $purchase_order_info[0];
        }
    }

    function profileEditAction()
    {
		if(!isset($_SESSION["Zend_Auth"]["storage"]->user_id) || !is_numeric($_SESSION["Zend_Auth"]["storage"]->user_id))
		{
			// user id doesn't exists, someone is doing something nasty
			// LOGOUT
			return $this->_redirect("/login/logout");
		}

        $get_user_info = new Databases_Joins_GetUserInfo();
        $this->view->user = $get_user_info->GetUserInfo($_SESSION["Zend_Auth"]["storage"]->user_id);
		
		if(!isset($this->view->user['user_id']) || !is_numeric($this->view->user['user_id']))
		{
			// user id doesn't exists. account was deleted, logging out
			return $this->_redirect("/login/logout");
		}	

        $this->view->title = "Edit Profile";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Edit Profile"));
		
        $form = new Forms_ProfileMerchant();
        $form->submitx->setLabel('Update');
        $this->view->form = $form;
		
        if($this->_request->isPost())
		{
			$error = false;
			$password_change = false;
            $formData = $this->_request->getPost();
            if($form->isValid($formData))
			{
                $form->getValues();

                ///////////////////////////////////////////////////////////
                //check valid start

                //password check
                if($form->getValue('password') || $form->getValue('password_r'))
                {
                    if($form->getValue('password') != $form->getValue('password_r'))
                    {
                        $this->view->notice="The passwords you typed twice were different.";
                        $error = true;
                    }
                }
				else
                {
                    $password_changed = true;
                }
                
                if(!$form->getValue('address'))
                {
                    $this->view->notice="The address is required.";
                    $error = true;
                }
                if(!$form->getValue('post_code'))
                {
                    $this->view->notice="The post code is required.";
                    $error = true;
                }
                if(!$form->getValue('suburb'))
                {
                    $this->view->notice="The suburb is required.";
                    $error = true;
                }
                if(!$form->getValue('state'))
                {
                    $this->view->notice="The state is required.";
                    $error = true;
                }
                if(!$form->getValue('contact_name'))
                {
                    $this->view->notice="The contact name is required.";
                    $error = true;
                }
                if(!$form->getValue('contact_phone'))
                {
                    $this->view->notice="The contact phone is required.";
                    $error = true;
                }

				if($error === false) // check if previously no error
				{
					$shipping_state_info_model  =   new Databases_Tables_ShippingStateInfo();
				    $shipping_state_info_model->post_code       =   $form->getValue('post_code');
				    $shipping_state_info_model->shipping_suburb =   $form->getValue('suburb');
				    $shipping_state_info_model->shipping_state  =   $form->getValue('state');
				    if($shipping_state_info_model->GetShippingStateInfo() == 0)
				    {
				        $this->view->notice = "Shipping state is not found";
				        $error = true;
				    }
				}

                //check valid end
                ///////////////////////////////////////////////////////////

                if($error === false) // no error
		        {
		            //new users
		            $check_user_string = new Databases_Tables_Users();
					$check_user_string->SetUser($this->view->user);

                    //update to db
                    if($password_change === true)
                    {
                        $check_user_string->password = $form->getValue('password');
                    }
                    $check_user_string->user_id     =   $_SESSION["Zend_Auth"]["storage"]->user_id;
                    $check_user_string->contact_name = $form->getValue('contact_name');
                    $check_user_string->contact_phone = $form->getValue('contact_phone');
                    
                    $check_user_string->address     =   $form->getValue('address');
                    $check_user_string->post_code   =   $form->getValue('post_code');
                    $check_user_string->suburb      =   $form->getValue('suburb');
                    $check_user_string->state       =   $form->getValue('state');
  
                    $check_user_string->EditUser();
					$this->view->notice_success = "Changes saved.";
                }
				else
				{
					$form->populate($formData);
				}
            }
			else
			{
                ///////////////////////////////////////////////////////////
                //check valid start

                if(!$formData['email'])
                {
                    $this->view->notice="Email is required.";
                    $error = true;
                }
                
                if(!Algorithms_Extensions_Plugin::EmailCheck($formData['email']))
                {
                    $this->view->notice="Email format is incorrect.";
                    $error = true;
                }
                if(!trim($formData['company']))
                {
                    $this->view->notice="Company is required.";
                    $error = true;
                }
                if(!trim($formData['address']))
                {
                    $this->view->notice="Address is required.";
                    $error = true;
                }
                if(!trim($formData['post_code']))
                {
                    $this->view->notice="Post code is required.";
                    $error = true;
                }
                if(!trim($formData['suburb']))
                {
                    $this->view->notice="Suburb is required.";
                    $error = true;
                }
                if(!trim($formData['state']))
                {
                    $this->view->notice="State is required.";
                    $error = true;
                }
                if(!trim($formData['contact_name']))
                {
                    $this->view->notice="Contact name is required.";
                    $error = true;
                }
                if(!trim($formData['contact_phone']))
                {
                    $this->view->notice="Contact phone is required.";
                    $error = true;
                }

                //check valid end
                ///////////////////////////////////////////////////////////

                if($error === true)
                {
                    $this->view->notice="Some information are inValid";
                    $form->populate($formData);
                }
            }
        }
		else
        {
            $form->populate($this->view->user);
        }
    }
    
}

