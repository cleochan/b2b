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
}

