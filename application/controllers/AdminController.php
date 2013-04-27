<?php

class AdminController extends Zend_Controller_Action
{
  
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
            $auth = Zend_Auth::getInstance();
            $users = new Databases_Tables_Users();
            $user_info = $users->UserInfo();
            if(!$auth->hasIdentity())
            { 
                $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
            }elseif(1 != $user_info['user_type']){
                $this->_redirect('/merchant');
            }
            
            //get system title
            $get_title = new Databases_Tables_Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");

            //make top menu
            $menu = new Algorithms_Core_Menu();
            $top_menu = $menu->MenuForAdmin();
            $this->view->top_menu = $menu -> FormatMenu($top_menu, $this->getRequest()->getActionName());
    }
	
    function indexAction()
    {
        $this->view->title = "Dashboard";
        //$params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard"));
    }
    
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
                
                //Discount
                if(0 > $form->getValue('discount') || 30 < $form->getValue('discount'))
                {
                    $this->view->notice="The discount is out of range. (0~30)";
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
                    $this->view->notice="The discount is out of range. (0~30)";
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
                if(0 > $form->getValue('credit'))
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
                $form->populate($user);
                $this->view->data = $user;
                $_SESSION['user_contents'][$theid] = $user;
            }
        }
    }
    
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
    }
    
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
    
    function merchantRechargeAction()
    {
        $params = $this->_request->getParams();
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Merchants List", "Adjust Balance|".$params['user_id']));
        
        $user_model = new Databases_Joins_GetUserInfo();
        $this->view->user = $user_model->GetUserInfo($params['user_id']);
        
        $this->view->title = "Adjust balance for merchant";
    }
    
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
        $this->view->title = "Import BPay CSV Files Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "BPay Import"));
        
        if ($_FILES["csvf"]["error"] > 0)
        {
            $this->view->notice = $_FILES["csvf"]["error"];
        }else{
            if('text/csv' != $_FILES["csvf"]["type"])
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
                $logs_financial->AddLog();
            }
        }
        
        $this->_redirect("/admin/bpay-import/result/1");
    }
    
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
            $this->view->users_feed = $users_feed->GetFeedInfo($params['user_id']);
            
            $feed_dictionary = new Databases_Tables_FeedDictionary();
            $dump_feed_dictionary = $feed_dictionary->DumpAll(1);
            
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
        $users_feed_model->cost_protection = $params['cost_protection'];
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
        
        $this->view->title = "Admin Order Import Preview";
        $menu_model = new Algorithms_Core_Menu;
        $this->view->navigation = $menu_model->GetNavigation(array("Dashboard", "Admin Import Order"));
        

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
                                $getorders_model->operator_id = $_SESSION["Zend_Auth"]["storage"]->user_id;
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
        $logs_financial = new Databases_Tables_LogsFinancial();
        $plugin_model = new Algorithms_Extensions_Plugin();
        $order_service_model    =   new Algorithms_Core_OrderService();
        $user_model             =   new Databases_Joins_GetUserInfo();
        $ip = $plugin_model->GetIp();
        $notice = "S1"; //success
        
        if(count($params['supplier_sku']))
        {
            $group_instance_balance_array = array();
            $merchant_ref_pool = array();
            $i=0;
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
                    
                    //add by angus 2013-04-26
                    $getorders_model->item_amount   =   $order_amount;
                    $user_info   =   $user_model->GetUserInfo($user_id);
                    $merchant_email =   $user_info['email'];
                    $order_service_model->crazySalesOrderType['RetailerAccountEmail']   =   $merchant_email;
                    $order_service_model->crazySalesOrderType['PaymentTypeID']          =   1; //PaymentTypeID is unknown,need to check the PaymentTypeID
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
                    $logs_financial->trans_id = $place_order_return['logs_orders_id'];

                    $logs_financial->AddLog();
                }else{
                    $notice = "E2";
                }
            }
        }else{
            $notice = "E1";
        }
        
        $this->_redirect("/admin/admin-import-order/notice/".$notice);
    }

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
    
}

