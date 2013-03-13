<?php

class PluginController extends Zend_Controller_Action
{
	
	function init(){
		$this->db = Zend_Registry::get("db");
		
	}
    
    function preDispatch()
	{
		//disable layout for Login page
		$this->_helper->layout->disableLayout();
	}
	
    function indexAction()
    {
        $a = new Tables_Users();
        
        $a->DumpData();
        
        die;
    }
    
    function testAction()
    {
        $data = array();
        $data['email'] = "mark560@gmail.com";
        $data['password'] = "212121";
        $data['user_type'] = 3;
        $data['user_status'] = 4;
        
        $data_xml = Algorithms_Core_Api::Array2Xml($data);
        $target = "http://demo.local.b2b/plugin/test-terminal";
        
        try{
            Algorithms_Core_Api::PostXml($data_xml, $target);
        }  catch (Zend_Exception $exp){
            var_dump($exp->getMessage());
        }
        
        die;
    }
    
    function testTerminalAction()
    {
        $params = file_get_contents('php://input');
        $array = Algorithms_Core_Api::Xml2Array($params);
        
        $db = new Databases_Tables_Users;
        $db->email = $array['email'];
        $db->password = $array['password'];
        $db->user_type = $array['user_type'];
        $db->user_status = $array['user_status'];
        $db->AddUser();
        
        die;
    }
    
    function t1Action()
    {
        Algorithms_Extensions_Plugin::FormatArray($this->getRequest()->getActionName());
        die;
    }
    
    function phpinfoAction()
    {
        phpinfo();
        die;
    }
    
    function stringTestAction()
    {
        $string = "[1|OK][2|NO]";
        
        $length = strlen($string);
        
        $result_1 = array();
        
        while($length)
        {
            $from = strpos($string, "[");
            $to = strpos($string, "]");
            
            if(FALSE !== $from && FALSE !== $to && $from < $to)
            {
                $result_1[] = substr($string, $from+1, $to-$from-1);

                $string = substr($string, $to+1);

                $length = strlen($string);
            }else{
                $length = 0; // Exit
            }
        }
        
        //Algorithms_Extensions_Plugin::FormatArray($result_1);
        
        die;
    }
    
    function feedTestAction()
    {
        $model = new Algorithms_Core_Feed();
        $model->user_id_array = array(4);
        $result = $model->Publish();
        echo $result;
        die;
    }
    
    function testXmlAction()
    {
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
   <common>
      <version>1.0</version>
      <action>PlaceOrder</action>
      <merchantid>1</merchantid>
      <publickey>l23khu23KJH34kjl</publickey>
   </common>
   <params>
      <b2b_order_id>4</b2b_order_id>
      <merchant_ref>567708</merchant_ref>
      <merchant_id>3</merchant_id>
      <supplier_sku>SKU 835</supplier_sku>
      <merchant_sku>LS3228</merchant_sku>
      <quantity>2</quantity>
      <order_amount>3756.00</order_amount>
      <buyer_first_name>Testtt</buyer_first_name>
      <buyer_last_name>Hello</buyer_last_name>
      <buyer_company>BBB</buyer_company>
      <buyer_address_1>1 Villa Rd</buyer_address_1>
      <buyer_address_2>aaa</buyer_address_2>
      <buyer_suburb>Springvale</buyer_suburb>
      <buyer_state>VIC</buyer_state>
      <buyer_postcode>3179</buyer_postcode>
      <buyer_country>AU</buyer_country>
      <buyer_phone>aaa</buyer_phone>
      <buyer_fax>aaa</buyer_fax>
      <shipping_method>standard</shipping_method>
      <shipping_instruction>aa</shipping_instruction>
      <serial_no>aa</serial_no>
      <comments>aa</comments>
   </params>
   <response>
      <order_status>2</order_status>
      <reject_reason>DDDDD</reject_reason>
      <tracking_number>EEEEEE</tracking_number>
   </response>
</root>
XML;
        
        $api_model = new Algorithms_Core_Api();
        $xml_array = $api_model->Xml2Array($xml);
        //Algorithms_Extensions_Plugin::FormatArray($xml_array);die;
        $api_model->api_target = 1;
        $api_model->api_type = 2;
        $api_model->original_xml_array = $xml_array;
        $api_model->Push();

        die;
    }
    
    function apiTestAction()
    {
        $api_model = new Algorithms_Core_Api();
        
        $api_model->api_target = 1;
        $api_model->api_type = 1;
        $api_model->original_xml_array = array("a","b","c");
        $api_model->Push();
        die;
    }
    
    function numTestAction()
    {
        echo number_format(-1000, 2);die;
    }
}
