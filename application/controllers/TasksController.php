<?php

class TasksController extends Zend_Controller_Action
{
  
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if(!$auth->hasIdentity() || !$users->IsValid())
            { 
                $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
            }

            //get system title
            $get_title = new Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");

            //make top menu
            $menu = new Menu();
            $this->view->top_menu = $menu -> GetTopMenu($this->getRequest()->getControllerName());
    }
	
    function indexAction()
    {
        $this->view->title = "My Tasks";
        $params = $this->_request->getParams();
        
        //check list mode start
        if(!$_SESSION["Zend_Auth"]["storage"]->default_list)
        {
            $this->_redirect('index/index/type/'.$params['type']);
        }
        //check list mode finished
        
        $this->view->type = $params['type'];
        $menu = new Menu();
        $this->view->menu = $menu -> GetTicketMenu($params['type']); 
        //create user list

        $users = new Users();
        $this->view->get_user_list = $users -> MakeListForDropDown();
        
        $projects = new Projects();
        $this->view->get_project_list = $projects -> GetArray();
 
        //$_SESSION['search_ticket_users_current']
        if(!$_SESSION['search_ticket_users_current'])
        {
                $_SESSION['search_ticket_users_current'] = $_SESSION["Zend_Auth"]["storage"]->id;
        }

        $tickets = new Tasks();
        $tickets -> request = $params['type'];
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $tickets -> page = $current_page;

        $list = $tickets -> PushListData();
        $this->view->list = $list;

        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;

        $tickets -> page = $this->view->older;
        $list = $tickets -> PushListData();
        //print_r($list);die;
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $tickets -> page = $this->view->newer;
                $list = $tickets -> PushListData();
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }

        $this->view->p_type = $params['type'];
        $this->view->p_page = $params['page'];

    }
    
    function switchModeAction()
    {
        $params = $this->_request->getParams();
        
        $users = new Users();
        $users->SwitchMode($params['mode']);
        
        $this->_redirect('index/index/type/'.$params['type']);
    }
    
    function moveUpAction()
    {
        $params = $this->_request->getParams();
        
        $tickets_users = new TicketsUsers();
        $tickets_users ->MoveUp($params['id']);
        
        $this->_redirect($params['url']);
        die;
    }
    
    function moveDownAction()
    {
        $params = $this->_request->getParams();
        
        $tickets_users = new TicketsUsers();
        $tickets_users ->MoveDown($params['id']);
        
        $this->_redirect($params['url']);
        die;
    }
}

