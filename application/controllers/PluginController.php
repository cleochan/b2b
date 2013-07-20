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
    
    function testWsdlClientAction()
    {
        $client = new SoapClient("http://demo.local.b2b/wsdl/s1.wsdl");

        Algorithms_Extensions_Plugin::FormatArray($client->S1(2));
        
        die;
    }
    
    function testWsdlServerAction()
    {
            function S1($num)
            {
                if(1==$num)
                {
                    $result = array("a","b","c");
                }elseif(2==$num)
                {
                    $result = array("d","e","f");
                }
                
                return $result;
            }

        ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

        $server = new SoapServer("http://demo.local.b2b/wsdl/s1.wsdl");

        $server->addFunction("S1");

        $server->handle();
        
        die;
    }
    
    function importProductAction()
    {
        $select = $this->db->select();
        $select->from("ss_products as p", array("productID as product_id", "product_code as supplier_sku", "brand as brand_name", "MPN as mpn", "in_stock as stock", "Price as offer_price", "name as product_name", "categoryID as category_id"));
        $select->joinLeft("ss_product_additional as a", "a.productID=p.productID", array("cost as cost_price"));
        $data = $this->db->fetchAll($select);
        
        //Algorithms_Extensions_Plugin::FormatArray($data);die;
        
        foreach($data as $d)
        {
            $random1 = mt_rand(1000, 9999);
            $random2 = mt_rand(10, 99);
            
            $new = array(
                "product_id" => $d['product_id'],
                "supplier_sku" => $d['supplier_sku'],
                "brand_name" => $d['brand_name'],
                "mpn" => $d['mpn'],
                "stock" => $d['stock'],
                "offer_price" => $d['offer_price'],
                "cost_price" => $d['cost_price'],
                "product_name" => $d['product_name'],
                "features1" => "features1 - ".$random1,
                "features2" => "features2 - ".$random1,
                "features3" => "features3 - ".$random1,
                "features4" => "features4 - ".$random1,
                "features5" => "features5 - ".$random1,
                "product_details" => "product_details - ".$random1,
                "specification" => "specification - ".$random1,
                "dimension" => "dimension - ".$random1,
                "colour" => "colour - ".$random1,
                "size" => "size - ".$random1,
                "factory_url" => "http://cdn.crazysales.com.au/factory_url/".$random1.".jpg",
                "package_content" => "package_content - ".$random1,
                "warranty" => "warranty - ".$random1,
                "category_id" => $d['category_id'],
                "category_name" => NULL,
                "weight" => $random1,
                "image_url_1" => "http://cdn.crazysales.com.au/image_url_1/".$random1.".jpg",
                "image_url_2" => "http://cdn.crazysales.com.au/image_url_2/".$random1.".jpg",
                "image_url_3" => "http://cdn.crazysales.com.au/image_url_3/".$random1.".jpg",
                "image_url_4" => "http://cdn.crazysales.com.au/image_url_4/".$random1.".jpg",
                "image_url_5" => "http://cdn.crazysales.com.au/image_url_5/".$random1.".jpg",
                "pm" => "pm - ".$random1,
                "options" => "options - ".$random1,
                "search_keyword" => "search_keyword - ".$random1,
                "list_price" => $d['offer_price']+200,
                "shipping" => $random2
            );

            //$this->db->insert("product_info_3", $new);
        }
        
        echo "End.";
        die;
    }
    
    function wsdlTest2Action()
    {
        $url = 'http://10.0.0.186:8743/OrderService.svc?wsdl';
        $client = new SoapClient($url, 
            array(
                'trace' => true, 
                'exceptions' => true,
                ''
            )
        );
        
        // 2. Pass Array 
         $response = $client->SetOrderStatus(array(
            'request' => array('OrderStatus' => array(
                array(
                    'OrderNumber' => 37344470,
                    'StatusID' => 3,
                    'Status' => 'Processing',	
                )
            ))
        ));

        echo '<pre>';
        var_dump($response);
        var_dump($client->__getLastRequestHeaders());
        echo $client->__getLastRequest();
        echo '</pre>';
        
        die;
    }
    
    function strTestAction()
    {
        $feed_column_value = 'hello "world", how are you ?';
        $feed_column_value = str_replace('"', "'", $feed_column_value);
        
        echo $feed_column_value;
        die;
    }
    
    function categoryTestAction()
    {
        $cat_id =   $this->_request->getParam('cat_id');
        $productFilter  =   new Databases_Joins_ProductFilter();
        print_r($productFilter->getProductCategoryInfo($cat_id));
        die();
    }
    
    function getProductsAction()
    {
        $page_now =   $this->_request->getParam('now');
        $PerPage =   $this->_request->getParam('per');
        $product_webservice_model   =   new Algorithms_Core_ProductService();
        $product_webservice_model->EntriesPerPage =   $PerPage;
        $product_webservice_model->PageNumber =   $page_now;
        $reponse_data  =   $product_webservice_model->WebServicesGetProducts();
        print_r($reponse_data);
        die();
    }
    function pkAction()
    {
        echo md5("lk32kgsiU,ykjhl2k3rsfAlkjh!3hEsdgf");
        die;
    }
}
