<?php

class IndexController extends Zend_Controller_Action
{
	
    function preDispatch()
    {
        //$this->_redirect('/merchant');
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/merchant');
	exit();
    }
}

