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

        if(!$auth->hasIdentity())
        { 
            $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
        }elseif(2 != $user_info['user_type']){
            $this->_redirect('/admin');
        }

        //get system title
        $get_title = new Databases_Tables_Params();
        $this->view->system_title = $get_title -> GetVal("system_title");
        $this->view->system_version = $get_title -> GetVal("system_version");

        //make top menu
        $menu = new Algorithms_Core_Menu();
        $top_menu = $menu->MenuForMerchant();
        $this->view->top_menu = $menu -> FormatMenu($top_menu, $this->getRequest()->getActionName());
    }
    
    function indexAction()
    {
        $this->view->title = "Dashboard";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard"));
        
        $get_user_info = new Databases_Joins_GetUserInfo();
        $this->view->user = $get_user_info->GetUserInfo($this->params['user_id']);
    }
    
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
        
        $this->view->list = $getorders_model->PushList();
        $this->view->pagination = $getorders_model->Pagination();
        
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
    
    function placeOrderAddItemAction()
    {
        $this->view->title = "Place Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Place Order"));
        
        if(!$_SESSION['place_order']['items'])
        {
            $_SESSION['place_order']['items'] = array();
        }
        
        if($this->params['supplier_sku'])
        {
            $_SESSION['place_order']['items'][] = array(
                "supplier_sku" => $this->params['supplier_sku'],
                "merchant_sku" => $this->params['merchant_sku'],
                "quantity" => $this->params['quantity'],
                "merchant_ref" => $this->params['merchant_ref'],
                "shipping_method" => $this->params['shipping_method'],
                "shipping_instruction" => $this->params['shipping_instruction'],
                "tracking_number" => $this->params['tracking_number'],
                "serial_no" => $this->params['serial_no'],
                "comments" => $this->params['comments']
            );
        }
        
        $this->_redirect("/merchant/place-order");
    }
    
    function placeOrderRemoveItemAction()
    {
        if(NULL !== $this->params['ikey'])
        {
            unset($_SESSION['place_order']['items'][$this->params['ikey']]);
        }
        
        $this->_redirect("/merchant/place-order");
    }
    
    function placeOrderPreviewAction()
    {
        $this->view->title = "Place Order Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Place Order Preview"));
        
        //make $data_array
        $data_array = array();
        $getorders_model = new Databases_Joins_GetOrders();
        $user_extension_model = new Databases_Tables_UsersExtension();
        $user_info = $user_extension_model->UserInfo();
        $group_instance_balance_array = array();
        
        $system_params_model    =   new Databases_Tables_Params();        
        $this->view->paypal_url         =   $system_params_model->GetVal('paypal_url');
        $this->view->paypal_account     =   $system_params_model->GetVal('paypal_account');
        $this->view->paypal_return_url  =   $system_params_model->GetVal('paypal_return_url');
        
        //save delivery
        if(!$_SESSION['place_order']['delivery'])
        {
            $_SESSION['place_order']['delivery'] = array();
        }
        
        $_SESSION['place_order']['delivery'] = array(
            "shipping_first_name" => $this->params['shipping_first_name'],
            "shipping_last_name" => $this->params['shipping_last_name'],
            "shipping_company" => $this->params['shipping_company'],
            "shipping_address_1" => $this->params['shipping_address_1'],
            "shipping_address_2" => $this->params['shipping_address_2'],
            "shipping_company" => $this->params['shipping_company'],
            "shipping_suburb" => $this->params['shipping_suburb'],
            "shipping_state" => $this->params['shipping_state'],
            "shipping_postcode" => $this->params['shipping_postcode'],
            "shipping_country" => $this->params['shipping_country'],
            "shipping_phone" => $this->params['shipping_phone'],
            "shipping_fax" => $this->params['shipping_fax'],
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
                    "shipping_company" => $this->params['shipping_company'],
                    "shipping_suburb" => $this->params['shipping_suburb'],
                    "shipping_state" => $this->params['shipping_state'],
                    "shipping_postcode" => $this->params['shipping_postcode'],
                    "shipping_country" => $this->params['shipping_country'],
                    "shipping_phone" => $this->params['shipping_phone'],
                    "shipping_fax" => $this->params['shipping_fax'],
                    "supplier_sku" => $items['supplier_sku'],
                    "merchant_sku" => $items['merchant_sku'],
                    "quantity" => $items['quantity'],
                    "merchant_ref" => $items['merchant_ref'],
                    "shipping_method" => $items['shipping_method'],
                    "shipping_instruction" => $items['shipping_instruction'],
                    "tracking_number" => $items['tracking_number'],
                    "serial_no" => $items['serial_no'],
                    "comments" => $items['comments'],
                    "pick_up" => $this->params['pickup']
                );
            }
        }
        if ($_SESSION['place_order'][$this->params['sessionid']]&& $this->params['sessionid'])
        {
            $data_array =   $_SESSION['place_order'][$this->params['sessionid']];
             foreach($data_array as $da_key => $da_val)
            {
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

                $check_result = $getorders_model->PlaceOrderCheck();

                $data_array[$da_key]['result'] = $check_result[1];
                $data_array[$da_key]['reason'] = $check_result[2];
                $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                $data_array[$da_key]['credit'] = $check_result['credit'];
                $data_array[$da_key]['user_id'] = $check_result['user_id'];


                //update instant balance
                if ($check_result[2]=="Out of balance"):
                    $this->view->ifpay=1;
                endif;
                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
            }
            $this->view->notice_paypal =   "PayPal Rechange successfully.";
            $this->view->list = $data_array;
        }elseif(!empty($data_array))
        {
            foreach($data_array as $da_key => $da_val)
            {
                $count_column = count($da_val);
                if(22 != $count_column) //Reject due to the column amount
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

                    $check_result = $getorders_model->PlaceOrderCheck();
                    
                    $data_array[$da_key]['pick_up']    =   $this->params['pickup']?"Y":"N";
                    $data_array[$da_key]['result'] = $check_result[1];
                    $data_array[$da_key]['reason'] = $check_result[2];
                    $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                    $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                    $data_array[$da_key]['credit'] = $check_result['credit'];
                    $data_array[$da_key]['user_id'] = $check_result['user_id'];

                    //update instant balance
                    //update instant balance
                    if ($check_result[2]=="Out of balance"):
                        $this->view->ifpay=1;
                    endif;
                    $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
                }
            }
            $sessionId  =   base64_encode($this->params['user_id'].date('YmdHis'));
            $_SESSION['place_order'][$sessionId]   =   $data_array;
            $this->view->sessionId  =   $sessionId;
            $this->view->userId     =   $this->params['user_id'];
        }
        
        $this->view->list = $data_array;
    }
    
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
        $logs_financial = new Databases_Tables_LogsFinancial();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $order_service_model    =   new Algorithms_Core_OrderService();
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
                $getorders_model->supplier_sku = $supplier_sku;
                $getorders_model->quantity = $params['quantity'][$loop_key];
                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $getorders_model->pick_up = $params['pick_up'][$loop_key];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;

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
                    
                    $getorders_model->item_amount   =   $order_amount;          
                    $order_service_model->crazySalesOrderType['RetailerAccountEmail']   =   $_SESSION["Zend_Auth"]["storage"]->email;
                    $order_service_model->crazySalesOrderType['PaymentTypeID']          =   1; 
                    $order_service_model->crazySalesOrderType['ShipFirstName']          =   $params['shipping_first_name'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipAddress_1']          =   $params['shipping_address_1'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipAddress_2']          =   $params['shipping_address_2'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipCity']               =   $params['shipping_suburb'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipState']              =   $params['shipping_state'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipZipCode']            =   $params['shipping_postcode'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipCountryCode']        =   $params['shipping_country'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipPhone']              =   $params['shipping_phone'][$loop_key];
                    $order_service_model->crazySalesOrderType['orderAmount']            =   $order_amount;
                    $order_service_model->crazySalesOrderItemType['Quantity']           =   $params['quantity'][$loop_key];
                    $order_service_model->crazySalesOrderItemType['ItemSku']            =   $params['supplier_sku'][$loop_key];
                    
                    $response_data   =   $order_service_model->WebServicePlaceOrder();
                    
                    if($response_data['order_number']) 
                    {
                        $getorders_model->main_order_id =   $response_data['order_number'];
                        $getorders_model->item_status   =   1;
                    }elseif($response_data['MessageType']['Description'])
                    {
                        $getorders_model->item_status   =   2;
                        $getorders_model->api_response  =   $response_data['MessageType']['Description'];
                    }
                    //end add
                    
                    $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                    
                    //update merchant ref pool
                    $merchant_ref_pool = $place_order_return['merchant_ref_pool'];

                    //Update Financial Info
                    $logs_financial->user_id = $user_id;
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $order_amount;
                   // $logs_financial->trans_id = $place_order_return['logs_orders_id'];

                    $logs_financial->AddLog();
                }else{
                    $notice = "E2";
                }
            }
        }else{
            $notice = "E1";
        }
        
        //unset session
        unset($_SESSION['place_order']);
        $this->_redirect("/merchant/order-report/notice/".$notice);
    }
    
    function importOrderAction()
    {
        $this->view->title = "Import Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Import Order"));
    }
    
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
        
        $this->view->title = "Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Import Order"));
        
        $group_instance_balance_array = array();
        $getorders_model = new Databases_Joins_GetOrders();
        
        $system_params_model    =   new Databases_Tables_Params();        
        $this->view->paypal_url         =   $system_params_model->GetVal('paypal_url');
        $this->view->paypal_account     =   $system_params_model->GetVal('paypal_account');
        $this->view->paypal_return_url  =   $system_params_model->GetVal('paypal_return_url');
        
        if ($_SESSION['import_order'][$this->params['sessionid']]&& $this->params['sessionid'])
        {
            $data_array =   $_SESSION['import_order'][$this->params['sessionid']];
             foreach($data_array as $da_key => $da_val)
            {
                $count_column = count($da_val);

                $getorders_model->shipping_first_name = $da_val[1];
                $getorders_model->shipping_last_name = $da_val[2];
                $getorders_model->shipping_company = $da_val[3];
                $getorders_model->merchant_company = $da_val[20]; // REQUIRED AND IMPORTANT !!!
                $getorders_model->shipping_address_1 = $da_val[4];
                $getorders_model->shipping_suburb = $da_val[6];
                $getorders_model->shipping_state = $da_val[7];
                $getorders_model->shipping_postcode = $da_val[8];
                $getorders_model->shipping_country = $da_val[9];
                $getorders_model->supplier_sku = $da_val[12];
                $getorders_model->quantity = $da_val[14];
                $getorders_model->operator_id = $this->params['user_id'];
                $getorders_model->pick_up = $da_val[21];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;

                $check_result = $getorders_model->PlaceOrderCheck();

                $data_array[$da_key]['result'] = $check_result[1];
                $data_array[$da_key]['reason'] = $check_result[2];
                $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                $data_array[$da_key]['credit'] = $check_result['credit'];
                $data_array[$da_key]['user_id'] = $check_result['user_id'];


                //update instant balance
                if ($check_result[2]=="Out of balance"):
                    $this->view->ifpay=1;
                endif;
                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
            }
            $this->view->notice_paypal =   "PayPal Rechange successfully.";
            $this->view->list = $data_array;
        }
        else
        {
            
            if ($_FILES["csvf"]["error"] > 0)
            {
                
                $this->view->notice = $_FILES["csvf"]["error"];
            }
            else{
                if('text/csv' != $_FILES["csvf"]["type"])
                {
                    $this->view->notice = "File type is invalid.";
                }else{
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

                                if(22 != $count_column) //Reject due to the column amount
                                {
                                    $data_array[$da_key]['result'] = "N";
                                    $data_array[$da_key]['reason'] = "Column Amount Error.";
                                }else{ //check contents
                                    $getorders_model->shipping_first_name = $da_val[1];
                                    $getorders_model->shipping_last_name = $da_val[2];
                                    $getorders_model->shipping_company = $da_val[3];
                                    $getorders_model->merchant_company = $da_val[20]; // REQUIRED AND IMPORTANT !!!
                                    $getorders_model->shipping_address_1 = $da_val[4];
                                    $getorders_model->shipping_suburb = $da_val[6];
                                    $getorders_model->shipping_state = $da_val[7];
                                    $getorders_model->shipping_postcode = $da_val[8];
                                    $getorders_model->shipping_country = $da_val[9];
                                    $getorders_model->supplier_sku = $da_val[12];
                                    $getorders_model->quantity = $da_val[14];
                                    $getorders_model->operator_id = $this->params['user_id'];
                                    $getorders_model->pick_up = $da_val[21];
                                    $getorders_model->group_instance_balance_array = $group_instance_balance_array;

                                    $check_result = $getorders_model->PlaceOrderCheck();

                                    $data_array[$da_key]['result'] = $check_result[1];
                                    $data_array[$da_key]['reason'] = $check_result[2];
                                    $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                                    $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                                    $data_array[$da_key]['credit'] = $check_result['credit'];
                                    $data_array[$da_key]['user_id'] = $check_result['user_id'];


                                    //update instant balance
                                    if ($check_result[2]=="Out of balance"):
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
                }
            }
        }
    }
    
    function paypalSuccessAction ()
    {
        $params =   $this->_request->getParams();
        $sessionId  =   $params['sessionid'];
        $this->view->sessionId  =   $sessionId;
        //$this->_redirect("/merchant/import-order-preview/sessionid/".$sessionId);
    }
    
    function paypalPlaceOrderAction ()
    {
        $params =   $this->_request->getParams();
        $sessionId  =   $params['sessionid'];
        $this->view->sessionId  =   $sessionId;
        //$this->_redirect("/merchant/place-order-preview/sessionid/".$sessionId);
    }
    
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
        $logs_financial = new Databases_Tables_LogsFinancial();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $order_service_model    =   new Algorithms_Core_OrderService();
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
                $getorders_model->supplier_sku = $supplier_sku;
                $getorders_model->quantity = $params['quantity'][$loop_key];
                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $getorders_model->pick_up = $params['pick_up'][$loop_key];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;

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
                    
                    $getorders_model->item_amount   =   $order_amount;          
                    $order_service_model->crazySalesOrderType['RetailerAccountEmail']   =   $_SESSION["Zend_Auth"]["storage"]->email;
                    $order_service_model->crazySalesOrderType['PaymentTypeID']          =   1; 
                    $order_service_model->crazySalesOrderType['ShipFirstName']          =   $params['shipping_first_name'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipAddress_1']          =   $params['shipping_address_1'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipAddress_2']          =   $params['shipping_address_2'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipCity']               =   $params['shipping_suburb'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipState']              =   $params['shipping_state'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipZipCode']            =   $params['shipping_postcode'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipCountryCode']        =   $params['shipping_country'][$loop_key];
                    $order_service_model->crazySalesOrderType['ShipPhone']              =   $params['shipping_phone'][$loop_key];
                    $order_service_model->crazySalesOrderType['orderAmount']            =   $order_amount;
                    $order_service_model->crazySalesOrderItemType['Quantity']           =   $params['quantity'][$loop_key];
                    $order_service_model->crazySalesOrderItemType['ItemSku']            =   $params['supplier_sku'][$loop_key];
                    
                    $response_data   =   $order_service_model->WebServicePlaceOrder();
                    
                    if($response_data['order_number']) 
                    {
                        $getorders_model->main_order_id =   $response_data['order_number'];
                        $getorders_model->item_status   =   1;
                    }elseif($response_data['MessageType']['Description'])
                    {
                        $getorders_model->item_status   =   2;
                        $getorders_model->api_response  =   $response_data['MessageType']['Description'];
                    }
                    //end add
                    
                    $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                    //update merchant ref pool
                    $merchant_ref_pool = $place_order_return['merchant_ref_pool'];

                    //Update Financial Info
                    $logs_financial->user_id = $user_id;
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $order_amount;
                    //$logs_financial->trans_id = $place_order_return['logs_orders_id'];

                    $logs_financial->AddLog();
                }else{
                    $notice = "E2";
                }
            }
        }else{
            $notice = "E1";
        }
        $this->_redirect("/merchant/order-report/notice/".$notice);
    }
    
}

