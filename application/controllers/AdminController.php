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
                    //update to db
                    $check_user_string->user_id = $form->getValue('user_id');
                    $check_user_string->email = $form->getValue('user_id');
                    if(!$password_not_changed)
                    {
                        $check_user_string->password = $form->getValue('password');
                    }
                    $check_user_string->user_status = $form->getValue('user_status');
                    $check_user_string->company = $form->getValue('company');
                    $check_user_string->contact_name = $form->getValue('contact_name');
                    $check_user_string->contact_phone = $form->getValue('contact_phone');
                    $check_user_string->credit = $form->getValue('credit');
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
        
        $logs_orders_model = new Databases_Tables_LogsOrders();
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
        if($params['order_id'])
        {
            $logs_orders_model->order_id = $params['order_id'];
            $this->view->order_id = $params['order_id'];
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
        $users_feed_model->feed_qualifier = $params['feed_qualifier'];
        $users_feed_model->feed_category = $product_categories_model->CheckedArray($_SESSION['b2b']['merchant_feed_generation'][$params['user_id']]);
        $users_feed_model->sku_included = $params['sku_included'];
        $users_feed_model->sku_excluded = $params['sku_excluded'];
        $users_feed_model->stock = $params['stock'];
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
}

