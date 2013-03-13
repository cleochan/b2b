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
        
        $logs_orders_model = new Databases_Tables_LogsOrders();
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
        if($this->params['order_id'])
        {
            $logs_orders_model->order_id = $this->params['order_id'];
            $this->view->order_id = $this->params['order_id'];
        }
        if($this->params['p_current_page'])
        {
            $logs_orders_model->p_current_page = $this->params['p_current_page'];
        }else{
            $logs_orders_model->p_current_page = 1; //from the first page
        }
        $this->view->list = $logs_orders_model->PushList();
        $this->view->pagination = $logs_orders_model->Pagination();
        
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
         */
        
        $this->view->title = "Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Import Order"));
        
        if ($_FILES["csvf"]["error"] > 0)
        {
            $this->view->notice = $_FILES["csvf"]["error"];
        }else{
            if('text/csv' != $_FILES["csvf"]["type"])
            {
                $this->view->notice = "File type is invalid.";
            }else{
                //Action
                $group_instance_balance_array = array();
                $logs_orders_model = new Databases_Tables_LogsOrders();
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
                            
                            if(21 != $count_column) //Reject due to the column amount
                            {
                                $data_array[$da_key]['result'] = "N";
                                $data_array[$da_key]['reason'] = "Column Amount Error.";
                            }else{ //check contents
                                $logs_orders_model->shipping_first_name = $da_val[1];
                                $logs_orders_model->shipping_last_name = $da_val[2];
                                $logs_orders_model->shipping_company = $da_val[3];
                                $logs_orders_model->merchant_company = $da_val[20]; // REQUIRED AND IMPORTANT !!!
                                $logs_orders_model->shipping_address_1 = $da_val[4];
                                $logs_orders_model->shipping_suburb = $da_val[6];
                                $logs_orders_model->shipping_state = $da_val[7];
                                $logs_orders_model->shipping_postcode = $da_val[8];
                                $logs_orders_model->shipping_country = $da_val[9];
                                $logs_orders_model->supplier_sku = $da_val[12];
                                $logs_orders_model->quantity = $da_val[14];
                                $logs_orders_model->operator_id = $this->params['user_id'];
                                $logs_orders_model->group_instance_balance_array = $group_instance_balance_array;
                                
                                $check_result = $logs_orders_model->PlaceOrderCheck();
                                
                                $data_array[$da_key]['result'] = $check_result[1];
                                $data_array[$da_key]['reason'] = $check_result[2];
                                $data_array[$da_key]['order_amount'] = $check_result['order_amount'];
                                $data_array[$da_key]['instant_balance'] = $check_result['instant_balance'];
                                $data_array[$da_key]['credit'] = $check_result['credit'];
                                $data_array[$da_key]['user_id'] = $check_result['user_id'];
                                
                                //update instant balance
                                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
                            }
                        }
                    }
                    
                    $this->view->list = $data_array;
                    
                }else{
                    $this->view->notice = "Error.";
                }
            }
        }
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
        $logs_orders = new Databases_Tables_LogsOrders();
        $logs_financial = new Databases_Tables_LogsFinancial();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $ip = $plugin_model->GetIp();
        $notice = "S1"; //success
        
        if(count($params['supplier_sku']))
        {
            $group_instance_balance_array = array();
            
            foreach($params['supplier_sku'] as $loop_key => $supplier_sku)
            {
                //Validation
                $logs_orders->shipping_first_name = $params['shipping_first_name'][$loop_key];
                $logs_orders->shipping_last_name = $params['shipping_last_name'][$loop_key];
                $logs_orders->shipping_company = $params['shipping_company'][$loop_key];
                $logs_orders->merchant_company = $params['merchant_company'][$loop_key];
                $logs_orders->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                $logs_orders->shipping_suburb = $params['shipping_suburb'][$loop_key];
                $logs_orders->shipping_state = $params['shipping_state'][$loop_key];
                $logs_orders->shipping_postcode = $params['shipping_postcode'][$loop_key];
                $logs_orders->shipping_country = $params['shipping_country'][$loop_key];
                $logs_orders->supplier_sku = $supplier_sku;
                $logs_orders->quantity = $params['quantity'][$loop_key];
                $logs_orders->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $logs_orders->group_instance_balance_array = $group_instance_balance_array;

                $check_result = $logs_orders->PlaceOrderCheck();

                if("Y" == $check_result[1]) //passed the validation
                {
                    $order_amount = $check_result['order_amount'];
                    $instant_balance = $check_result['instant_balance'];
                    $user_id = $check_result['user_id'];

                    //update instant balance
                    $group_instance_balance_array[$user_id] = $instant_balance;
                    
                    //Insert Into Orders
                    $logs_orders->merchant_ref = $params['merchant_ref'][$loop_key];
                    $logs_orders->order_amount = $order_amount;
                    $logs_orders->user_id = $user_id;
                    $logs_orders->ip = $ip;
                    $logs_orders->shipping_first_name = $params['shipping_first_name'][$loop_key];
                    $logs_orders->shipping_last_name = $params['shipping_last_name'][$loop_key];
                    $logs_orders->shipping_company = $params['shipping_company'][$loop_key];
                    $logs_orders->shipping_address_1 = $params['shipping_address_1'][$loop_key];
                    $logs_orders->shipping_address_2 = $params['shipping_address_2'][$loop_key];
                    $logs_orders->shipping_suburb = $params['shipping_suburb'][$loop_key];
                    $logs_orders->shipping_state = $params['shipping_state'][$loop_key];
                    $logs_orders->shipping_postcode = $params['shipping_postcode'][$loop_key];
                    $logs_orders->shipping_country = $params['shipping_country'][$loop_key];
                    $logs_orders->shipping_phone = $params['shipping_phone'][$loop_key];
                    $logs_orders->shipping_fax = $params['shipping_fax'][$loop_key];
                    $logs_orders->supplier_sku = $params['supplier_sku'][$loop_key];
                    $logs_orders->merchant_sku = $params['merchant_sku'][$loop_key];
                    $logs_orders->quantity = $params['quantity'][$loop_key];
                    $logs_orders->shipping_method = $params['shipping_method'][$loop_key];
                    $logs_orders->shipping_instruction = $params['shipping_instruction'][$loop_key];
                    $logs_orders->serial_no = $params['serial_no'][$loop_key];
                    $logs_orders->comments = $params['comments'][$loop_key];

                    $logs_orders_id = $logs_orders->PlaceOrder(); // Transaction ID for financial table

                    //Update Financial Info
                    $logs_financial->user_id = $user_id;
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $order_amount;
                    $logs_financial->trans_id = $logs_orders_id;

                    $logs_financial->AddLog();
                }else{
                    $notice = "E2";
                    Algorithms_Extensions_Plugin::FormatArray($check_result);die;
                }
            }
        }else{
            $notice = "E1";
        }
        
        $this->_redirect("/merchant/order-report/notice/".$notice);
    }
}

