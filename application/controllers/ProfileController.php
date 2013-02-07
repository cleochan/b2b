<?php

class ProfileController extends Zend_Controller_Action
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
		$params = $this->_request->getParams();
		$this->view->title = "My Profile";

		$form = new ProfileForm();
		$form->submit->setLabel('Update');
		$this->view->form = $form;

		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				//file uploads
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start
								
				//1, realname repeated
				if(trim($form->getValue('realname')))
				{
					$users = new Users();
					$users_array = $users -> select();
					$users_array -> where('id <> ?', $form->getValue('id'));
					$users_array -> where('realname like ?', trim($form->getValue('realname')));
					$users_result = $users -> fetchRow($users_array);
					
					if($users_result['id'])
					{
						$this->view->notice="This name has been taken, please choose another one.";
						$form->populate($formData);
						$error = 1;
					}
				}else
				{
					$this->view->notice="Real Name is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//2, password differences
				if($form->getValue('passwd') || $form->getValue('passwd_r'))
				{
					if($form->getValue('passwd') != $form->getValue('passwd_r'))
					{
						$this->view->notice="The passwords you typed twice were different.";
						$form->populate($formData);
						$error = 1;
					}
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					$users = new Users();
				
					$row = $users->fetchRow('id = "'.$form->getValue('id').'"');

					if($form->getValue('passwd'))
					{
						$row->passwd = md5($form->getValue('passwd'));
					}
					$row->realname = trim($form->getValue('realname'));
					$row->email = $form->getValue('email');
					$row->skype = $form->getValue('skype');
					$row->new_alert = $form->getValue('new_alert');
					$row->reminder = $form->getValue('reminder');
					$row->default_list = $form->getValue('default_list');
					$_SESSION["Zend_Auth"]["storage"]->new_alert = $form->getValue('new_alert');
				
					$row->save();
					$this->_redirect('/profile/index/act/success');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start

				if(!$formData['realname'])
				{
					$this->view->notice="Real Name is required.";
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
		}else
		{
			$users = new Users();
			$user = $users->fetchRow('id="'.$_SESSION["Zend_Auth"]["storage"]->id.'"');
			$form->populate($user->toArray());

			if('success' == $params['act'])
			{
				$this->view->notice="Update Successfully.";
			}
		}
    }
}

























