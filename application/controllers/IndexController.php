<?php

class IndexController extends Zend_Controller_Action
{
    function init()
    {
        $this->db = Zend_Registry::get("db");
    }
    
    function preDispatch()
    {
        $this->_redirect('/merchant');
    }
}

