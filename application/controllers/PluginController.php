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
$xml_data = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
   <common>
      <version>1.0</version>
      <action>GetProductList</action>
      <publickey>l23khu23KJH34kjl</publickey>
   </common>
   <params>
      <product_request_qty_per_page>100</product_request_qty_per_page>
      <product_request_page_amount>500</product_request_page_amount>
      <product_request_page>500</product_request_page>
   </params>
   <response>
      <product_1>
         <supplier_sku>A1</supplier_sku>
         <brand_id>1</brand_id>
         <brand_name>B1</brand_name>
         <mpn>C1</mpn>
         <stock>2</stock>
         <offer_price>100.00</offer_price>
         <cost_price>60.00</cost_price>
         <product_name>D1</product_name>
         <features1>E1</features1>
         <features2>F1</features2>
         <features3>G1</features3>
         <features4>H1</features4>
         <features5>I1</features5>
         <product_details>J1</product_details>
         <specification>K1</specification>
         <dimension>L1</dimension>
         <colour>M1</colour>
         <size>N1</size>
         <factory_url>O1</factory_url>
         <package_content>P1</package_content>
         <warranty>Q1</warranty>
         <category_id>3</category_id>
         <category_name>R1</category_name>
         <weight>S1</weight>
         <image_url_1>T1</image_url_1>
         <image_url_2>U1</image_url_2>
         <image_url_3>V1</image_url_3>
         <image_url_4>W1</image_url_4>
         <image_url_5>X1</image_url_5>
         <pm>Y1</pm>
         <options>Z1</options>
         <search_keyword>AA1</search_keyword>
         <list_price>120.00</list_price>
         <shipping>10.00</shipping>
      </product_1>
      <product_2>
         <supplier_sku>A2</supplier_sku>
         <brand_id>1</brand_id>
         <brand_name>B1</brand_name>
         <mpn>C1</mpn>
         <stock>2</stock>
         <offer_price>100.00</offer_price>
         <cost_price>60.00</cost_price>
         <product_name>D1</product_name>
         <features1>E1</features1>
         <features2>F1</features2>
         <features3>G1</features3>
         <features4>H1</features4>
         <features5>I1</features5>
         <product_details>J1</product_details>
         <specification>K1</specification>
         <dimension>L1</dimension>
         <colour>M1</colour>
         <size>N1</size>
         <factory_url>O1</factory_url>
         <package_content>P1</package_content>
         <warranty>Q1</warranty>
         <category_id>3</category_id>
         <category_name>R1</category_name>
         <weight>S1</weight>
         <image_url_1>T1</image_url_1>
         <image_url_2>U1</image_url_2>
         <image_url_3>V1</image_url_3>
         <image_url_4>W1</image_url_4>
         <image_url_5>X1</image_url_5>
         <pm>Y1</pm>
         <options>Z1</options>
         <search_keyword>AA1</search_keyword>
         <list_price>120.00</list_price>
         <shipping>10.00</shipping>
      </product_2>
      <product_3>
         <supplier_sku>A3</supplier_sku>
         <brand_id>1</brand_id>
         <brand_name>B1</brand_name>
         <mpn>C1</mpn>
         <stock>2</stock>
         <offer_price>100.00</offer_price>
         <cost_price>60.00</cost_price>
         <product_name>D1</product_name>
         <features1>E1</features1>
         <features2>F1</features2>
         <features3>G1</features3>
         <features4>H1</features4>
         <features5>I1</features5>
         <product_details>J1</product_details>
         <specification>K1</specification>
         <dimension>L1</dimension>
         <colour>M1</colour>
         <size>N1</size>
         <factory_url>O1</factory_url>
         <package_content>P1</package_content>
         <warranty>Q1</warranty>
         <category_id>3</category_id>
         <category_name>R1</category_name>
         <weight>S1</weight>
         <image_url_1>T1</image_url_1>
         <image_url_2>U1</image_url_2>
         <image_url_3>V1</image_url_3>
         <image_url_4>W1</image_url_4>
         <image_url_5>X1</image_url_5>
         <pm>Y1</pm>
         <options>Z1</options>
         <search_keyword>AA1</search_keyword>
         <list_price>120.00</list_price>
         <shipping>10.00</shipping>
      </product_3>
   </response>
</root>
XML;

            //post
            $headers = array(
                "Content-type: text/xml"
                ,"Content-length: ".strlen($xml_data)
                ,"Connection: close"
            );

            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL,"http://demo.local.b2b/api/port-a");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
            $response = curl_exec($ch); 
            
            if(curl_errno($ch))
            {
                print curl_error($ch);
            }else{
                curl_close($ch);
                $result = TRUE;
            }
if($result)
{
    echo "True";
}else{
    echo "False";
}
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
    
    function postProductTestAction()
    {
        $product_filter = new Databases_Joins_ProductFilter();
        $product_filter->PostXmlToRefreshProducts();
    }
}
