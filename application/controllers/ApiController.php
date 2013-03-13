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
        //Algorithms_Extensions_Plugin::FormatArray($xml_array);die;
        if(is_array($xml_array))
        {
            $api_types_model = new Databases_Tables_ApiTypes();
            $api_types_name = $xml_array['common']['action'];
            $api_types_model->api_types_name = $api_types_name;
            $action_id = $api_types_model->GetTypeId();
            
            if($action_id)
            {
                switch ($action_id)
                {
                    case 1: //GetProductList
                        
                        break;
                    case 2: //PlaceOrder
                        $tmpa = new Databases_Tables_Tmpa();
        $data = array("contents" => "<<<".$xml_array['params']['b2b_order_id'].">>>");
        $tmpa->insert($data);
                        $logs_orders_model = new Databases_Tables_LogsOrders();
                        $logs_orders_model->logs_orders_id = $xml_array['params']['b2b_order_id'];
                        $logs_orders_model->order_status = $xml_array['response']['order_status'];
                        if($xml_array['response']['reject_reason'])
                        {
                            $logs_orders_model->api_response = $xml_array['response']['reject_reason'];
                        }
                        if($xml_array['response']['tracking_number'])
                        {
                            $logs_orders_model->tracking_number = $xml_array['response']['tracking_number'];
                        }
                        $logs_orders_model->GetResponseToUpdateOrder(1); //Update status
                        break;
                    default :
                        break;
                }
            }
        }
        
        die;
    }
}

