<?php

class ApiController extends Zend_Controller_Action
{
    function indexAction()
    {
        echo "Invalid Action";
        die;
    }
    
    function PortAAction()
    {
        $params = $this->_request->getParams();
        
        
    }
}

