<?php

class ConfigController extends Zend_Controller_Action
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
		$this->view->title = "System Configuration";

		$form = new ConfigForm();
		$form->submit->setLabel('Update');
		$this->view->form = $form;

		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
								
				$pa = new Params();
                
                $update_array = array(
                    "system_title" => $form->getValue('sys_title'),
                    "system_version" => $form->getValue('sys_ver'),
                    "system_path" => $form->getValue('sys_path'),
                    "smtp_server" => $form->getValue('smtp_server'),
                    "smtp_account" => $form->getValue('smtp_account'),
                    "smtp_pw" => $form->getValue('smtp_pw'),
                    "sender_account" => $form->getValue('sender_account'),
                    "sender_name" => $form->getValue('sender_name')
                );
                
                foreach($update_array as $update_key => $update_val)
                {
                    $row = $pa->fetchRow("ckey = '".$update_key."'");
                    $row->cval = $update_val;			
                    $row->save();
                }
				
				$this->_redirect('/config/index');
			}else{
				$this->view->notice="Some information are inValid";
				$form->populate($formData);
			}
		}else
		{
			$pa_info = new Params();
			$info['sys_title'] = $pa_info -> GetVal("system_title");
			$info['sys_ver'] = $pa_info -> GetVal("system_version");
			$info['sys_path'] = $pa_info -> GetVal("system_path");
			$info['smtp_server'] = $pa_info -> GetVal("smtp_server");
			$info['smtp_account'] = $pa_info -> GetVal("smtp_account");
			$info['smtp_pw'] = $pa_info -> GetVal("smtp_pw");
			$info['sender_account'] = $pa_info -> GetVal("sender_account");
			$info['sender_name'] = $pa_info -> GetVal("sender_name");
			$form->populate($info);
			
			if('success' == $params['act'])
			{
				$this->view->notice="Update Successfully.";
			}
		}
    }
}

























