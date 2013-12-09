<?php

class AdminController extends Zend_Controller_Action
{
  ///
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
            $auth = Zend_Auth::getInstance();
            $users = new Databases_Tables_Users();
            $user_info = $users->UserInfo();
            if(!$auth->hasIdentity() || !$user_info['user_type'])
            { 
                $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
            }elseif(1 != $user_info['user_type']){
                $this->_redirect('/merchant');
            }
            
            //get system title
            $get_title = new Databases_Tables_Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");
            $this->view->system_css     = $get_title->GetVal('system_css');
            
            //make top menu
            $menu = new Algorithms_Core_Menu();
            $top_menu = $menu->MenuForAdmin();
            $this->view->top_menu = $menu -> FormatMenu($top_menu, $this->getRequest()->getActionName());
    }
    
    /**
     * Index of Admin, Dashboard
     * Display  Product Info Table Refresh Time, Pending Order Refresh Time, Category Table Refresh Time, Merchant Feed Refresh Time
     * Dispaly 10 Recent Orders And Pending Orders
     */
    function indexAction()
    {
        $this->view->title = "Dashboard";
        //$params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard"));
        $time_now       =   time();
        $params_model   =   new Databases_Tables_Params();
        $product_info_table_refresh_time    =   $params_model->GetVal('product_info_table_refresh_time');
        $pending_order_refresh_time         =   $params_model->GetVal('pending_order_refresh_time');
        $product_categories_table_refresh_time  =   $params_model->GetVal('product_categories_table_refresh_time');
        $merchant_feed_refresh_time             =   $params_model->GetVal('merchant_feed_refresh_time');
        if(($time_now - strtotime($product_info_table_refresh_time))>86400)
        {
            $this->view->product_info_table_refresh_flag    =   1;
        }else{
            $this->view->product_info_table_refresh_flag    =   0;
        }
        if(($time_now - strtotime($pending_order_refresh_time))>86400)
        {
            $this->view->pending_order_refresh_flag    =   1;
        }else{
            $this->view->pending_order_refresh_flag    =   0;
        }
        if(($time_now - strtotime($product_categories_table_refresh_time))>86400)
        {
            $this->view->product_categories_table_refresh_flag    =   1;
        }else{
            $this->view->product_categories_table_refresh_flag    =   0;
        }
        if(($time_now - strtotime($merchant_feed_refresh_time))>86400)
        {
            $this->view->merchant_feed_refresh_flag    =   1;
        }else{
            $this->view->merchant_feed_refresh_flag    =   0;
        }
        $this->view->product_info_table_refresh_time    =   $product_info_table_refresh_time;
        $this->view->pending_order_refresh_time         =   $pending_order_refresh_time;
        $this->view->product_categories_table_refresh_time    =   $product_categories_table_refresh_time;
        $this->view->merchant_feed_refresh_time         =   $merchant_feed_refresh_time;
        
        /**
         * @var $getorders_model Databases_Joins_GetOrders
         * @todo Get Pending Order Info
         */
        $getorders_model = new Databases_Joins_GetOrders();
        $getorders_model->item_status   =   0;
        $this->view->list = $getorders_model->PushList();
        
        /**
         * @todo Get recent 10 orders
         */
        $getorders_model->item_status   =   '-1';
        $getorders_model->limit =   10;
        $this->view->recent_orders_list =   $getorders_model->PushList();
    }
       
    /**
     * Merchant List
     * Read merchant data from database
     */
    function merchantsAction()
    {
        $this->view->title = "Merchants List";
        //$params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List"));
        
        $data_model = new Databases_Joins_GetUserInfo();
        $data = $data_model ->GetUserList();
        
        $this->view->list = $data;
    }
    
    /**
     * Add Merchant
     * if post
     *  get form data
     *      if isValid
     *          getValues
     *          verify data's Correctness
     *          EditUser
     *      else
     *          data must required
     *      end if
     * end if
     */
    function merchantAddAction()
    {
        $this->view->title = "Add Merchant";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Add Merchant"));
        
        $form = new Forms_Merchant();
        $form->credit->setValue('0.00');
        $form->discount->setValue('0.00');
		$form->submitx->setLabel('Create Merchant');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
            $formData = $this->_request->getPost();
            if($form->isValid($formData)){
                $form->getValues();

                ///////////////////////////////////////////////////////////
                //check valid start

                //password check
                if($form->getValue('password') || $form->getValue('password_r'))
                {
                    if($form->getValue('password') != $form->getValue('password_r'))
                    {
                        $this->view->notice="The passwords you typed twice were different.";
                        $form->populate($formData);
                        $error = 1;
                    }
                }else
                {
                    $this->view->notice="Password is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Email format check
                if(!Algorithms_Extensions_Plugin::EmailCheck($form->getValue('email')))
                {
                    $this->view->notice="Email format is incorrect.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Credit
                if(0 > $form->getValue('credit'))
                {
                    $this->view->notice="The credit is out of range.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('company'))
                {
                    $this->view->notice="The Company is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('address'))
                {
                    $this->view->notice="The address is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('post_code'))
                {
                    $this->view->notice="The post code is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('suburb'))
                {
                    $this->view->notice="The suburb is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('state'))
                {
                    $this->view->notice="The state is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('contact_name'))
                {
                    $this->view->notice="The contact name is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('contact_phone'))
                {
                    $this->view->notice="The contact phone is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Discount
                if(0 > $form->getValue('discount') || 30 < $form->getValue('discount'))
                {
                    $this->view->notice="The discount is out of range. (0~30)";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice
                if(!$form->getValue('invoice_type')){
                    $this->view->notice="Invoice Type is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice_value
                if(!$form->getValue('invoice_type') && $form->getValue('invoice_value_'.$form->getValue('invoice_type'))){
                    $this->view->notice="Invoice Type Value is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //new users
                $check_user_string = new Databases_Tables_Users();

                //username exist
                $check_user_string->email = $form->getValue('email');
                $check_user_exist = $check_user_string ->IsUserExist();
                if($check_user_exist)
                {
                    $this->view->notice="Email is existed.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //check valid end
                ///////////////////////////////////////////////////////////

                if(!$error)
                {
                    //insert to db
                    $check_user_string->email = $form->getValue('email');
                    $check_user_string->password = md5($form->getValue('password'));
                    $check_user_string->user_type = 2; //merchant
                    $check_user_string->user_status = $form->getValue('user_status');
                    $check_user_string->company = $form->getValue('company');
                    $check_user_string->contact_name = $form->getValue('contact_name');
                    $check_user_string->contact_phone = $form->getValue('contact_phone');
                    $check_user_string->credit = $form->getValue('credit');
                    $check_user_string->discount = $form->getValue('discount');
                    
                    $check_user_string->address     =   $form->getValue('address');
                    $check_user_string->post_code   =   $form->getValue('post_code');
                    $check_user_string->suburb      =   $form->getValue('suburb');
                    $check_user_string->state       =   $form->getValue('state');
                    $check_user_string->flat_rate_shipping       =   $form->getValue('flat_rate_shipping');
                    
                    $check_user_string->invoice_type             =   $form->getValue('invoice_type');
                    $check_user_string->invoice_value            =   $form->getValue('invoice_value_'.$form->getValue('invoice_type'));
                    $check_user_string->account_name             =   $form->getValue('account_name');
                    $check_user_string->account_email            =   $form->getValue('account_email');
                    $check_user_string->account_phone            =   $form->getValue('account_phone');
                    
                    $check_user_string ->AddUser();
                    
                    $this->_redirect('admin/merchants');
                }
            }else{
                ///////////////////////////////////////////////////////////
                //check valid start

                if(!$formData['email'])
                {
                    $this->view->notice="Email is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                if(!Algorithms_Extensions_Plugin::EmailCheck($formData['email']))
                {
                    $this->view->notice="Email format is incorrect.";
                    $form->populate($formData);
                    $error = 1;
                }

                if(!trim($formData['password']))
                {
                    $this->view->notice="Password is required.";
                    $form->populate($formData);
                    $error = 1;
                }

                if(!trim($formData['password_r']))
                {
                    $this->view->notice="Password is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                if(!trim($formData['company']))
                {
                    $this->view->notice="Company is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['address']))
                {
                    $this->view->notice="Address is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['post_code']))
                {
                    $this->view->notice="Post code is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['suburb']))
                {
                    $this->view->notice="Suburb is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['state']))
                {
                    $this->view->notice="State is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['contact_name']))
                {
                    $this->view->notice="Contact name is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['contact_phone']))
                {
                    $this->view->notice="Contact phone is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                                
                //Credit
                if(0 > $formData['credit'] || $form->getValue('credit')>10000)
                {
                    $this->view->notice="The credit is out of range.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Discount
                if(0 > $formData['discount'] || 30 < $formData['discount'])
                {
                    $this->view->notice="The discount is out of range. (0~30)";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice
                if(!$$formData['invoice_type']){
                    $this->view->notice="Invoice Type is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice_value
                if(!$$formData['invoice_type'] && $$formData['invoice_value_'.$formData['invoice_type']]){
                    $this->view->notice="Invoice Type Value is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //check valid end
                ///////////////////////////////////////////////////////////

                if(!$error)
                {
                    $this->view->notice="Some information are inValid";
                    $form->populate($formData);
                }

            }
        }
    }

    /**
     * Edit Merchant
     * if post
     *  get form data
     *      if isValid
     *          getValues
     *          verify data's Correctness
     *          EditUser
     *      else
     *          data must required
     *      end if
     * else
     *  GetUserInfo
     * end if
     * 
     */
    function merchantEditAction()
    {
        $this->view->title = "Edit Merchant";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Edit Merchant|".$params['user_id']));
		
        $form = new Forms_Merchant();
        $form->submitx->setLabel('Update');
        $this->view->form = $form;

        if($this->_request->isPost()){
            $formData = $this->_request->getPost();
            if($form->isValid($formData)){
                $form->getValues();
                
                ///////////////////////////////////////////////////////////
                //check valid start

                //password check
                if($form->getValue('password') || $form->getValue('password_r'))
                {
                    if($form->getValue('password') != $form->getValue('password_r'))
                    {
                        $this->view->notice="The passwords you typed twice were different.";
                        $form->populate($formData);
                        $error = 1;
                    }
                }else
                {
                    $password_not_changed = 1;
                }
                
                //Email format check
                if(!Algorithms_Extensions_Plugin::EmailCheck($form->getValue('email')))
                {
                    $this->view->notice="Email format is incorrect.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Credit
                if(0 > $form->getValue('credit') || $form->getValue('credit')>10000)
                {
                    $this->view->notice="The credit is out of range.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Discount
                if(0 > $form->getValue('discount') || 30 < $form->getValue('discount'))
                {
                    $this->view->notice="The discount is out of range. (0 ~ 30)";
                    $form->populate($formData);
                    $error = 1;
                }
                
                if(!$form->getValue('company'))
                {
                    $this->view->notice="The Company is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('address'))
                {
                    $this->view->notice="The address is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('post_code'))
                {
                    $this->view->notice="The post code is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('suburb'))
                {
                    $this->view->notice="The suburb is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('state'))
                {
                    $this->view->notice="The state is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('contact_name'))
                {
                    $this->view->notice="The contact name is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!$form->getValue('contact_phone'))
                {
                    $this->view->notice="The contact phone is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //new users
                $check_user_string = new Databases_Tables_Users();

                //username exist
                $check_user_string->email = $form->getValue('email');
                $check_user_string->user_id = $form->getValue('user_id');
                $check_user_exist = $check_user_string ->IsUserExist();
                if($check_user_exist)
                {
                    $this->view->notice="Email is existed.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //check valid end
                ///////////////////////////////////////////////////////////

                if(!$error)
                {
                    //update to db
                    $check_user_string->user_id = $form->getValue('user_id');
                    $check_user_string->email = $form->getValue('email');
                    if(!$password_not_changed)
                    {
                        $check_user_string->password = $form->getValue('password');
                    }
                    $check_user_string->user_status = $form->getValue('user_status');
                    $check_user_string->company = $form->getValue('company');
                    $check_user_string->contact_name = $form->getValue('contact_name');
                    $check_user_string->contact_phone = $form->getValue('contact_phone');
                    $check_user_string->credit = $form->getValue('credit');
                    $check_user_string->discount = $form->getValue('discount');
                    
                    $check_user_string->address     =   $form->getValue('address');
                    $check_user_string->post_code   =   $form->getValue('post_code');
                    $check_user_string->suburb      =   $form->getValue('suburb');
                    $check_user_string->state       =   $form->getValue('state');
                    $check_user_string->flat_rate_shipping       =   $form->getValue('flat_rate_shipping');
                    $check_user_string->invoice_type             =   $form->getValue('invoice_type');
                    $check_user_string->invoice_value            =   $form->getValue('invoice_value_'.$form->getValue('invoice_type'));
                    $check_user_string->account_name             =   $form->getValue('account_name');
                    $check_user_string->account_email            =   $form->getValue('account_email');
                    $check_user_string->account_phone            =   $form->getValue('account_phone');
                    
                    $check_user_string->EditUser();
                    
                    //unset session
                    $theid = $form->getValue('user_id');
                    unset($_SESSION['user_contents'][$theid]);
                    //redirect
                    $this->_redirect('admin/merchants');
                }
            }else{
                ///////////////////////////////////////////////////////////
                //check valid start

                if(!$formData['email'])
                {
                    $this->view->notice="Email is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                if(!Algorithms_Extensions_Plugin::EmailCheck($formData['email']))
                {
                    $this->view->notice="Email format is incorrect.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['company']))
                {
                    $this->view->notice="Company is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['address']))
                {
                    $this->view->notice="Address is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['post_code']))
                {
                    $this->view->notice="Post code is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['suburb']))
                {
                    $this->view->notice="Suburb is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['state']))
                {
                    $this->view->notice="State is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['contact_name']))
                {
                    $this->view->notice="Contact name is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                if(!trim($formData['contact_phone']))
                {
                    $this->view->notice="Contact phone is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //Credit
                if(0 > $formData['credit'])
                {
                    $this->view->notice="The credit is out of range.";
                    $form->populate($formData);
                    $error = 1;
                }
                
                //Discount
                if(0 > $formData['discount'] || 30 < $formData['discount'])
                {
                    $this->view->notice="The discount is out of range. (0 ~ 30)";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice
                if(!$form->getValue('invoice_type')){
                    $this->view->notice="Invoice Type is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //invoice_value
                if(!$formData['invoice_type'] && $formData['invoice_value_'.$formData['invoice_type']]){
                    $this->view->notice="Invoice Type Value is required.";
                    $form->populate($formData);
                    $error = 1;
                }
                //check valid end
                ///////////////////////////////////////////////////////////

                if(!$error)
                {
                    $this->view->notice="Some information are inValid";
                    $form->populate($formData);
                }

            }

            //push static data
            $theid = $form->getValue('id');
            if($_SESSION['user_contents'][$theid])
            {
                $this->view->data = $_SESSION['user_contents'][$theid];
            }
        }else
        {
            if($params['user_id'])
            {
                $theid = $params['user_id'];
                $get_user_info = new Databases_Joins_GetUserInfo();
                $user = $get_user_info -> GetUserInfo($theid);
                $user['invoice_value_'.$user['invoice_type']]   =   $user['invoice_value'];
                $form->populate($user);
                $this->view->data = $user;
                $_SESSION['user_contents'][$theid] = $user;
            }
        }
    }
    
    /**
     * Admin View: Order Report
     * if get params
     * get params
     * PushList
     */
    function adminOrderReportAction()
    {
        $this->view->title = "Admin View: Order Report";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Admin - Order Report|".$params['user_id']));
        
        $getorders_model = new Databases_Joins_GetOrders();
        $getorders_model->user_id = $params['user_id'];
        if($params['start_date'])
        {
            $getorders_model->start_date = $params['start_date'];
            $this->view->start_date = $params['start_date'];
        }
        if($params['end_date'])
        {
            $getorders_model->end_date = $params['end_date'];
            $this->view->end_date = $params['end_date'];
        }
        if($params['merchant_ref'])
        {
            $getorders_model->merchant_ref = $params['merchant_ref'];
            $this->view->merchant_ref = $params['merchant_ref'];
        }
        if($params['p_current_page'])
        {
            $getorders_model->p_current_page = $params['p_current_page'];
        }else{
            $getorders_model->p_current_page = 1; //from the first page
        }
        $this->view->list = $getorders_model->PushList();
        $this->view->pagination = $getorders_model->Pagination();
        $this->view->user_id = $params['user_id'];
        $order_amount   =   $getorders_model->getAllOrderAmountTotal();
        $this->view->order_total    =   $order_amount['order_total'];
    }
    
    /**
     * Admin View: Financial Report
     * if get params
     * get params
     * end if
     * PushList
     */
    function adminFinancialReportAction()
    {
        $this->view->title = "Admin View: Financial Report";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Admin - Financial Report|".$params['user_id']));
        
        $logs_orders_model = new Databases_Tables_LogsFinancial();
        $logs_orders_model->user_id = $params['user_id'];
        if($params['start_date'])
        {
            $logs_orders_model->start_date = $params['start_date'];
            $this->view->start_date = $params['start_date'];
        }
        if($params['end_date'])
        {
            $logs_orders_model->end_date = $params['end_date'];
            $this->view->end_date = $params['end_date'];
        }
        if($params['action_type'])
        {
            $logs_orders_model->action_type = $params['action_type'];
            $this->view->action_type = $params['action_type'];
        }
        if($params['p_current_page'])
        {
            $logs_orders_model->p_current_page = $params['p_current_page'];
        }else{
            $logs_orders_model->p_current_page = 1; //from the first page
        }
        $this->view->list = $logs_orders_model->PushList();
        $this->view->pagination = $logs_orders_model->Pagination();
        $this->view->user_id = $params['user_id'];
        
        $financial_action_type_model = new Databases_Tables_FinancialActionType();
        $financial_action_type = $financial_action_type_model -> GetType();
        $this->view->financial_action_type = $financial_action_type;
    }
    
    /**
     * Help Management
     */
    function helpMgtAction()
    {
        $this->view->title = "Help Management";
        //$params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help Mgt"));
        
        $get_contents = new Databases_Tables_Helpdesk();
        $this->view->list = $get_contents->PushList();
        
        $get_category = new Databases_Tables_HelpdeskCategory();
        $this->view->category_array = $get_category->GetCategory();
    }
    
    /**
     * Add Topic
     */
    function helpAddAction()
    {
        $this->view->title = "Add Topic";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help Mgt", "Add Help"));
        
        $form = new Forms_Help();
	$form->submitx->setLabel('Create Topic');
	$this->view->form = $form;
		
	if($this->_request->isPost()){
            $formData = $this->_request->getPost();
            if($form->isValid($formData)){
                $form->getValues();

                if(!$error)
                {
                    //insert to db
                    $help_model = new Databases_Tables_Helpdesk();
                    $help_model->category = $form->getValue('category');
                    $help_model->h_subject = $form->getValue('h_subject');
                    $help_model->h_contents = $form->getValue('h_contents');
                    $help_model->AddHelp();
                    
                    $this->_redirect('admin/help-mgt');
                }
            }else{
                $this->view->notice="Some information are inValid";
                $form->populate($formData);
            }
        }
    }
    
    /**
     * Edit Topic
     */
    function helpEditAction()
    {
        $this->view->title = "Edit Topic";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help Mgt", "Edit Help|".$params['user_id']));
		
        $form = new Forms_Help();
        $form->submitx->setLabel('Update');
        $this->view->form = $form;

        if($this->_request->isPost()){
            $formData = $this->_request->getPost();
            if($form->isValid($formData)){
                $form->getValues();

                if(!$error)
                {
                    $help_model = new Databases_Tables_Helpdesk();
                    
                    //update to db
                    $help_model->helpdesk_id = $form->getValue('helpdesk_id');
                    $help_model->category = $form->getValue('category');
                    $help_model->h_subject = $form->getValue('h_subject');
                    $help_model->h_contents = $form->getValue('h_contents');
                    $help_model->EditHelp();
                    
                    //unset session
                    $theid = $form->getValue('helpdesk_id');
                    unset($_SESSION['help_contents'][$theid]);
                    //redirect
                    $this->_redirect('admin/help-mgt');
                }
            }else{
                $this->view->notice="Some information are inValid";
                $form->populate($formData);
            }

            //push static data
            $theid = $form->getValue('helpdesk_id');
            if($_SESSION['help_contents'][$theid])
            {
                $this->view->data = $_SESSION['help_contents'][$theid];
            }
        }else
        {
            if($params['helpdesk_id'])
            {
                $theid = $params['helpdesk_id'];
                $get_help_info = new Databases_Tables_Helpdesk();
                $get_help_info->helpdesk_id = $theid;
                $help_info = $get_help_info->GetHelpInfo();
                $form->populate($help_info);
                $this->view->data = $help_info;
                $_SESSION['help_contents'][$theid] = $help_info;
            }
        }
    }
    
    /**
     * Update Help Status
     */
    function updateHelpStatusAction()
    {
        $params = $this->_request->getParams();
        
        if($params['helpdesk_id'])
        {
            $help_model = new Databases_Tables_Helpdesk();
            $help_model->helpdesk_id = $params['helpdesk_id'];
            $help_model->UpdateStatus();
            //redirect
            $this->_redirect('admin/help-mgt');
        }
    }
    
    /**
     * Help Preview
     */
    function helpPreviewAction()
    {
        $params = $this->_request->getParams();
        $helpdesk_model = new Databases_Tables_Helpdesk();
        $helpdesk_model->helpdesk_id = $params['helpdesk_id'];
        $help_info = $helpdesk_model->GetHelpInfo();
        
        $this->view->title = "Help Preview - ".$help_info['h_subject'];
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Help Mgt", "Help Preview|".$params['helpdesk_id']));
        
        $this->view->help_info = $help_info;
    }
    
    /**
     * Merchant Recharge - Adjust Balance
     */
    function merchantRechargeAction()
    {
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Adjust Balance|".$params['user_id']));
        
        $user_model = new Databases_Joins_GetUserInfo();
        $this->view->user = $user_model->GetUserInfo($params['user_id']);
        
        $this->view->title = "Adjust balance for merchant";
    }
    
    /**
     * Merchant Recharge Preview
     * get params
     * verify params
     * update merchant balance
     */
    function merchantRechargePreviewAction()
    {
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Adjust Balance|".$params['user_id']));
        
        if(0 >= $params['val'])
        {
            echo "Error: The value must be a positive number.";die;
        }
        
        $user_model = new Databases_Joins_GetUserInfo();
        $this->view->user = $user_model->GetUserInfo($params['user_id']);
        
        if(1 == $params['update'])
        {
            if($params['adj'] && $params['val'] && $params['user_id'])
            {
                //Action
                $logs_financial = new Databases_Tables_LogsFinancial();
                $logs_financial->user_id = $params['user_id'];
                $logs_financial->action_type = 3; //Adjustment
                if("+" == $params['adj'])
                {
                    $logs_financial->action_affect = 1; //Recharge
                }elseif("-" == $params['adj'])
                {
                    $logs_financial->action_affect = 2; //Deduct
                }else{
                    $logs_financial->action_affect = 0; //Error
                }
                $logs_financial->action_value = $params['val'];
                if($logs_financial->AddLog())
                {
                    $this->_redirect("/admin/merchants");
                }else{
                    echo "Error: Action is rejected, please contact system administrator.";die;
                }
                
            }else{
                echo "Error: Invalid Action.";
                die;
            }
        }else{
            $this->view->title = "Adjust balance preview";
            $this->view->current_balance = $this->view->user['balance'];
            if(1 == $params['adj'])
            {
                $this->view->action_type = "+";
                $this->view->new_balance = round(($this->view->user['balance'] +$params['val']), 2);
            }elseif(2 == $params['adj']){
                $this->view->action_type = "-";
                $this->view->new_balance = round(($this->view->user['balance'] - $params['val']), 2);
            }else
            {
                echo "Error: Invalid Action.";
                die;
            }
            $this->view->adjust_value = $params['val'];
            $this->view->user_id = $params['user_id'];
        }
    }
    
    function bpayImportAction()
    {
        $this->view->title = "Import BPay CSV Files";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "BPay Import"));
        
        if(1 == $params['result'])
        {
            $this->view->notice = "<font color='green'>Action completed.</font>";
        }
    } 
    
    function bpayImportPreviewAction()
    {
        $valid_file_types = array('application/octet-stream',
                                  'text/csv',
                                  'application/vnd.ms-excel'
                            );
        
        $this->view->title = "Import BPay CSV Files Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "BPay Import"));
        
        if ($_FILES["csvf"]["error"] > 0)
        {
            $this->view->notice = $_FILES["csvf"]["error"];
        }else{
            if( in_array($_FILES["csvf"]["type"], $valid_file_types) )
            {
                $this->view->notice = "File type is invalid.";
            }else{
                //Action
                $bpay_model = new Algorithms_Extensions_Bpay();
                $data_array = array();
                if (($handle = fopen($_FILES["csvf"]["tmp_name"], "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $data_array[] = $data;
                    }
                    fclose($handle);
                    
                    $this->view->list = $bpay_model->CheckCSV($data_array);
                    
                }else{
                    $this->view->notice = "Error.";
                }
            }
        }
    }
    
    function bpayImportConfirmAction()
    {
        $this->view->title = "Import BPay CSV Files";
        $params = $this->_request->getParams();
        
        $tmp_data = array();
        $logs_financial = new Databases_Tables_LogsFinancial();
        $user_extension = new Databases_Tables_UsersExtension();
        
        $row = count($params['customer_ref']);
        
        for($n=2;$n<=$row;$n++) //ignore title
        {
            $tmp_data[] = array(
                "customer_ref" => $params['customer_ref'][$n],
                "transaction_ref" => $params['transaction_ref'][$n],
                "amount" => $params['amount'][$n]
            );
        }
        
        if(count($tmp_data))
        {
            foreach($tmp_data as $data)
            {
                $logs_financial->user_id = $user_extension->GetUserId(1, $data['customer_ref']);
                $logs_financial->action_type = 2; //recharge
                $logs_financial->action_affect = 1; //plus
                $logs_financial->action_value = $data['amount'];
                $logs_financial->trans_id = $data['transaction_ref'];
                /**
                 * @var $logs_financial Databases_Tables_LogsFinancial
                 * @todo AddLog and recharge
                 */
                $logs_financial->AddLog();
            }
        }
        
        $this->_redirect("/admin/bpay-import/result/1");
    }
    
    /**
     * Merchants List Feed Generation
     * get params 
     * verify params
     */
    function merchantFeedGenerationAction()
    {
        $this->view->title = "Feed Generation";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Feed Generation|".$params['user_id']));
        
        if($params['user_id'])
        {
            $user_model = new Databases_Joins_GetUserInfo();
            $this->view->user_info = $user_model->GetUserInfo($params['user_id']);
            
            $users_feed = new Databases_Tables_UsersFeed();
            $this->view->users_feed =  $users_feed->GetFeedInfo($params['user_id']);
            
            $feed_dictionary = new Databases_Tables_FeedDictionary();
            $dump_feed_dictionary = $feed_dictionary->DumpAll(1);
            
            $shipping_courier_model             =   new Databases_Tables_ShippingCourier();
            $this->view->shipping_courier_list  =   $shipping_courier_model->GetShippingCouriers();
            
            $supplier_type_model                =   new Databases_Tables_SupplierType();
            $this->view->suppliert_type_list    =   $supplier_type_model->GetSupplierType();
            
            $user_feed_definition = new Databases_Tables_UsersFeedDefinition();
            $this->view->get_column_info = $user_feed_definition->ElementsForList($dump_feed_dictionary, $this->view->users_feed['users_feed_id']);
             
            //Generate Category
            $product_categories_model = new Databases_Tables_ProductCategories();
            $this->view->product_categories = $product_categories_model->TreeForMerchant($this->view->users_feed['feed_category']);
            $_SESSION['b2b']['merchant_feed_generation'][$params['user_id']] = $this->view->product_categories;
            
            if($this->view->users_feed['users_feed_id'])
            {
                $get_feed_path = new Algorithms_Extensions_Plugin();
                $this->view->feed_path = $get_feed_path->GetFeedPath($this->view->users_feed['feed_name'], $this->view->users_feed['feed_extension']);
            }else{
                $this->view->notice = "<font color='#ff0000'><strong>No feed existed, please create a new one.</strong></font>";
                
                $plugin_model = new Algorithms_Extensions_Plugin();
                $this->view->initial_feed_name = $plugin_model->GenerateInitialFeedName($this->view->user_info['company']);
            }
            
            switch ($params['result'])
            {
                case 1:
                    $this->view->notice = "<font color='green'>* Data saved succesfully.</strong></font>";
                    break;
                case 2:
                    $this->view->notice = "<font color='#ff0000'><strong>* Data saved failed.</strong></font>";
                    break;
                default:
                    break;
            }
            
        }else{
            echo "Invalid Action.";
            die;
        }
    }
    
    /**
     * Merchant Feed Generation Confirm
     * Get post data 
     * UpdateFeed
     * _redirect
     */
    function merchantFeedGenerationConfirmAction()
    {
        $this->view->title = "Feed Generation Confirmation";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Feed Generation|".$params['user_id']));
        
//        Algorithms_Extensions_Plugin::FormatArray($params);
//        echo "<br />===================<br />";
//        Algorithms_Extensions_Plugin::FormatArray($_SESSION['b2b']['merchant_feed_generation']);
//        die;
        
        $product_categories_model = new Databases_Tables_ProductCategories();
        
        $users_feed_model = new Databases_Tables_UsersFeed();
        $users_feed_model->user_id = $params['user_id'];
        $users_feed_model->feed_name = $params['feed_name'];
        $users_feed_model->feed_extension = $params['feed_extension'];
        $users_feed_model->feed_delimeter = $params['feed_delimeter'];
        $users_feed_model->special_delimeter = $params['special_delimeter'];
        $users_feed_model->feed_qualifier = $params['feed_qualifier'];
        $users_feed_model->feed_category = $product_categories_model->CheckedArray($_SESSION['b2b']['merchant_feed_generation'][$params['user_id']]);
        $users_feed_model->sku_included = $params['sku_included'];
        $users_feed_model->sku_excluded = $params['sku_excluded'];
        $users_feed_model->stock = $params['stock'];
        $sc_class_array             =   $params['sc_class'];
        $supplier_type_array        =   $params['supplier_type'];
        $sc_class                   =   implode(',', $sc_class_array);
        $supplier_type              =   implode(',', $supplier_type_array);
        $shipping_courier_model     =   new Databases_Tables_ShippingCourier();
        $shipping_courier_list      =   $shipping_courier_model->GetShippingCouriers();

        $supplier_type_model        =   new Databases_Tables_SupplierType();
        $suppliert_type_list        =   $supplier_type_model->GetSupplierType();
        if(count($sc_class_array)==count($shipping_courier_list)){
            $users_feed_model->sc_class =   '';
        }else{
            $users_feed_model->sc_class =   $sc_class;
        }
        if(count($supplier_type_array)==count($suppliert_type_list)){
            $users_feed_model->supplier_type    =   '';
        }else{
            $users_feed_model->supplier_type    =   $supplier_type;
        }
        $users_feed_model->cost_protection  =   $params['cost_protection'];
        $users_feed_model->feed_product_type     =   $params['feed_product_type'];
        $users_feed_model->feed_column_definition = array(
            "ckd" => $params['ckd'],
            "column_alias" => $params['column_alias'],
            "column_value" =>$params['column_value'],
            "column_value_adjustment" =>$params['column_value_adjustment']
        );
        
        if($users_feed_model->UpdateFeed())
        {
            $this->_redirect("/admin/merchant-feed-generation/user_id/".$params['user_id']."/result/1");
        }else{
            $this->_redirect("/admin/merchant-feed-generation/user_id/".$params['user_id']."/result/2");
        }
    }
    
    function adminImportOrderAction()
    {
        $this->view->title = "Admin Import Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Admin Import Order"));
    }
    
    /**
     * Import Order Preview
     * get csv file data
     * start loop 
     * get order info
     * PlaceOrderCheck
     * finish loop
     */
    function adminImportOrderPreviewAction()
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
        
        $this->view->title = "Admin Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Admin Import Order"));

        if ($_FILES["csvf"]["error"] > 0)
        {
            $this->view->notice = $_FILES["csvf"]["error"];
        }else{	
            if( in_array($_FILES["csvf"]["type"], $valid_file_types) )
            {                
                //Action
                $group_instance_balance_array = array();
                $getorders_model = new Databases_Joins_GetOrders();
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
                                $getorders_model->merchant_company = $da_val[17]; // REQUIRED AND IMPORTANT !!!
                                $getorders_model->shipping_address_1 = $da_val[4];
                                $getorders_model->shipping_suburb = $da_val[6];
                                $getorders_model->shipping_state = $da_val[7];
                                $getorders_model->shipping_postcode = $da_val[8];
                                $getorders_model->shipping_phone    =   $da_val[10];
                                $getorders_model->shipping_country = $da_val[9];
                                $getorders_model->supplier_sku = $da_val[11];
                                $getorders_model->quantity = $da_val[13];
                                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                                $getorders_model->pick_up = $da_val[18];
                                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                                $quantity_array[$da_val[11]]    +=   $da_val[13];
                                $getorders_model->quantity_array    =   $quantity_array;
                                $users_extension_model = new Databases_Tables_UsersExtension();
                                $users_extension_model->company =  $da_val[17];
                                $user_info = $users_extension_model->CheckCompanyInCsv();
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
                                $data_array[$da_key]['order_amount'] = (float)$check_result['order_amount'];
                                $data_array[$da_key]['instant_balance'] = (float)$check_result['instant_balance'];
                                $data_array[$da_key]['credit'] = $check_result['credit'];
                                $data_array[$da_key]['user_id'] = $check_result['user_id'];
                                $data_array[$da_key][11]    =   $da_val[11];
                                
                                /**
                                 * @var $product_filter_model Databases_Joins_ProductFilter
                                 * @todo Get Product Info
                                 */
                                $product_info   =   $product_filter_model->getProductInfo($da_val[11]);
                                $data_array[$da_key]['product_name']    =   $product_info['product_name'];
                                $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                                //update instant balance
                                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
                            }
                        }
                    }
                    
                    $this->view->list = $data_array;
                    
                }else{
                    $this->view->notice = "Error.";
                }
            }else{
                $this->view->notice = "File type is invalid.";
            }
        }
    }
    
    /**
     * Improt Order Confirm 
     * Get Orders Data To PlaceOrder and SetOrderStatus before Import Order Success
     * start loop
     * validation
     * insert into orders
     * finish loop
     * operate_orders_model->placeorder
     */
    function adminImportOrderConfirmAction()
    {
        /**
         * start loop
         * validation
         * insert into orders
         * update financial table
         * finish loop
         */
        
        $this->view->title = "Admin Order Import Confirmation";
        $params = $this->_request->getParams();
        //Algorithms_Extensions_Plugin::FormatArray($params);die;
        $getorders_model = new Databases_Joins_GetOrders();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
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
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                $users_extension_model = new Databases_Tables_UsersExtension();
                $users_extension_model->company = $params['merchant_company'][$loop_key];
                $user_info = $users_extension_model->CheckCompanyInCsv();
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
                 * @todo Check Order 
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
                    
                    $sku_prices_info    =   $product_filter_model->GetSkuPrices($params['supplier_sku'][$loop_key], $user_id);
                    
                    $getorders_model->expected_item_cost    =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_item_cost       =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_ship_cost       =   round($check_result['shipping_cost'],2);
                    $getorders_model->ship_cost             =   round($check_result['shipping_cost'],2);
                    $getorders_model->payment_type_id       =   9;
                    //$getorders_model->item_amount           =   round($sku_prices_info['supplier_price'],2) + round($check_result['shipping_cost'],2);
                    $getorders_model->item_amount           =   $order_amount;
                    $getorders_model->shipping_courier      =   trim($sku_prices_info['shipping_courier']);
                    $getorders_model->sc_class              =   trim($sku_prices_info['sc_class']);
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
            $operate_orders_model   =   new Databases_Joins_OperateOrders();
            $operate_orders_model->purchase_order_ids   =   $purchase_order_ids;
            $result = $operate_orders_model->PlaceOrder();
        }else{
            $notice = "E1";
        }
        
        $this->_redirect("/admin/admin-import-order/notice/".$notice);
    }
    /*
    function productInfoListAction()
    {
        
        $this->view->title  =   "Product Info List";
        $params =   $this->_request->getParams();
        $menu_model =   new Algorithms_Core_Menu();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard","Product Info"));
        $produtct_info_1_model  =   new Databases_Tables_ProductInfo1();
        $search_types   =   array(
            'supplier_sku'  =>  'supplier_sku',
            'product_name'  =>  'product_name',
            'product_id'    =>  'product_id',
        );
        $search_column    =   $params['search_column'];
        $search_value    =   $params['search_value'];
        if($params['search_column'])
        {
            $produtct_info_1_model->search_column =  $params['search_column'];
        }
        if($params['search_value'])
        {
            $produtct_info_1_model->search_value =  $params['search_value'];
        }
        if($params['p_current_page'])
        {
            $produtct_info_1_model->p_current_page  =   $params['p_current_page'];
        }else
        {
            $produtct_info_1_model->p_current_page  =   1;
        }
        $this->view->search_column   =   $search_column;
        $this->view->search_value   =   $search_value;
        $this->view->search_types   =   $search_types;
        $this->view->list   =   $produtct_info_1_model->Pushlist();
        $this->view->pagination =   $produtct_info_1_model->Pagination();
    }

    function productDelAction()
    {

        $this->view->title  =   "Sure to delete this product";
        $params =   $this->_request->getParams();
        $menu_model =   new Algorithms_Core_Menu();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard","Product Info"));
        
        $product_info_1_model   =   new Databases_Tables_ProductInfo1();
        
        $product_info_1_model->product_info_id  =   $params['product_id'];
        
        $product_info   =   $product_info_1_model->getProductInfo();
        
        $this->view->productInfo    =   $product_info;
    }
    
    function productDelConfirmAction()
    {
        $params =   $this->_request->getParams();
        $product_info_1_model   =   new Databases_Tables_ProductInfo1();
        $menu_model =   new Algorithms_Core_Menu();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard","Product Info"));        
        $product_info_1_model->product_info_id  =   $params['product_id'];
        
        $delState   =   $product_info_1_model->delProductInfo();
        if($delState)
        {
            $this->_redirect('admin/product-info-list',array('message'=>'Product Info Delete Success.'));
        }else
        {
            $this->_redirect('admin/product-info-list',array('message'=>'Product Info Delete failed.'));
        }
    }
    
    function productEditAction()
    {
        $this->view->title  =   "Product Info Edit";
        $params =   $this->_request->getParams();
        $menu_model =   new Algorithms_Core_Menu();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard","Product Info"));        
        $product_info_1_model   =   new Databases_Tables_ProductInfo1();
        $product_info_1_model->product_info_id  =   $params['product_id'];
        
        $form   =   new Forms_Product();
        $form->submitx->setLabel('Update');
        $this->view->form   =   $form;
        if($this->_request->isPost())
        {
            $form_data  =   $this->_request->getPost();
            if($form->isValid($form_data))
            {
                $form->getValues();
                
                if(!$error)
                {
                    $product_info_1_model->offer_price  =   $form->getValue("offer_price");
                    $product_info_1_model->product_name =   $form->getValue("product_name");
                    $product_info_1_model->product_details  =   $form->getValue("product_details");
                    
                    $product_info_1_model->editProductInfo();
                    $this->_redirect("admin/product-info-list");
                }
            }else
            {
                $this->view->notice="Some information are inValid";
                $form->populate($form_data);
            }
        }
        else
        {
            $product_info   =   $product_info_1_model->getProductInfo();
            $this->view->product_info   =   $product_info;
            $form->populate($product_info);
            $this->view->data   =   $product_info;
        }
    }
    
    function productAddAction()
    {
        $this->view->title  =   "Add Product";
        $menu_model =   new Algorithms_Core_Menu();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard","Product Info"));
        $form   =   new Forms_Product();
        $form->submitx->setLabel("Add Product");
        $this->view->form   =   $form;
        if($this->_request->isPost())
        {
            $form_data  =   $this->_request->getPost();
            if($form->isValid($form_data))
            {
                $form->getValues();
                
                if(!$error)
                {
                    $produt_info_1_model    =   new Databases_Tables_ProductInfo1();
                    $produt_info_1_model->supplier_sku  =   $form->getValue("supplier_sku");
                    $produt_info_1_model->offer_price   =   $form->getValue("offer_price");
                    $produt_info_1_model->product_name   =   $form->getValue("product_name");
                    $produt_info_1_model->product_details   =   $form->getValue("product_details");
                    $produt_info_1_model->AddProduct();
                    $this->_redirect("admin/product-info-list");
                }  else {
                    $this->view->notice =   "Some information are inValid.";
                    $form->populate($form_data);
                }
            }
        }
    }
     * 
     */
    /**
     * Order View
     * Get Order Detail Info Include Order Date, Order Number, Order Status, Order Subtotal, Order Amount, Order Items, Order Shipping Information
     */
    function adminOrderViewAction()
    {
        $this->view->title      =   "Order View";
        $menu_model = new Algorithms_Core_Menu;
        $params =   $this->_request->getParams();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard", "Merchants List","Admin - Order Report|".$params['user_id'],"Order View"));
        $purchase_order_id  =   $params['order_id'];
        $product_info_model =   new Databases_Joins_ProductFilter();
        $purchase_order_model   =   new Databases_Tables_PurchaseOrder();
        $log_order_model        =   new Databases_Tables_LogsOrders();
        $purchase_order_model->purchase_order_ids    =   $purchase_order_id;
        $log_order_model->purchase_order_id         =   $purchase_order_id;
        $purchase_order_info    =   $purchase_order_model->GetPurchaseOrder();
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
    
    function adminImportDdOrderAction()
    {
        $this->view->title = "Admin Import DealsDirect Order";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Admin Import DealsDirect Order"));
    }
    
    /**
     * Import DealsDirect Orders Preview
     */
    function adminImportDdOrderPreviewAction (){
        $valid_file_types = array('application/octet-stream',
                                  'text/csv',
                                  'application/vnd.ms-excel'
                            );
        
        $this->view->title = "Admin Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Admin Import Order"));

        //$merchant_company   =   'Test Company';
        $merchant_company   =   'DealsDirect';
        $pick_up            =   'N';
        if ($_FILES["csvf"]["error"] > 0)
        {
            $this->view->notice = $_FILES["csvf"]["error"];
        }else{	
            if( in_array($_FILES["csvf"]["type"], $valid_file_types) )
            {                
                //Action
                $group_instance_balance_array = array();
                $getorders_model = new Databases_Joins_GetOrders();
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
				$post_code  =   trim($da_val[7]);
                                if(strlen($post_code)==3){
                                    $post_code  =   '0'.$post_code;
                                }
                                $supplier_sku    =   substr(trim($da_val[9]), 0, -3);
                                $full_name_array    = array_values(array_filter(explode(' ', trim($da_val[1]))));
                                
                                $getorders_model->shipping_first_name   =   $full_name_array[0];
                                $getorders_model->shipping_last_name    =   $full_name_array[1];
                                $getorders_model->shipping_company      =   trim($da_val[18]);
                                $getorders_model->merchant_company      =   $merchant_company;
                                $getorders_model->shipping_address_1    =   trim($da_val[3]).' '. trim($da_val[4]);
                                $getorders_model->shipping_suburb       =   trim($da_val[5]);
                                $getorders_model->shipping_state        =   trim($da_val[6]);
                                $getorders_model->shipping_postcode     =   $post_code;
                                $getorders_model->shipping_country      =   'AU';
                                $getorders_model->shipping_phone        =   trim($da_val[8])?trim($da_val[8]):'1';
                                $getorders_model->supplier_sku          =   $supplier_sku;
                                $getorders_model->quantity              =   trim($da_val[11]);
                                $getorders_model->operator_id           =   '1';
                                $getorders_model->pick_up               =   $pick_up;
                                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                                $params_array   =   array(
                                    'free_shipping' => 1
                                );
                                $getorders_model->params_array          =   $params_array;
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
                                $data_array[$da_key][11]    =   $da_val[11];
                                $data_array[$da_key]['supplier_sku']    =   $supplier_sku;
                                $data_array[$da_key]['shipping_first_name'] =   $full_name_array[0];
                                $data_array[$da_key]['shipping_last_name']  =   $full_name_array[1];
                                $data_array[$da_key]['merchant_company']    =   $merchant_company;
                                $data_array[$da_key]['shipping_country']    =   'AU';
                                $data_array[$da_key]['pick_up']             =   $pick_up;
                                $data_array[$da_key]['product_title']       =   $da_val[10];
                                $data_array[$da_key]['card_id']             =   $da_val[12];
                                $data_array[$da_key]['ref_num']             =   $da_val[13];
                                $data_array[$da_key]['cost']                =   $da_val[14];
                                $data_array[$da_key]['freight']             =   $da_val[15];
                                /**
                                 * @var $product_filter_model Databases_Joins_ProductFilter
                                 * @todo Get Product Info
                                 */
                                $product_info   =   $product_filter_model->getProductInfo($supplier_sku);
                                $data_array[$da_key]['product_name']    =   $product_info['product_name'];
                                $data_array[$da_key]['imageURL0']       =   $product_info['imageURL0'];
                                //update instant balance
                                $group_instance_balance_array[$check_result['user_id']] = $check_result['instant_balance'];
                            }
                        }
                    }
                    $this->view->list = $data_array;
                    
                }else{
                    $this->view->notice = "Error.";
                }
            }else{
                $this->view->notice = "File type is invalid.";
            }
        }
    }
    
    function adminImportDdOrderConfirmAction(){
        $this->view->title = "Admin Order DealsDirect Import Confirmation";
        $params = $this->_request->getParams();
        //Algorithms_Extensions_Plugin::FormatArray($params);die;
        $getorders_model = new Databases_Joins_GetOrders();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $dd_orders_model        =   new Databases_Tables_DdOrders();
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
                $getorders_model->shipping_suburb = trim($params['shipping_suburb'][$loop_key]);
                $getorders_model->shipping_state = trim($params['shipping_state'][$loop_key]);
                $getorders_model->shipping_postcode = trim($params['shipping_postcode'][$loop_key]);
                $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                $getorders_model->shipping_phone = trim($params['shipping_phone'][$loop_key])?trim($params['shipping_phone'][$loop_key]):'1';
                $getorders_model->supplier_sku = $supplier_sku;
                $getorders_model->quantity = $params['quantity'][$loop_key];
                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
                $getorders_model->pick_up = $params['pick_up'][$loop_key];
                $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                $users_extension_model = new Databases_Tables_UsersExtension();
                $users_extension_model->company = $params['merchant_company'][$loop_key];
                $params_array   =   array(
                    'free_shipping' => 1
                );
                $getorders_model->params_array  =   $params_array;
                /**
                 * @var $getorders_model Databases_Joins_GetOrders
                 * @todo Check Order 
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
                    $getorders_model->shipping_postcode = trim($params['shipping_postcode'][$loop_key]);
                    $getorders_model->shipping_country = $params['shipping_country'][$loop_key];
                    $getorders_model->shipping_phone = trim($params['shipping_phone'][$loop_key])?trim($params['shipping_phone'][$loop_key]):'1';
                    $getorders_model->shipping_fax = $params['shipping_fax'][$loop_key];
                    $getorders_model->supplier_sku = $params['supplier_sku'][$loop_key];
                    $getorders_model->merchant_sku = $params['merchant_sku'][$loop_key];
                    $getorders_model->quantity = trim($params['quantity'][$loop_key]);
                    $getorders_model->shipping_method = $params['shipping_method'][$loop_key];
                    $getorders_model->shipping_instruction = $params['shipping_instruction'][$loop_key];
                    $getorders_model->serial_no = $params['serial_no'][$loop_key];
                    $getorders_model->comments = $params['comments'][$loop_key];
                    $getorders_model->pick_up = $params['pick_up'][$loop_key];
                    $getorders_model->merchant_ref_pool = $merchant_ref_pool;
                    $getorders_model->discount_amount   =   round($check_result['discount_amount'],2);
                    $getorders_model->shipping_cost   =   round($check_result['shipping_cost'],2);
                    
                    $sku_prices_info    =   $product_filter_model->GetSkuPrices($params['supplier_sku'][$loop_key], $user_id);
                    
                    $getorders_model->expected_item_cost    =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_item_cost       =   round($sku_prices_info['supplier_price'],2);
                    $getorders_model->final_ship_cost       =   round($check_result['shipping_cost'],2);
                    $getorders_model->ship_cost             =   round($check_result['shipping_cost'],2);
                    $getorders_model->payment_type_id       =   9;
                    //$getorders_model->item_amount           =   round($sku_prices_info['supplier_price'],2) + round($check_result['shipping_cost'],2);
                    $getorders_model->item_amount           =   $order_amount;
                    $getorders_model->shipping_courier      =   trim($sku_prices_info['shipping_courier']);
                    $getorders_model->sc_class              =   trim($sku_prices_info['sc_class']);
                    /**
                     * @todo PlaceOrder
                     */
                    $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                    //update merchant ref pool
                    $merchant_ref_pool = $place_order_return['merchant_ref_pool'];
                }else{
                    $notice = "E2";
                }
                /**
                * @todo Add DD order
                */
                $dd_orders_model->o_num             =   $params['merchant_ref'][$loop_key];
                $dd_orders_model->buyer_full_name   =   $params['buyer_full_name'][$loop_key];
                $dd_orders_model->company           =   $params['shipping_company'][$loop_key];
                $dd_orders_model->address_line_1    =   $params['shipping_address_1'][$loop_key];
                $dd_orders_model->address_line_2    =   $params['shipping_address_2'][$loop_key];
                $dd_orders_model->suburb            =   $params['shipping_suburb'][$loop_key];
                $dd_orders_model->state             =   $params['shipping_state'][$loop_key];
                $dd_orders_model->post_code         =   $params['shipping_postcode'][$loop_key];
                $dd_orders_model->phone_num         =   $params['shipping_phone'][$loop_key];
                $dd_orders_model->product_code      =   $params['merchant_sku'][$loop_key];
                $dd_orders_model->product_title     =   $params['product_title'][$loop_key];
                $dd_orders_model->qty               =   $params['quantity'][$loop_key];
                $dd_orders_model->cart_id           =   $params['card_id'][$loop_key];
                $dd_orders_model->ref_num           =   $params['ref_num'][$loop_key];
                $dd_orders_model->cost              =   $params['cost'][$loop_key];
                $dd_orders_model->freight           =   $params['freight'][$loop_key];
                if("Y" == $check_result[1]){
                    $dd_orders_model->status            =   1;//Approved
                    $dd_orders_model->error_message     =   '';
                }else{
                    $dd_orders_model->status            =   5;//Canceled
                    $dd_orders_model->error_message     =   $check_result[2];
                }
                $dd_order_id    =   $dd_orders_model->addDdOrder();
                /**
                 * @todo updateDdOrderB2bOrderId
                 */
                //print_r( $place_order_return['purchase_order_id']);
                if("Y" == $check_result[1] && $place_order_return['purchase_order_id'] && $dd_order_id){
                    $dd_orders_model->b2b_order_id      =   $place_order_return['purchase_order_id'];
                    $dd_orders_model->order_id          =   $dd_order_id;
                    $dd_orders_model->updateDdOrderB2bOrderId();
                }
                unset($place_order_return);
            }
            
            $purchase_order_ids =   implode(',',$merchant_ref_pool);
            $operate_orders_model   =   new Databases_Joins_OperateOrders();
            $operate_orders_model->purchase_order_ids   =   $purchase_order_ids;
            $result = $operate_orders_model->PlaceOrder();
            if($result['orders']){
                foreach ($result['orders'] as $key => $order){
                    $dd_orders_model->b2b_order_id  =   $order['purchase_order_id'];
                    $dd_orders_model->cc_order_id   =   $order['main_order_id'];
                    $dd_orders_model->status        =   1;  //Approved
                    $dd_orders_model->updateDdOrderCcOrderID();
                }
            }
        }else{
            $notice = "E1";
        }
        $this->_redirect("/admin/admin-import-order/notice/".$notice);
    }
    
    /**
     * Invoice list
     */
    function invoiceAction(){
        $this->view->title = "Invoice";
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $invoice_model  =   new Databases_Tables_InvoiceList();
        if($params['page']){
            $invoice_model->page_now    =   $params['page'];
        }else{
            $invoice_model->page_now    =   1;
        }
        $result_data            =   $invoice_model->getInvoiceList();
        $this->view->navigation =   $menu_model->GetNavigation(array("Dashboard", "Invoice"));
        $this->view->list       =   $result_data['records'];
        $this->view->pagination =   $result_data['page_html'];
    }
}

