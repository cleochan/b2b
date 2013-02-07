<?php

class UsersController extends Zend_Controller_Action
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
		$this->view->title = "Users Management";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetUsersMenu($this->getRequest()->getActionName());
		
		$users = new Users();
		$user_list = $users -> MakeList();
		
		$this->view->list = $user_list;
    }
    
    function addAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Add User";
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetUsersMenu($this->getRequest()->getActionName());

		//create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new UserForm();
		$form->submit->setLabel('Create User');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start

				//new users
				$check_user_string = new Users;
				
				//password check
				if($form->getValue('passwd') || $form->getValue('passwd_r'))
				{
					if($form->getValue('passwd') != $form->getValue('passwd_r'))
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
												
				//supervisor
				if(trim($form->getValue('supervisor')))
				{
					$check_user_result = $check_user_string -> MakeString($form->getValue('supervisor'), 1);
					
					if(is_array($check_user_result)) //error
					{
						if('error1' == $check_user_result[0])
						{
							$this->view->notice="System can't find the supervisor: ".$check_user_result[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result[0])
						{
							$this->view->notice="You didn't choose a valid master.";
							$form->populate($formData);
						}						

						$error = 1;
					}
				}
				
				//username exist
				$check_username = $check_user_string -> fetchRow('username = "'.$form->getValue('username').'"');
				if($check_username['id'])
				{
					$this->view->notice="Username is existed.";
					$form->populate($formData);
					$error = 1;
				}
				
				//realname exist
				$check_realname = $check_user_string -> fetchRow('realname = "'.$form->getValue('realname').'"');
				if($check_realname['id'])
				{
					$this->view->notice="Real Name is existed.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					//insert to db
					$users = new Users();
				
					$row = $users->createRow();
					
					$row->username = $form->getValue('username');
					$row->passwd = md5($form->getValue('passwd'));
					$row->department = $form->getValue('department');
					$row->realname = $form->getValue('realname');
					$row->first_name = $form->getValue('first_name');
					$row->last_name = $form->getValue('last_name');
					$row->team_title = $form->getValue('team_title');
					$row->skype = $form->getValue('skype');
					$row->supervisor = $check_user_result;
					$row->status = $form->getValue('status');
					$row->level_view_tickets = $form->getValue('level_view_tickets');
					$row->level_mgt = $form->getValue('level_mgt');
					$row->email = $form->getValue('email');
					$row->new_alert = $form->getValue('new_alert');
					$row->reminder = $form->getValue('reminder');
					$row->default_list = $form->getValue('default_list');
				
					$row->save();
					$this->_redirect('users/index');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!$formData['username'])
				{
					$this->view->notice="Username is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				if(!trim($formData['realname']))
				{
					$this->view->notice="UserName is required.";
					$form->populate($formData);
					$error = 1;
				}
								
				if(!trim($formData['department']))
				{
					$this->view->notice="Department is required.";
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
		$this->view->title = "Edit User";
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetUsersMenu($this->getRequest()->getActionName());

		//create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new UserForm();
		$form->submit->setLabel('Update');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start

				//new users
				$check_user_string = new Users;
				
				//password check
				if($form->getValue('passwd') || $form->getValue('passwd_r'))
				{
					if($form->getValue('passwd') != $form->getValue('passwd_r'))
					{
						$this->view->notice="The passwords you typed twice were different.";
						$form->populate($formData);
						$error = 1;
					}
				}
												
				//supervisor
				if(trim($form->getValue('supervisor')))
				{
					$check_user_result = $check_user_string -> MakeString($form->getValue('supervisor'), 1);
					
					if(is_array($check_user_result)) //error
					{
						if('error1' == $check_user_result[0])
						{
							$this->view->notice="System can't find the supervisor: ".$check_user_result[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result[0])
						{
							$this->view->notice="You didn't choose a valid master.";
							$form->populate($formData);
						}						

						$error = 1;
					}
				}
				
				//realname exist
				$check_realname = $check_user_string -> select();
				$check_realname -> where('id != ?', $form->getValue('id'));
				$check_realname -> where('realname = ?', $form->getValue('realname'));
				$check_realname_result = $check_user_string -> fetchRow($check_realname);
				if($check_realname_result['id'])
				{
					$this->view->notice="Real Name is existed.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					//insert to db
					$users = new Users();
				
					$row = $users->fetchRow('id = "'.$form->getValue('id').'"');
					
					if($form->getValue('passwd'))
					{
						$row->passwd = md5($form->getValue('passwd'));
					}
					$row->department = $form->getValue('department');
					$row->realname = $form->getValue('realname');
					$row->first_name = $form->getValue('first_name');
					$row->last_name = $form->getValue('last_name');
					$row->team_title = $form->getValue('team_title');
					$row->skype = $form->getValue('skype');
					$row->supervisor = $check_user_result;
					$row->status = $form->getValue('status');
					$row->level_view_tickets = $form->getValue('level_view_tickets');
					$row->level_mgt = $form->getValue('level_mgt');
					$row->email = $form->getValue('email');
					$row->new_alert = $form->getValue('new_alert');
					$row->reminder = $form->getValue('reminder');
                    $row->default_list = $form->getValue('default_list');
				
					$row->save();
					//unset session
					$theid = $form->getValue('id');
					unset($_SESSION['user_contents'][$theid]);
					//redirect
					$this->_redirect('users/index');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!trim($formData['realname']))
				{
					$this->view->notice="UserName is required.";
					$form->populate($formData);
					$error = 1;
				}
								
				if(!trim($formData['department']))
				{
					$this->view->notice="Department is required.";
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
			if($params['id'])
			{
				$theid = $params['id'];
				$user = $users->fetchRow('id="'.$params['id'].'"');
				$user->supervisor = $users -> GetNameString($user->supervisor);
				$form->populate($user->toArray());
				$this->view->data = $user;
				$_SESSION['user_contents'][$theid] = $user;
			}
		}
    }
    
    function divisionAction()
    {
		$this->view->title = "Staff Division";
		$params = $this->_request->getParams();
        
        $menu = new Menu();
		$this->view->menu = $menu -> GetUsersMenu($this->getRequest()->getActionName());
        
        $users = new Users();
        $this->view->user_id = $params['id'];
        $this->view->user_name = $users->GetRealName($params['id']);
        
        $kpi_workbook_user = new KpiWorkbookUser();
        $this->view->user_tickets_checked = $kpi_workbook_user ->GetWork($params['id']);
        
		$kpi_workbook = new KpiWorkbook();
		$top_level = $kpi_workbook -> MakeTopLevel();
		$this->view->top_level = $top_level;
		
        if($params['wb'])
        {
            $this->view->wb = $params['wb'];
            $this->view->tree = $kpi_workbook -> BuildTree($params['wb']);
        }
        
        if($params['success'])
        {
            $this->view->note = "Saved successfully.";
        }
    }
    
    function divisionSubmitAction()
    {
        $params = $this->_request->getParams();
        
        if($params['user_id'])
        {
            $checked = array();
            $total = array();
            
            foreach($params['t'] as $t_key => $t_val)
            {
                $checked[] = $t_key;
            }
            
            foreach($params['a'] as $a_val)
            {
                $total[] = $a_val;
            }
            
            $diff = array_diff($total, $checked);
            
            $kpi_workbook_user = new KpiWorkbookUser();
            
            //step 1: add for $checked
            if(!empty($checked))
            {
                foreach($checked as $checked_val)
                {
                    $find = $kpi_workbook_user -> select();
                    $find -> where("user_id = ?", $params['user_id']);
                    $find -> where("workbook_id = ?", $checked_val);
                    $find_result = $kpi_workbook_user -> fetchRow($find);
                    
                    if(empty($find_result)) //insert a new row
                    {
                        $ac = $kpi_workbook_user ->createRow();
                        $ac -> user_id = $params['user_id'];
                        $ac -> workbook_id = $checked_val;
                        $ac -> save();
                    }
                }
            }
            
            //step 2: remove for $diff
            if(!empty($diff))
            {
                foreach($diff as $diff_val)
                {
                    $find = $kpi_workbook_user -> select();
                    $find -> where("user_id = ?", $params['user_id']);
                    $find -> where("workbook_id = ?", $diff_val);
                    $find_result = $kpi_workbook_user -> fetchRow($find);
                    
                    if(!empty($find_result)) //delete a new row
                    {
                        $ac = $kpi_workbook_user -> delete("user_id='".$find_result['user_id']."' and workbook_id='".$find_result['workbook_id']."'");
                    }
                }
            }
            //redirect
            $this->_redirect("/users/division/id/".$params['user_id']."/wb/".$params['wb']."/success/1");
            
        }else
        {
            echo "User ID Error.";die;
        }
        
        die;
    }

}
