<?php

class IndexController extends Zend_Controller_Action
{
	
    function preDispatch()
    {
        if($_SERVER['HTTP_HOST']=='b2b.crazysales.com.au'){
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/merchant');
            exit();
        }else{
            $this->_redirect('/merchant');
        }it();
    }
}

