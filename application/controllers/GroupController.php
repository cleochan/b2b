<?php

class GroupController extends Zend_Controller_Action
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
		$this->view->title = "Group Management";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetGroupMenu($this->getRequest()->getActionName());
		
		$grp = new Grp();
		$grp_list = $grp -> MakeList();
		
		$this->view->list = $grp_list;
    }
    
    function addAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Add Group";
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetGroupMenu($this->getRequest()->getActionName());

		//create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new GroupForm();
		$form->gname->setRequired(True);
		$form->submit->setLabel('Create');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start
				
				$check_group_string = new Grp();
				
				//group exist
				$check_group = $check_group_string -> fetchRow('gname = "'.$form->getValue('gname').'"');
				if($check_group['id'])
				{
					$this->view->notice="The group is existed.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					$users_model = new Users();
					
					$group_string = $users_model -> MakeString($form->getValue('members'));
					
					//insert to db
					$row = $check_group_string->createRow();
					
					$row->gname = $form->getValue('gname');
					$row->members = $group_string;
					
					$row->save();
					
					$this->_redirect('group/index');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!$formData['gname'])
				{
					$this->view->notice="Group name is required.";
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
    
    function editAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Edit Group";
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetGroupMenu($this->getRequest()->getActionName());
		
		$form = new GroupForm();
		$grp = new Grp();

		$theid = $params['id'];
		$grp_string = $grp->fetchRow('id="'.$theid.'"');
		$grp_array = explode("|", $grp_string['members']);
		//set checked options start
		if(!empty($grp_array))
		{
			$grp_ready = array();
			foreach($grp_array as $grp_val)
			{
				$k = explode("@", $grp_val);
				$grp_ready[] = $k[0];
			}
		}else
		{
			$grp_ready = array();
		}
		//set checked options end
				
		$form->submit->setLabel('Update');
		$this->view->form = $form;
		$this->view->data = $grp_string;
			
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
	
				if(!$error)
				{
					//update to db
					$grp = new Grp();
				
					$row = $grp->fetchRow('id = "'.$form->getValue('id').'"');
					
					if(!empty($row))
					{
						$users_model = new Users();
						$group_string = $users_model -> MakeString($form->getValue('members'));
						$row->members = $group_string;
						$row->save();
					}
					
					//redirect
					$this->_redirect('group/index');
				}
			}
		}else
		{
			if($params['id'])
			{
				//set checked options start
				$theid = $params['id'];
				$grp_string = $grp->fetchRow('id="'.$theid.'"');
				$grp_array = explode("|", $grp_string['members']);
				if(!empty($grp_array))
				{
					$grp_ready = array();
					foreach($grp_array as $grp_val)
					{
						$k = explode("@", $grp_val);
						$grp_ready[] = $k[0];
					}
				}else
				{
					$grp_ready = array();
				}
				//set checked options end
				
				$form->submit->setLabel('Update');
				$this->view->form = $form;
				$this->view->data = $grp_string;
				
				$result = $grp_string->toArray();
				$result['members'] = $grp_ready;
				$form->populate($result);
			}
		}
    }
	
	function deleteAction()
	{
		$params = $this->_request->getParams();
		$this->view->title = "Delete Group";
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetGroupMenu($this->getRequest()->getActionName());
		
		$grp = new Grp();
		
		if($this->_request->isPost())
		{
			$id = $params['id'];
			$del = $params['del'];
			if('Yes' == $del && 0 < $id)
			{
				$grp->delete('id="'.$id.'"');
			}
			$this->_redirect('group/index');
		}else
		{
			if($params['id'])
			{
				$id = $params['id'];
				$this->view->data = $grp->fetchRow("id='".$id."'");
			}
		}
	}

}
