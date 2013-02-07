<?php

class CategoryController extends Zend_Controller_Action
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
		$this->view->title = "Category Management";
		$params = $this->_request->getParams();
        
		$kpi_workbook = new Category();
		$top_level = $kpi_workbook -> MakeTopLevel();
		$this->view->top_level = $top_level;
		
        if($params['wb'])
        {
            $this->view->wb = $params['wb'];
            $this->view->tree = $kpi_workbook -> BuildTree($params['wb']);
        }
        
        switch($params['error'])
        {
            case 1:
                $note = "You can't delete the element with active child.";
                break;
            case 2:
                $note = "You can't restore the element with the deleted parent.";
                break;
            default :
                $note = "";
                break;
        }
        
        $this->view->note = $note;
        
        //deleted list
        $this->view->delete_list = $kpi_workbook ->DeletedList();
    }
    
    function editAction()
    {
        $params = $this->_request->getParams();
        
        $behavior = $params['behavior'];
        $parent_id = $params['parent_id'];
        $self_id = $params['self_id'];
        $self_name = trim($params['self_name']);
        $child_name = trim($params['child_name']);
        
        $workbook_table = new Category();
        
        switch($behavior)
        {
            case 'add':
                if($self_id)
                {
                    $row = $workbook_table->fetchRow('id = "'.$self_id.'"');
					//switch parent
                    $row->parent_id = $parent_id;
					//update self name
                    $row->cname = $self_name;
					$row->save(); 
                }
                
                //add child
                if($child_name)
                {
                    $row = $workbook_table->createRow();
					$row->parent_id = $self_id;
					$row->status = 1;
					$row->cname = $child_name;
					$row->save();
                }
                
                break;
            case 'del':
                if($self_id)
                {
                    if($workbook_table->fetchRow('parent_id = "'.$self_id.'" and status="1"'))
                    {
                        $error = 1;
                    }else
                    {
                        $row = $workbook_table->fetchRow('id = "'.$self_id.'" and status="1"');
                        $row->status = 0;
                        $row->del_time = date("Y-m-d H:i:s");
                        $row->del_who = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
                        $row->save(); 
                    }
                }
                break;
            case 'restore':
                if($self_id)
                {
                    if($workbook_table->fetchRow('id = "'.$parent_id.'" and status="0"'))
                    {
                        $error = 2;
                    }else
                    {
                        $row = $workbook_table->fetchRow('id = "'.$self_id.'" and status="0"');
                        $row->status = 1;
                        $row->del_time = "";
                        $row->del_who = "";
                        $row->save(); 
                    }
                }
                break;
            default :
                
                break;
        }
                
        //redirect
		$this->_redirect('category/index/wb/'.$params['wb'].'/error/'.$error);
    }

}
