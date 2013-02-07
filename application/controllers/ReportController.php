<?php

class ReportController extends Zend_Controller_Action
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
			$this->_redirect('/login/logout?url='.$_SERVER['REQUEST_URI']);
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
		$this->view->title = "Report";
		
		$report = new Report();
		$list = $report -> PushWeeklyList();
		$this->view->list = $list;
    }
    
    function viewAction()
    {
    	$params = $this->_request->getParams();
    	
    	$report = new Report();
    	$list = $report -> GetData($params['id']);
    	$this->view->list1 = $list[0];
    	$this->view->list2 = $list[1];
    	$this->view->list3 = $list[2];
    	
    	$dinfo = $report->DateStartEndCalculation($params['id']);
    	$this->view->title = "IT Weekly Task List ( ".substr($dinfo['week_start'],0,10)." to ".substr($dinfo['week_end'],0,10)." )";
    }

}
