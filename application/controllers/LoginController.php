<?php

class LoginController extends Zend_Controller_Action
{
    public $params_val;
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
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
            $email = $f->filter($this->_request->getPost('email'));
            $password = $f->filter($this->_request->getPost('password'));
            if (empty($username)) {
                $this->view->msg = "Email/UserName is required.";
            } else {
                Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
                $db = Zend_Registry::get('db');
                $authAdapter = new Zend_Auth_Adapter_DbTable($db);
				
                $authAdapter->setTableName('users');
                $authAdapter->setIdentityColumn('email');
                $authAdapter->setCredentialColumn('password');

                $authAdapter->setIdentity($email);
                $authAdapter->setCredential(md5($password));

                $auth_back = Zend_Auth::getInstance();
                $result = $auth_back->authenticate($authAdapter);

                if ($result->isValid()) {				
                    $data = $authAdapter->getResultRowObject(null, 'password');
                    if($data->user_status) //user_status=0
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
                        }elseif(1 == $data->user_type){ //admin
                            $this->_redirect('/admin');
                        }elseif(2 == $data->user_type){ //merchant
                            $this->_redirect('/index');
                        }
                    }else{
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
		
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/login?url='.$params['url']);
    }
}

