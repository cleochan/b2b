<?php

class ApiController extends Zend_Controller_Action
{
    function indexAction()
    {
        echo "Invalid Action";
        die;
    }
    
    function portAAction() // Port for B2B transaction with main site
    {
        $params =  file_get_contents('php://input');
        
        $tmpa = new Databases_Tables_Tmpa();
        $data = array("contents" => $params);
        $tmpa->insert($data);
        
        die;
    }
}

