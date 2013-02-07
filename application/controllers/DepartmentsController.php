<?php

class DepartmentsController extends Zend_Controller_Action
{
	
	function init(){
		$this->db = Zend_Registry::get("db");
		
	}
	
	function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		$users = new Users();
		if(!$auth->hasIdentity() || !$users->IsValid())
		{
			$this->_redirect('/login/logout');
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
		$this->view->title = "Departments Management";
		$params = $this->_request->getParams();

		$departments = new Departments();
		$this->view->departments = $departments -> GetArray();
		
		if('ud' == $params['act'])
		{
			if($params['nname'])
			{
				//check exist
				$departments = new Departments();
				$department = $departments -> fetchRow('name = "'.$params['nname'].'"');
				if($department['id'])
				{
					$this->view->notice = "This department name is existed.";
					$error = 1;
				}else
				{
					$data = array("name" => $params['nname']);
					$departments -> insert($data);
				}
			}
			
			if(!$error)
			{
				if($params['pname'])
				{
					foreach($params['pname'] as $pname_key => $pname_val)
					{
						$set = array("name" => $pname_val);
						$where = $this->db->quoteInto('id = ?', $pname_key);
						$this->db->update('departments', $set, $where);
					}
				}
				
				$this->_redirect('/departments/index');
			}
		}
    }

}
