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
        
        //Original Log
        $tmpa = new Databases_Tables_Tmpa();
        $data = array("contents" => $params);
        $tmpa->insert($data);
        
        //exchange xml to array
        $api_model = new Algorithms_Core_Api();
        $xml_array = $api_model->Xml2Array($params);
        
        if(is_array($xml_array))
        {
            $api_types_model = new Databases_Tables_ApiTypes();
            $api_types_name = $xml_array['root']['common']['action'];
            $api_types_model->api_types_name = $api_types_name;
            $action_id = $api_types_model->GetTypeId();
            
            if($action_id)
            {
                switch ($action_id)
                {
                    case 1: //GetProductList
                        
                        break;
                    case 2: //PlaceOrder
                        
                        break;
                    default :
                        break;
                }
            }
        }
        
        
        die;
    }
}

