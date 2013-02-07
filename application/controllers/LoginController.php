<?php

class LoginController extends Zend_Controller_Action
{
	public $params_val;
	
	function init()
	{
		$this->db = Zend_Registry::get("db");
		//$this->auth = Zend_Registry::get("auth");	
	}
	
	function preDispatch()
	{
		//disable layout for Login page
		$this->_helper->layout->disableLayout();
		
		//get system title
		$get_title = new Params();
		$this->view->system_title = $get_title -> GetVal("system_title");
	}
	
    function indexAction()
    {
		$this->view->title = "Login"; // title of this page
		$params = $this->_request->getParams();
		$this->view->url = $params['url'];
		if ($this->_request->isPost()) {
            Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getPost('username'));
			$password = $f->filter($this->_request->getPost('passwd'));
			if (empty($username)) {
				$this->view->msg = "Username is required.";
			} else {
                Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
				$db = Zend_Registry::get('db');
				$authAdapter = new Zend_Auth_Adapter_DbTable($db);
				
				$authAdapter->setTableName('users');
				$authAdapter->setIdentityColumn('username');
				$authAdapter->setCredentialColumn('passwd');

				$authAdapter->setIdentity($username);
				$authAdapter->setCredential(md5($password));

				$auth_back = Zend_Auth::getInstance();
				$result = $auth_back->authenticate($authAdapter);

				if ($result->isValid()) {				
					$data = $authAdapter->getResultRowObject(null, 'passwd');
					if($data->status) //status=0
					{
                        $auth_back->getStorage()->write($data);
						require_once('Zend/Session/Namespace.php');
						//Zend_Loader::loadClass('Zend_Session_Namespace');
						
						$session = new Zend_Session_Namespace('Zend_Auth');
						$session->setExpirationSeconds(3600*24*365); //a year
						Zend_Session::rememberMe(3600*24*365);
						
						if($this->_request->getPost('url'))
						{
							$this->_redirect($this->_request->getPost('url'));
						}else
						{
							$this->_redirect('/index/index/type/2');
						}
					}else
					{
						Zend_Auth::getInstance()->clearIdentity();
						$this->view->msg = "Login failed";
					}
				} else {
					$this->view->msg = "Login failed";
				}	
			}
		}
    }
	
	function logoutAction()
	{
		$params = $this->_request->getParams();
		
        unset($_SESSION['search_ticket_users_current']);
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/login?url='.$params['url']);
	}	


}

