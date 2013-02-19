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
                $this->_redirect('/index');
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
        //$params = $this->_request->getParams();
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
}

