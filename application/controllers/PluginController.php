<?php

class PluginController extends Zend_Controller_Action
{
    var $product_categories;
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
    
    function paAction()
    {
        if(1 != $a)
        {
            echo "a";
        }else{
            echo "b";
        }
        die;
    }
    
    function setStatusAction()
    {
        $order_service_model    =   new Algorithms_Core_OrderService();
        $crazySalesOrderStatusType1 =   new CrazySalesOrderStatusType();
        $crazySalesOrderStatusType2 =   new CrazySalesOrderStatusType();
        $crazySalesOrderStatusType3 =   new CrazySalesOrderStatusType();
        $crazySalesOrderStatusType1->OrderNumber    =   '37850675';//37850576
        $crazySalesOrderStatusType1->Status         =   'Processing';
        $crazySalesOrderStatusType1->StatusID       =   3;
        $crazySalesOrderStatusType1->PurchaseOrderId       =   429;
        $crazySalesOrderStatusType2->OrderNumber    =   '37850576';//37850576
        $crazySalesOrderStatusType2->Status         =   'Processing';
        $crazySalesOrderStatusType2->StatusID       =   3;
        $crazySalesOrderStatusType1->PurchaseOrderId       =   428;
        $crazySalesOrderStatusType3->OrderNumber    =   '37850535';//37850576
        $crazySalesOrderStatusType3->Status         =   'Processing';
        $crazySalesOrderStatusType3->StatusID       =   3;
        $crazySalesOrderStatusType1->PurchaseOrderId       =   427;
        $crazySalesOrderStatusType  =   array();
        $crazySalesOrderStatusType[]    =   $crazySalesOrderStatusType1;
        $crazySalesOrderStatusType[]    =   $crazySalesOrderStatusType2;
        $crazySalesOrderStatusType[]    =   $crazySalesOrderStatusType3;
        $order_service_model->crazySalesOrderStatusType =   $crazySalesOrderStatusType;
        $result_message =   $order_service_model->WebServiceSetOrderStatus();
        $feed_class =   new Algorithms_Core_Feed();
        if($result_message['MessageType'])
        {
            foreach ($result_message['MessageType'] as $message_type)
            {
                $string = trim($message_type['Description']);
                $result =   '';
                if($string)
                {
                    $length = strlen($string);

                    while($length)
                    {
                        $from = strpos($string, "[");
                        $to = strpos($string, "]");

                        if(FALSE !== $from && FALSE !== $to && $from < $to)
                        {
                            $result = substr($string, $from+1, $to-$from-1);

                            $string = substr($string, $to+1);

                            $length = strlen($string);
                        }else{
                            $length = 0; // Exit
                        }
                    }
                }
            }
        }
        die;
        
    }
    
    function ftpTestAction(){
        $conn = ftp_connect("5.152.218.218") or die("Could not connect");
        ftp_login($conn,"administrator","Huahua123456");
        echo ftp_put($conn,"56413_203091_F.jpg","http://www.crazysales.com.au/products_pictures/564/56413_203091_F.jpg",FTP_BINARY);
        ftp_close($conn);
        die;
    }
    
    function menuFilterAction(){
        $html   =   "<div id='hc' class='mmenu'>
<ul>
<li class='top'><a href='/appliances/'><span>Appliances</span></a>
<ul class='mmenu-sub'><li><a href='/appliances/air-conditioners-fans/' class='fi'>Air Conditioners &amp; Fans</a></li>
<li><a href='/appliances/blenders-mixers-food-processors/'>Blenders, Mixers &amp; Food Processors</a></li>
<li><a href='/appliances/breadmakers/'>Breadmakers</a></li>
<li><a href='/appliances/coffee-machines/'>Coffee Machines</a></li>
<li><a href='/appliances/cooking-appliances/'>Cooking Appliances</a></li>
<li><a href='/appliances/eurolab/'>Eurolab</a></li>
<li><a href='/appliances/heaters-fireplaces/'>Heaters - Gas &amp; Electric</a></li>
<li><a href='/appliances/ironing/'>Ironing</a></li>
<li><a href='/appliances/juicers/'>Juicers</a></li>
<li><a href='/appliances/kettles-jugs-urns/'>Kettles, Jugs and Urns</a></li>
<li><a href='/appliances/lamps/'>Lamps</a></li>
<li><a href='/appliances/microwaves/'>Microwaves</a></li>
<li><a href='/appliances/morphy-richards/'>Morphy Richards</a></li>
<li><a href='/appliances/novelties-1/'>Novelties</a></li>
<li><a href='/appliances/rank-arena/'>Rank Arena</a></li>
<li><a href='/appliances/sewing-machines/'>Sewing Machines</a></li>
<li><a href='/appliances/sheffield/'>Sheffield</a></li>
<li><a href='/appliances/steam-cleaning/'>Steam Cleaning</a></li>
<li><a href='/appliances/sunbeam/'>Sunbeam</a></li>
<li><a href='/appliances/toasters/'>Toasters</a></li>
<li><a href='/appliances/vacuum-steam-cleaners/'>Vacuum Cleaners</a></li>
<li><a href='/appliances/water-coolers/'>Water Coolers</a></li>
<li><a href='/appliances/white-goods/'>White Goods</a></li>
</ul></li><li class='top'><a href='/baby-1/'><span>Baby</span></a>
<ul class='mmenu-sub'><li><a href='/baby-1/baby-carriers/' class='fi'>Baby Carriers</a></li>
<li><a href='/baby-1/bathing-toilet-training/'>Bathing &amp; Toilet Training</a></li>
<li><a href='/baby-1/car-seats/'>Car Seats</a></li>
<li><a href='/baby-1/change-tables/'>Change Tables</a></li>
<li><a href='/baby-1/cots-portacots/'>Cots &amp; Portacots</a></li>
<li><a href='/baby-1/feeding/'>Feeding</a></li>
<li><a href='/baby-1/gifts-and-keepsake/'>Gifts and Keepsake</a></li>
<li><a href='/baby-1/health-safety/'>Health &amp; Safety</a></li>
<li><a href='/baby-1/high-chairs/'>High Chairs</a></li>
<li><a href='/baby-1/monitors-mobiles/'>Monitors &amp; Mobiles</a></li>
<li><a href='/baby-1/nursery-manchester/'>Nursery Manchester &amp; Decor</a></li>
<li><a href='/baby-1/nursery-toys/'>Nursery Toys</a></li>
<li><a href='/baby-1/other-1/'>Other</a></li>
<li><a href='/baby-1/play-gyms-activity-centres/'>Play Gyms &amp; Activity Centres</a></li>
<li><a href='/baby-1/prams-strollers/'>Prams &amp; Strollers</a></li>
<li><a href='/baby-1/safety-gates-playpens/'>Safety Gates &amp; Playpens</a></li>
<li><a href='/baby-1/swings-bouncers/'>Swings &amp; Bouncers</a></li>
</ul></li><li class='top'><a href='/christmas-lights/'><span>Christmas Lights</span></a>
<ul class='mmenu-sub'><li><a href='/christmas-lights/acrylic-christmas-figurine/' class='fi'>Acrylic Christmas Figurines</a></li>
<li><a href='/christmas-lights/chasing-waterfall-led-lights/'>Chasing Waterfall LED lights</a></li>
<li><a href='/christmas-lights/cherry-blossom-christmas-trees/'>Cherry Blossom Trees</a></li>
<li><a href='/christmas-lights/christmas-decorations/'>Christmas Decorations</a></li>
<li><a href='/christmas-lights/christmas-garden-lights/'>Christmas Garden Lights</a></li>
<li><a href='/christmas-lights/christmas-led-lights/'>Christmas LED Lights</a></li>
<li><a href='/christmas-lights/christmas-novelty/'>Christmas Novelty</a></li>
<li><a href='/christmas-lights/christmas-trees/'>Christmas Trees</a></li>
<li><a href='/christmas-lights/inflatable-christmas-displays/'>Inflatable Displays</a></li>
<li><a href='/christmas-lights/christmas-rope-lights/'>Rope Lights</a></li>
<li><a href='/christmas-lights/silhouette-rope-lights/'>Silhouette Rope Lights</a></li>
<li><a href='/christmas-lights/solar-lights/'>Solar Lights</a></li>
</ul></li><li class='top'><a href='/computers/'><span>Computers</span></a>
<ul class='mmenu-sub'><li><a href='/computers/accessories/' class='fi'>Accessories</a></li>
<li><a href='/computers/android-tablets/'>Android Tablets</a></li>
<li><a href='/computers/apple/'>Apple</a></li>
<li><a href='/computers/comb-binders-1/'>Binders, Laminators &amp; Shredders</a></li>
<li><a href='/computers/desktop-computers-pcs/'>Desktop Computers PCs</a></li>
<li><a href='/computers/hard-drives/'>Hard Drives - External</a></li>
<li><a href='/computers/ink-toner-brother-1/'>Ink &amp; Toner - Brother</a></li>
<li><a href='/computers/ink-toner-canon-1/'>Ink &amp; Toner - Canon</a></li>
<li><a href='/computers/ink-toner-epson-1/'>Ink &amp; Toner - Epson</a></li>
<li><a href='/computers/ink-toner-hp-1/'>Ink &amp; Toner - HP</a></li>
<li><a href='/computers/ink-toner-lexmark-1/'>Ink &amp; Toner - Lexmark</a></li>
<li><a href='/computers/ink-toner-samsung-1/'>Ink &amp; Toner - Samsung</a></li>
<li><a href='/computers/ink-toner-xerox-1/'>Ink &amp; Toner - Xerox</a></li>
<li><a href='/computers/ink-toner-cartridges/'>Ink &amp; Toner Cartridges</a></li>
<li><a href='/computers/ipad-accessories/'>iPad Accessories</a></li>
<li><a href='/computers/keyboards-mouse-webcams/'>Keyboards, Mouse &amp; Webcams</a></li>
<li><a href='/computers/laptop-accessories/'>Laptop Accessories</a></li>
<li><a href='/computers/laptops-pcs/'>Laptops &amp; Notebooks</a></li>
<li><a href='/computers/lcd-led-monitors/'>LCD &amp; LED Monitors</a></li>
<li><a href='/computers/logitech/'>Logitech</a></li>
<li><a href='/computers/memory-cards-usb-drives/'>Memory Cards &amp; USB Drives</a></li>
<li><a href='/computers/networking/'>Networking</a></li>
<li><a href='/computers/printers-scanners/'>Printers &amp; Scanners</a></li>
<li><a href='/computers/usb-gadgets/'>USB Gadgets</a></li>
</ul></li><li class='top'><a href='/electronics/'><span>Electronics</span></a>
<ul class='mmenu-sub'><li><a href='/electronics/accessories-2/' class='fi'>Accessories</a></li>
<li><a href='/electronics/auto-electronics-1/'>Auto Electronics</a></li>
<li><a href='/electronics/av-accessories/'>AV Accessories</a></li>
<li><a href='/electronics/cordless-home-phones/'>Cordless Home Phones</a></li>
<li><a href='/electronics/digital-cameras/'>Digital Cameras</a></li>
<li><a href='/electronics/digital-photo-frames-1/'>Digital Photo Frames</a></li>
<li><a href='/electronics/digital-video-cameras/'>Digital Video Cameras</a></li>
<li><a href='/electronics/dvd-players/'>DVD Players</a></li>
<li><a href='/electronics/ebook-readers-tablets/'>eBook Readers &amp; Tablets</a></li>
<li><a href='/electronics/energy-efficient-products/'>Energy Efficient Products</a></li>
<li><a href='/electronics/gadgets/'>Gadgets</a></li>
<li><a href='/electronics/gps-units/'>GPS Units</a></li>
<li><a href='/electronics/headphones-speakers/'>Headphones &amp; Speakers</a></li>
<li><a href='/electronics/hifi-radio/'>HiFi &amp; Radio</a></li>
<li><a href='/electronics/home-theatre/'>Home Theatre</a></li>
<li><a href='/electronics/iphone-ipod-docks/'>iPhone &amp; iPod Docks</a></li>
<li><a href='/electronics/led-lighting/'>LED Lighting</a></li>
<li><a href='/electronics/media-conversion/'>Media Conversion</a></li>
<li><a href='/electronics/media-players/'>Media Players</a></li>
<li><a href='/electronics/mobile-phone-accessories/'>Mobile Phone Accessories</a></li>
<li><a href='/electronics/mobile-smart-phones/'>Mobile Smart Phones</a></li>
<li><a href='/electronics/portable-audio-mp3-players/'>Portable Audio &amp; MP3 Players</a></li>
<li><a href='/electronics/projectors/'>Projectors</a></li>
<li><a href='/electronics/tvs/'>TVs</a></li>
<li><a href='/electronics/weather-stations/'>Weather Stations</a></li>
</ul></li><li class='top'><a href='/fashion/'><span>Fashion</span></a>
<ul class='mmenu-sub'><li><a href='/fashion/casual-shoes-boys/' class='fi'>Casual Shoes - Boys</a></li>
<li><a href='/fashion/casual-shoes-mens/'>Casual Shoes - Mens</a></li>
<li><a href='/fashion/casual-shoes-womens/'>Casual Shoes - Womens</a></li>
<li><a href='/fashion/dancewear/'>Dancewear</a></li>
<li><a href='/fashion/dresses-womens/'>Dresses - Womens</a></li>
<li><a href='/fashion/ed-hardy-mens-shoes/'>Ed Hardy - Mens Shoes</a></li>
<li><a href='/fashion/hats/'>Hats</a></li>
<li><a href='/fashion/jackets-sweaters-childrens/'>Jackets &amp; Sweaters - Childrens</a></li>
<li><a href='/fashion/mens-jackets-sweats/'>Jackets &amp; Sweaters - Men</a></li>
<li><a href='/fashion/womens-jackets-sweats/'>Jackets &amp; Sweaters - Womens</a></li>
<li><a href='/fashion/jeans-1/'>Jeans &amp; Pants</a></li>
<li><a href='/fashion/onesies/'>Onesies</a></li>
<li><a href='/fashion/polo-shirts/'>Polo Shirts - Mens</a></li>
<li><a href='/fashion/polo-shirts-womens/'>Polo Shirts - Womens</a></li>
<li><a href='/fashion/shirts-womens/'>Shirts - Womens and Mens</a></li>
<li><a href='/fashion/shorts-and-board-shorts-mens/'>Shorts and Board Shorts - Mens</a></li>
<li><a href='/fashion/slippers/'>Slippers</a></li>
<li><a href='/fashion/sunglasses/'>Sunglasses</a></li>
<li><a href='/fashion/swimwear/'>Swimwear</a></li>
<li><a href='/fashion/t-shirts-childrens/'>T-Shirts - Childrens</a></li>
<li><a href='/fashion/t-shirts-mens-large/'>T-Shirts - Mens - Large</a></li>
<li><a href='/fashion/t-shirts-mens-medium/'>T-Shirts - Mens - Medium</a></li>
<li><a href='/fashion/t-shirts/'>T-Shirts - Mens - Small</a></li>
<li><a href='/fashion/t-shirts-mens-xlarge/'>T-Shirts - Mens - XLarge</a></li>
<li><a href='/fashion/t-shirts-mens-xxlarge/'>T-Shirts - Mens - XXLarge</a></li>
<li><a href='/fashion/t-shirts-womens-large/'>T-Shirts - Womens - Large</a></li>
<li><a href='/fashion/t-shirts-womens-medium/'>T-Shirts - Womens - Medium</a></li>
<li><a href='/fashion/t-shirts-womens/'>T-Shirts - Womens - Small</a></li>
<li><a href='/fashion/t-shirts-womens-xlarge/'>T-Shirts - Womens - XLarge</a></li>
<li><a href='/fashion/t-shirts-womens-xsmall/'>T-Shirts - Womens - XSmall</a></li>
<li><a href='/fashion/t-shirts-womens-xxlarge/'>T-Shirts - Womens - XXLarge</a></li>
<li><a href='/fashion/tank-tops-womens-and-mens/'>Tank Tops - Womens and Mens</a></li>
<li><a href='/fashion/tops-womens/'>Tops - Womens</a></li>
<li><a href='/fashion/trackpants/'>Trackpants</a></li>
<li><a href='/fashion/tracksuits/'>Tracksuits</a></li>
<li><a href='/fashion/shoes-true-religion/'>True Religion - Mens Shoes</a></li>
<li><a href='/fashion/uggs-ankle/'>Uggs - Ankle</a></li>
<li><a href='/fashion/uggs-short/'>Uggs - Short</a></li>
<li><a href='/fashion/uggs-tall/'>Uggs - Tall</a></li>
<li><a href='/fashion/underwear-socks/'>Underwear &amp; Socks</a></li>
<li><a href='/fashion/womens-plus-sizes/'>Womens Plus Sizes</a></li>
<li><a href='/fashion/wrangler/'>Wrangler</a></li>
</ul></li><li class='top'><a href='/furniture/'><span>Furniture</span></a>
<ul class='mmenu-sub'><li><a href='/furniture/bar-stools-tables/' class='fi'>Bar Stools</a></li>
<li><a href='/furniture/bathroom-furniture/'>Bathroom Furniture</a></li>
<li><a href='/furniture/bedroom-furniture/'>Bedroom Furniture</a></li>
<li><a href='/furniture/bedroom-furniture-beds/'>Bedroom Furniture - Beds</a></li>
<li><a href='/furniture/bookcases-shelves/'>Bookcases &amp; Shelves</a></li>
<li><a href='/furniture/coffee-side-tables/'>Coffee &amp; Side Tables</a></li>
<li><a href='/furniture/dining-settings/'>Dining Settings</a></li>
<li><a href='/furniture/entertainment-tv-units/'>Entertainment &amp; TV Units</a></li>
<li><a href='/furniture/home-office-office-chairs/'>Home Office - Chairs</a></li>
<li><a href='/furniture/home-office-desks/'>Home Office - Desks</a></li>
<li><a href='/furniture/home-office-filing-cabinets-storage/'>Home Office - Storage</a></li>
<li><a href='/furniture/kids-furniture/'>Kids Furniture</a></li>
<li><a href='/furniture/lounges/'>Lounges &amp; Recliners</a></li>
<li><a href='/furniture/mattresses/'>Mattresses</a></li>
<li><a href='/furniture/multipurpose-cupboards/'>Multipurpose Cupboards</a></li>
<li><a href='/furniture/occasional-chairs/'>Occasional Chairs</a></li>
<li><a href='/furniture/office-furniture/'>Office Furniture</a></li>
<li><a href='/furniture/outdoor-furniture-1/'>Outdoor Furniture</a></li>
<li><a href='/furniture/replica-furniture/'>Replica Furniture</a></li>
<li><a href='/furniture/storage-all/'>Storage - All</a></li>
<li><a href='/furniture/storage-clothes/'>Storage - Clothes</a></li>
<li><a href='/furniture/storage-dvd-media/'>Storage - DVD &amp; Media</a></li>
<li><a href='/furniture/storage-jewellery/'>Storage - Jewellery</a></li>
<li><a href='/furniture/storage-shoes/'>Storage - Shoes</a></li>
<li><a href='/furniture/wall-art-all/'>Wall Art - All</a></li>
<li><a href='/furniture/wall-art-christmas/'>Wall Art - Christmas</a></li>
<li><a href='/furniture/wall-art-home-decor/'>Wall Art - Home Decor</a></li>
<li><a href='/furniture/wall-art-kids/'>Wall Art - Kids</a></li>
</ul></li><li class='top'><a href='/kitchen-home/'><span>Homewares</span></a>
<ul class='mmenu-sub'><li><a href='/kitchen-home/bathroom-accessories/' class='fi'>Bathroom - Accessories</a></li>
<li><a href='/kitchen-home/candles-burners-1/'>Candles &amp; Burners</a></li>
<li><a href='/kitchen-home/clocks-2/'>Clocks</a></li>
<li><a href='/kitchen-home/decorator/'>Decorator</a></li>
<li><a href='/kitchen-home/gifts/'>Gifts</a></li>
<li><a href='/kitchen-home/handbags-luggage/'>Handbags &amp; Luggage</a></li>
<li><a href='/kitchen-home/home-laundry-ironing-airers/'>Home Laundry, Ironing &amp; Airers</a></li>
<li><a href='/kitchen-home/kitchen-accessories-utensils/'>Kitchen - Accessories &amp; Utensils</a></li>
<li><a href='/kitchen-home/kitchen-bakeware/'>Kitchen - Bakeware</a></li>
<li><a href='/kitchen-home/kitchen-bins/'>Kitchen - Bins and Storage</a></li>
<li><a href='/kitchen-home/kitchen-coffee-tea-accessories/'>Kitchen - Coffee &amp; Tea Accessories</a></li>
<li><a href='/kitchen-home/kitchen-cookware/'>Kitchen - Cookware</a></li>
<li><a href='/kitchen-home/kitchen-food-storage/'>Kitchen - Food Storage</a></li>
<li><a href='/kitchen-home/kitchen-knives/'>Kitchen - Knives</a></li>
<li><a href='/kitchen-home/kitchen-mugs-and-cups/'>Kitchen - Mugs and Cups</a></li>
<li><a href='/kitchen-home/kitchen-scales/'>Kitchen - Scales</a></li>
<li><a href='/kitchen-home/lanterns/'>Lanterns</a></li>
<li><a href='/kitchen-home/photo-frames-1/'>Photo Frames</a></li>
<li><a href='/kitchen-home/tabletop-and-cutlery/'>Tabletop - Cutlery</a></li>
<li><a href='/kitchen-home/tabletop-dinnerware/'>Tabletop - Dinnerware</a></li>
<li><a href='/kitchen-home/tabletop-glassware/'>Tabletop - Glassware</a></li>
<li><a href='/kitchen-home/tabletop-servingware/'>Tabletop - Servingware</a></li>
<li><a href='/kitchen-home/tabletop-silverware/'>Tabletop - Silverware</a></li>
</ul></li><li class='top'><a href='/jewellery/'><span>Jewellery</span></a>
<ul class='mmenu-sub'><li><a href='/jewellery/bangles-layered/'>Bangles - Layered</a></li>
<li><a href='/jewellery/bracelets-fine/'>Bracelets - Fine</a></li>
<li><a href='/jewellery/bracelets-layered/'>Bracelets - Layered</a></li>
<li><a href='/jewellery/bracelets-leather-and-silk/'>Bracelets - Leather and Silk</a></li>
<li><a href='/jewellery/earrings-fashion/'>Earrings - Fashion</a></li>
<li><a href='/jewellery/earrings-fine/'>Earrings - Fine</a></li>
<li><a href='/jewellery/earrings-layered/'>Earrings - Layered</a></li>
<li><a href='/jewellery/earrings-solid-sterling-silver/'>Earrings - Solid Sterling Silver</a></li>
<li><a href='/jewellery/gifts-and-sets/'>Gifts and Sets</a></li>
<li><a href='/jewellery/necklaces-fashion/'>Necklaces - Fashion</a></li>
<li><a href='/jewellery/necklaces-fine/'>Necklaces - Fine</a></li>
<li><a href='/jewellery/necklaces-layered/'>Necklaces - Layered</a></li>
<li><a href='/jewellery/necklaces-pearl/'>Necklaces - Pearl</a></li>
<li><a href='/jewellery/necklaces-solid-sterling-silver-pendant/'>Necklaces - Solid Sterling Silver Pendant</a></li>
<li><a href='/jewellery/nrl-supporter-jewellery/'>NRL Supporter Jewellery</a></li>
<li><a href='/jewellery/personalised/'>Personalised</a></li>
<li><a href='/jewellery/rings-engagement/'>Rings - Engagement</a></li>
<li><a href='/jewellery/rings-fashion/'>Rings - Fashion</a></li>
<li><a href='/jewellery/rings-fine/'>Rings - Fine</a></li>
<li><a href='/jewellery/rings-wedding/'>Rings - Wedding</a></li>
</ul></li><li class='top'><a href='/manchester/'><span>Manchester</span></a>
<ul class='mmenu-sub'><li><a href='/manchester/bath-floor-mats/' class='fi'>Bath Mats &amp; Shower Curtains</a></li>
<li><a href='/manchester/bathrobe/'>Bathrobe</a></li>
<li><a href='/manchester/bedding_accessories_and_valances/'>Bedding Accessories and Valances</a></li>
<li><a href='/manchester/bed-essentials-blankets/'>Blankets</a></li>
<li><a href='/manchester/blankets-electric/'>Blankets - Electric</a></li>
<li><a href='/manchester/http-www-dealsdirect-com-au-manchester-home-decor/'>Blinds &amp; Curtains</a></li>
<li><a href='/manchester/brands-canningvale/'>Brands - Canningvale</a></li>
<li><a href='/manchester/brands-sheridan-actil/'>Brands - Sheridan Actil</a></li>
<li><a href='/manchester/brands-tontine/'>Brands - Tontine</a></li>
<li><a href='/manchester/children-manchester/'>Children Manchester</a></li>
<li><a href='/manchester/comforter-sets/'>Comforter Sets &amp; Bedspreads</a></li>
<li><a href='/manchester/cushions/'>Cushions</a></li>
<li><a href='/manchester/http-www-dealsdirect-com-au-manchester-home-decor-1/'>Home Decor</a></li>
<li><a href='/manchester/protectors-toppers/'>Mattress Toppers &amp; Protectors</a></li>
<li><a href='/manchester/pillowcases/'>Pillowcases</a></li>
<li><a href='/manchester/pillows/'>Pillows</a></li>
<li><a href='/manchester/quilt-and-sheet-sets-coordinated/'>Quilt and Sheet Sets - Coordinated</a></li>
<li><a href='/manchester/quilt-covers-double/'>Quilt Covers - Double</a></li>
<li><a href='/manchester/quilt-covers-king/'>Quilt Covers - King</a></li>
<li><a href='/manchester/quilt-covers-queen/'>Quilt Covers - Queen</a></li>
<li><a href='/manchester/quilt-covers-single/'>Quilt Covers - Single</a></li>
<li><a href='/manchester/duvets-quilts/'>Quilts and Doonas</a></li>
<li><a href='/manchester/sheets-sets-1000tc/'>Sheets - 1000TC</a></li>
<li><a href='/manchester/sheets-225tc-sets/'>Sheets - 225TC - 375TC</a></li>
<li><a href='/manchester/sheets-400tc-750tc/'>Sheets - 400TC -750TC</a></li>
<li><a href='/manchester/sheets-flannelette/'>Sheets - Flannelette</a></li>
<li><a href='/manchester/sheets-microfibre/'>Sheets - Microfibre</a></li>
<li><a href='/manchester/table-linen/'>Table Linen</a></li>
<li><a href='/manchester/throw-rugs/'>Throw Rugs</a></li>
<li><a href='/manchester/towels-bathrobes/'>Towels</a></li>
<li><a href='/manchester/towels-beach-towels/'>Towels - Beach Towels</a></li>
</ul></li><li class='top'><a href='/outdoors-1/'><span>Outdoors</span></a>
<ul class='mmenu-sub'><li><a href='/outdoors-1/backpacks-and-duffel-bags/' class='fi'>Backpacks and Duffel Bags</a></li>
<li><a href='/outdoors-1/bbq/'>BBQ</a></li>
<li><a href='/outdoors-1/boating/'>Boating</a></li>
<li><a href='/outdoors-1/camping-bedding/'>Camping Bedding</a></li>
<li><a href='/outdoors-1/camping-furniture/'>Camping Furniture</a></li>
<li><a href='/outdoors-1/camping-gear/'>Camping Gear</a></li>
<li><a href='/outdoors-1/camping-tents/'>Camping Tents</a></li>
<li><a href='/outdoors-1/cooler-boxes/'>Cooler Boxes</a></li>
<li><a href='/outdoors-1/festive-lights/'>Festive Lights</a></li>
<li><a href='/outdoors-1/garden-greenhouse/'>Garden Greenhouse</a></li>
<li><a href='/outdoors-1/garden-power-tools/'>Garden Power Tools</a></li>
<li><a href='/outdoors-1/garden-sheds/'>Garden Sheds</a></li>
<li><a href='/outdoors-1/garden-accessories/'>Gardening Accessories</a></li>
<li><a href='/outdoors-1/gazebos-shade-sails-umbrellas/'>Gazebos &amp; Party Tents</a></li>
<li><a href='/outdoors-1/hammocks/'>Hammocks</a></li>
<li><a href='/outdoors-1/heating/'>Heating</a></li>
<li><a href='/outdoors-1/outdoor-decor/'>Outdoor Decor</a></li>
<li><a href='/outdoors-1/outdoor-furniture-hardwood/'>Outdoor Furniture - Hardwood</a></li>
<li><a href='/outdoors-1/outdoor-furniture-outdoor-living/'>Outdoor Furniture - Outdoor Living</a></li>
<li><a href='/outdoors-1/outdoor-furniture-rattan/'>Outdoor Furniture - Rattan</a></li>
<li><a href='/outdoors-1/outdoor-furniture-storage/'>Outdoor Furniture - Storage</a></li>
<li><a href='/outdoors-1/outdoor-rugs-mats/'>Outdoor Rugs &amp; Mats</a></li>
<li><a href='/outdoors-1/outdoor-water-fun/'>Outdoor Water Fun</a></li>
<li><a href='/outdoors-1/pool-accessories/'>Pool Accessories</a></li>
<li><a href='/outdoors-1/pool-covers-and-cleaning/'>Pool Covers and Cleaning</a></li>
<li><a href='/outdoors-1/pools-and-spas/'>Pools and Spas</a></li>
<li><a href='/outdoors-1/shade-sails-umbrellas/'>Shade Sails &amp; Umbrellas</a></li>
<li><a href='/outdoors-1/solar-garden-lights/'>Solar Garden Lights</a></li>
<li><a href='/outdoors-1/tarpaulins/'>Tarpaulins</a></li>
<li><a href='/outdoors-1/water-fountains/'>Water Fountains</a></li>
</ul></li><li class='top'><a href='/perfume-1/'><span>Perfume</span></a>
<ul class='mmenu-sub'><li><a href='/perfume-1/azzaro/' class='fi'>Azzaro</a></li>
<li><a href='/perfume-1/burberry-1/'>Burberry</a></li>
<li><a href='/perfume-1/bvlgari/'>Bvlgari</a></li>
<li><a href='/perfume-1/cacharel/'>Cacharel</a></li>
<li><a href='/perfume-1/calvin-klein/'>Calvin Klein</a></li>
<li><a href='/perfume-1/carolina-herrera/'>Carolina Herrera</a></li>
<li><a href='/perfume-1/chanel/'>Chanel</a></li>
<li><a href='/perfume-1/chloe/'>Chloe</a></li>
<li><a href='/perfume-1/christian-audigier/'>Christian Audigier</a></li>
<li><a href='/perfume-1/christian-dior/'>Christian Dior</a></li>
<li><a href='/perfume-1/david-beckham/'>David Beckham</a></li>
<li><a href='/perfume-1/davidoff/'>Davidoff</a></li>
<li><a href='/perfume-1/dkny-1/'>DKNY</a></li>
<li><a href='/perfume-1/dolce-gabbana/'>Dolce &amp; Gabbana</a></li>
<li><a href='/perfume-1/dunhill/'>Dunhill</a></li>
<li><a href='/perfume-1/elizabeth-arden/'>Elizabeth Arden</a></li>
<li><a href='/perfume-1/elizabeth-taylor/'>Elizabeth Taylor</a></li>
<li><a href='/perfume-1/escada-1/'>Escada</a></li>
<li><a href='/perfume-1/giorgio-armani/'>Giorgio Armani</a></li>
<li><a href='/perfume-1/givenchy/'>Givenchy</a></li>
<li><a href='/perfume-1/gucci-2/'>Gucci</a></li>
<li><a href='/perfume-1/guerlain/'>Guerlain</a></li>
<li><a href='/perfume-1/guess-1/'>Guess</a></li>
<li><a href='/perfume-1/hugo-boss-1/'>Hugo Boss</a></li>
<li><a href='/perfume-1/issey-miyake/'>Issey Miyake</a></li>
<li><a href='/perfume-1/joop/'>Joop</a></li>
<li><a href='/perfume-1/kenzo/'>Kenzo</a></li>
<li><a href='/perfume-1/lacoste/'>Lacoste</a></li>
<li><a href='/perfume-1/lancome-1/'>Lancome</a></li>
<li><a href='/perfume-1/lanvin/'>Lanvin</a></li>
<li><a href='/perfume-1/liz-claiborne/'>Liz Claiborne</a></li>
<li><a href='/perfume-1/lolita-lempicka/'>Lolita Lempicka</a></li>
<li><a href='/perfume-1/mens-fragrances/'>Mens Fragrances</a></li>
<li><a href='/perfume-1/mens-gift-sets/'>Mens Gift Sets</a></li>
<li><a href='/perfume-1/nautica-1/'>Nautica</a></li>
<li><a href='/perfume-1/paco-rabanne/'>Paco Rabanne</a></li>
<li><a href='/perfume-1/ralph-lauren-1/'>Ralph Lauren</a></li>
<li><a href='/perfume-1/revlon/'>Revlon</a></li>
<li><a href='/perfume-1/salvatore-ferragamo/'>Salvatore Ferragamo</a></li>
<li><a href='/perfume-1/tommy-hilfiger/'>Tommy Hilfiger</a></li>
<li><a href='/perfume-1/vera-wang/'>Vera Wang</a></li>
<li><a href='/perfume-1/versace/'>Versace</a></li>
<li><a href='/perfume-1/womens-fragrances/'>Womens Fragrances</a></li>
<li><a href='/perfume-1/womens-gift-sets/'>Womens Gift Sets</a></li>
<li><a href='/perfume-1/yves-saint-laurent-1/'>Yves Saint Laurent</a></li>
</ul></li><li class='top'><a href='/personalised-products/'><span>Personalised Products</span></a>
<ul class='mmenu-sub'><li><a href='/personalised-products/personalised-afl/' class='fi'>Personalised - AFL</a></li>
<li><a href='/personalised-products/personalised-all-1/'>Personalised - All</a></li>
<li><a href='/personalised-products/personalised-books/'>Personalised - Books</a></li>
<li><a href='/personalised-products/personalised-chore-chart/'>Personalised - Chore Chart</a></li>
<li><a href='/personalised-products/personalised-christmas-1/'>Personalised - Christmas</a></li>
<li><a href='/personalised-products/personalised-jewellery/'>Personalised - Jewellery</a></li>
<li><a href='/personalised-products/personalised-kids-1/'>Personalised - Kids</a></li>
<li><a href='/personalised-products/personalised-labels-1/'>Personalised - Labels</a></li>
<li><a href='/personalised-products/personalised-motor-sports/'>Personalised - Motor Sports</a></li>
<li><a href='/personalised-products/personalised-nrl/'>Personalised - NRL</a></li>
<li><a href='/personalised-products/personalised-sports-memorabilia/'>Personalised - Other Sports</a></li>
<li><a href='/personalised-products/personalised-pets-1/'>Personalised - Pets</a></li>
<li><a href='/personalised-products/personalised-stationery-1/'>Personalised - Stationery</a></li>
<li><a href='/personalised-products/personalised-wall-art/'>Personalised - Wall Art</a></li>
</ul></li><li class='top'><a href='/pet-supplies/'><span>Pet Accessories</span></a>
<ul class='mmenu-sub'><li><a href='/pet-supplies/behaviour-training-aids/' class='fi'>Behaviour / Training Aids</a></li>
<li><a href='/pet-supplies/birds/'>Birds</a></li>
<li><a href='/pet-supplies/cat-food/'>Cat Food</a></li>
<li><a href='/pet-supplies/cat-scratching-poles-trees/'>Cat Scratching Poles / Trees</a></li>
<li><a href='/pet-supplies/cat-supplies/'>Cat Supplies</a></li>
<li><a href='/pet-supplies/dog-coats-jackets/'>Dog Coats/Jackets</a></li>
<li><a href='/pet-supplies/dog-food/'>Dog Food</a></li>
<li><a href='/pet-supplies/dog-supplies/'>Dog Supplies</a></li>
<li><a href='/pet-supplies/enclosures-playpens/'>Enclosures &amp; Playpens</a></li>
<li><a href='/pet-supplies/equine-supplies/'>Equine Supplies / Horse Rugs</a></li>
<li><a href='/pet-supplies/fish/'>Fish</a></li>
<li><a href='/pet-supplies/flea-tick-and-heartworm/'>Flea Tick and Heartworm</a></li>
<li><a href='/pet-supplies/kennels/'>Kennels</a></li>
<li><a href='/pet-supplies/others/'>Others</a></li>
<li><a href='/pet-supplies/pet-beds/'>Pet Beds</a></li>
<li><a href='/pet-supplies/pet-beds-heated/'>Pet Beds - Heated</a></li>
<li><a href='/pet-supplies/pet-feeders/'>Pet Feeders</a></li>
<li><a href='/pet-supplies/pet-joint-health/'>Pet Joint Health</a></li>
<li><a href='/pet-supplies/pet-toys/'>Pet Toys</a></li>
<li><a href='/pet-supplies/pet-travel/'>Pet Travel</a></li>
<li><a href='/pet-supplies/pet-wormers/'>Pet Wormers</a></li>
<li><a href='/pet-supplies/rabbit-hutches-chicken-coops/'>Rabbit Hutches and Chicken Coops</a></li>
<li><a href='/pet-supplies/shampoo-and-conditioners/'>Shampoo and Conditioners</a></li>
</ul></li><li class='top'><a href='/rugs/'><span>Rugs</span></a>
<ul class='mmenu-sub'><li><a href='/rugs/145cm-x75cm/' class='fi'>145cm x75cm</a></li>
<li><a href='/rugs/150cm-x-80cm/'>150cm x 80cm</a></li>
<li><a href='/rugs/165cm-x-115cm/'>165cm x 115cm</a></li>
<li><a href='/rugs/170cm-x-120cm/'>170cm x 120cm</a></li>
<li><a href='/rugs/225cm-x-155cm/'>225cm x 155cm</a></li>
<li><a href='/rugs/230cm-x-160cm/'>230cm x 160cm</a></li>
<li><a href='/rugs/280cm-x-190cm/'>280cm x 190cm</a></li>
<li><a href='/rugs/290cm-x-200cm/'>290cm x 200cm</a></li>
<li><a href='/rugs/300cm-x-80cm/'>300cm x 80cm</a></li>
<li><a href='/rugs/320cm-x-230cm/'>320cm x 230cm</a></li>
<li><a href='/rugs/330cm-x-240cm/'>330cm x 240cm</a></li>
<li><a href='/rugs/400cm-x-80cm/'>400cm x 80cm</a></li>
<li><a href='/rugs/arte-espina/'>Arte Espina</a></li>
<li><a href='/rugs/brink-campman/'>Brink &amp; Campman</a></li>
<li><a href='/rugs/designer-rugs/'>Designer Rugs</a></li>
<li><a href='/rugs/natural-fibre/'>Natural Fibre</a></li>
<li><a href='/rugs/novelty-childrens/'>Novelty &amp; Childrens</a></li>
<li><a href='/rugs/outdoor-rugs/'>Outdoor Rugs &amp; Mats</a></li>
<li><a href='/rugs/round/'>Round</a></li>
<li><a href='/rugs/rug-supa-grips/'>Rug Supa Grips</a></li>
<li><a href='/rugs/runners/'>Runners</a></li>
<li><a href='/rugs/shag-pile/'>Shag Pile</a></li>
<li><a href='/rugs/traditional/'>Traditional</a></li>
</ul></li><li class='top'><a href='/sports-fitness/'><span>Sports &amp; Fitness</span></a>
<ul class='mmenu-sub'><li><a href='/sports-fitness/as-seen-on-tv-2/' class='fi'>As Seen on TV</a></li>
<li><a href='/sports-fitness/bike-helmets/'>Bike Helmets</a></li>
<li><a href='/sports-fitness/bikes/'>Bikes</a></li>
<li><a href='/sports-fitness/boxing/'>Boxing</a></li>
<li><a href='/sports-fitness/compression-clothing/'>Compression Clothing</a></li>
<li><a href='/sports-fitness/electric-bikes/'>Electric Bikes</a></li>
<li><a href='/sports-fitness/elliptical-trainers/'>Elliptical Trainers</a></li>
<li><a href='/sports-fitness/exercise-bikes/'>Exercise Bikes</a></li>
<li><a href='/sports-fitness/fishing-accessories/'>Fishing Accessories</a></li>
<li><a href='/sports-fitness/fishing-combos/'>Fishing Combos</a></li>
<li><a href='/sports-fitness/fishing-reels/'>Fishing Reels</a></li>
<li><a href='/sports-fitness/fishing-rods/'>Fishing Rods</a></li>
<li><a href='/sports-fitness/fitness-accessories/'>Fitness Accessories</a></li>
<li><a href='/sports-fitness/fitness-dvds/'>Fitness DVDs</a></li>
<li><a href='/sports-fitness/games-tables-1/'>Games Tables</a></li>
<li><a href='/sports-fitness/golf/'>Golf</a></li>
<li><a href='/sports-fitness/fitness-gym-equipment/'>Gym Equipment &amp; Fitness</a></li>
<li><a href='/sports-fitness/kids-sport-outdoor-play/'>Kids Sport &amp; Outdoor Play</a></li>
<li><a href='/sports-fitness/pilates-yoga-toning/'>Pilates, Yoga &amp; Toning</a></li>
<li><a href='/sports-fitness/skateboards-scooters/'>Skateboards &amp; Scooters</a></li>
<li><a href='/sports-fitness/trampolines/'>Trampolines</a></li>
<li><a href='/sports-fitness/treadmills/'>Treadmills</a></li>
<li><a href='/sports-fitness/water-sports/'>Water Sports</a></li>
</ul></li><li class='top'><a href='/storage/'><span>Storage</span></a>
<ul class='mmenu-sub'><li><a href='/storage/bathroom-storage/' class='fi'>Bathroom Storage</a></li>
<li><a href='/storage/bedroom-storage/'>Bedroom Storage</a></li>
<li><a href='/storage/garage-storage/'>Garage Storage</a></li>
<li><a href='/storage/kids-storage/'>Kids Storage</a></li>
<li><a href='/storage/kitchen-storage/'>Kitchen Storage</a></li>
<li><a href='/storage/laundry-storage/'>Laundry Storage</a></li>
<li><a href='/storage/living-storage/'>Living Storage</a></li>
<li><a href='/storage/office-storage/'>Office Storage</a></li>
<li><a href='/storage/outdoor-storage/'>Outdoor Storage</a></li>
</ul></li><li class='top'><a href='/supermarket/'><span>Supermarket Deals</span></a>
<ul class='mmenu-sub'><li><a href='/supermarket/hair-care-1/'>Hair Care</a></li>
<li><a href='/supermarket/health-beauty-2/'>Health &amp; Beauty</a></li>
<li><a href='/supermarket/household/'>Household</a></li>
<li><a href='/supermarket/party-supplies/'>Party Supplies</a></li>
</ul></li><li class='top'><a href='/tools-auto/'><span>Tools &amp; Auto</span></a>
<ul class='mmenu-sub'><li><a href='/tools-auto/auto-accessories/' class='fi'>Auto Accessories</a></li>
<li><a href='/tools-auto/automotive-covers/'>Auto Covers</a></li>
<li><a href='/tools-auto/auto-electrical-battery-accessories/'>Auto Electrical &amp; Battery Accessories</a></li>
<li><a href='/tools-auto/auto-mats/'>Auto Mats</a></li>
<li><a href='/tools-auto/auto-oil-fluid-accessories/'>Auto Oil &amp; Fluid Accessories</a></li>
<li><a href='/tools-auto/auto-seat-covers-accessories/'>Auto Seat Covers &amp; Accessories</a></li>
<li><a href='/tools-auto/auto-steering-wheel-covers/'>Auto Steering Wheel Covers</a></li>
<li><a href='/tools-auto/auto-tools/'>Auto Tools</a></li>
<li><a href='/tools-auto/auto-trailer-and-towing/'>Auto Trailer &amp; Towing</a></li>
<li><a href='/tools-auto/auto-tyre-repair/'>Auto Tyre Repair</a></li>
<li><a href='/tools-auto/auto-work-lights-and-torches/'>Auto Work Lights &amp; Torches</a></li>
<li><a href='/tools-auto/air-tools-compressors/'>Compressors &amp; Air Tools</a></li>
<li><a href='/tools-auto/garage-storage-organisation/'>Garage Shelving &amp; Organisation</a></li>
<li><a href='/tools-auto/garden-tools/'>Garden Tools</a></li>
<li><a href='/tools-auto/generators-motors/'>Generators &amp; Motors</a></li>
<li><a href='/tools-auto/hand-tools/'>Hand Tools</a></li>
<li><a href='/tools-auto/ladders/'>Ladders</a></li>
<li><a href='/tools-auto/other-tools/'>Other Tools</a></li>
<li><a href='/tools-auto/power-tools-accessories/'>Power Tools &amp; Accessories</a></li>
<li><a href='/tools-auto/pressure-washers/'>Pressure Washers</a></li>
<li><a href='/tools-auto/tarpaulins-1/'>Tarpaulins</a></li>
<li><a href='/tools-auto/toolboxes-workbenches-belts/'>Toolboxes &amp; Workbenches</a></li>
<li><a href='/tools-auto/water-pumps/'>Water Pumps</a></li>
<li><a href='/tools-auto/welders-accessories/'>Welders &amp; Accessories</a></li>
</ul></li><li class='top'><a href='/toys/'><span>Toys &amp; Games</span></a>
<ul class='mmenu-sub'><li><a href='/toys/art-craft/' class='fi'>Art &amp; Craft</a></li>
<li><a href='/toys/as-seen-on-tv-1/'>As Seen On TV</a></li>
<li><a href='/toys/blasters-soakers/'>Blasters &amp; Soakers</a></li>
<li><a href='/toys/boys-toys/'>Boys Toys</a></li>
<li><a href='/toys/ben-10/'>Boys Toys - Ben 10</a></li>
<li><a href='/toys/hot-wheels/'>Boys Toys - Hot Wheels</a></li>
<li><a href='/toys/construction-building-blocks/'>Construction &amp; Building Blocks</a></li>
<li><a href='/toys/cubby-houses/'>Cubby Houses</a></li>
<li><a href='/toys/dolls-accessories/'>Dolls &amp; Accessories</a></li>
<li><a href='/toys/dolls-barbie/'>Dolls - Barbie</a></li>
<li><a href='/toys/dolls-bratz/'>Dolls - Bratz</a></li>
<li><a href='/toys/dolls-dora-diego/'>Dolls - Dora &amp; Diego</a></li>
<li><a href='/toys/dolls-lalaloopsy/'>Dolls - Lalaloopsy</a></li>
<li><a href='/toys/dolls-monster-high/'>Dolls - Monster High</a></li>
<li><a href='/toys/educational-learning-toys/'>Educational &amp; Learning Toys</a></li>
<li><a href='/toys/games-puzzles/'>Games &amp; Puzzles</a></li>
<li><a href='/toys/girls-toys/'>Girls Toys</a></li>
<li><a href='/toys/infant-preschool/'>Infant &amp; Preschool</a></li>
<li><a href='/toys/fisher-price/'>Infant &amp; Preschool - Fisher Price</a></li>
<li><a href='/toys/leapfrog/'>Infant &amp; Preschool - Leapfrog</a></li>
<li><a href='/toys/little-tikes/'>Infant &amp; Preschool - Little Tikes</a></li>
<li><a href='/toys/thomas-friends/'>Infant &amp; Preschool - Thomas &amp; Friends</a></li>
<li><a href='/toys/vtech/'>Infant &amp; Preschool - vtech</a></li>
<li><a href='/toys/jumping-castles/'>Jumping Castles</a></li>
<li><a href='/toys/kids-decor-furniture/'>Kids Decor &amp; Furniture</a></li>
<li><a href='/toys/music-gear/'>Music Gear</a></li>
<li><a href='/toys/nerf/'>Nerf</a></li>
<li><a href='/toys/outdoor/'>Outdoor Toys</a></li>
<li><a href='/toys/plush/'>Plush</a></li>
<li><a href='/toys/plush-electronic/'>Plush - Electronic</a></li>
<li><a href='/toys/pretend-play/'>Pretend Play</a></li>
<li><a href='/toys/costumes/'>Pretend Play - Costumes</a></li>
<li><a href='/toys/remote-control-toys/'>Remote Control Toys</a></li>
<li><a href='/toys/ride-on-toys/'>Ride Ons &amp; Bikes</a></li>
<li><a href='/toys/scooters-skateboards/'>Scooters &amp; Skateboards</a></li>
<li><a href='/toys/toy-story/'>Toy Story</a></li>
<li><a href='/toys/trampolines-1/'>Trampolines</a></li>
</ul></li><li class='top'><a href='/watches/'><span>Watches</span></a>
<ul class='mmenu-sub'><li><a href='/watches/casio/'>Casio</a></li>
<li><a href='/watches/converse-1/'>Converse</a></li>
<li><a href='/watches/dkny/'>DKNY</a></li>
<li><a href='/watches/emporio-armani/'>Emporio Armani</a></li>
<li><a href='/watches/fossil/'>Fossil</a></li>
<li><a href='/watches/guess-watches/'>GUESS Watches</a></li>
<li><a href='/watches/kids-watches/'>Kids Watches</a></li>
<li><a href='/watches/ladies-watches/'>Ladies Watches</a></li>
<li><a href='/watches/marc-jacobs-1/'>Marc Jacobs</a></li>
<li><a href='/watches/mens-watches/'>Mens Watches</a></li>
<li><a href='/watches/michael-kors-1/'>Michael Kors</a></li>
<li><a href='/watches/nrl/'>NRL</a></li>
<li><a href='/watches/seiko/'>Seiko</a></li>
<li><a href='/watches/swatch/'>Swatch</a></li>
<li><a href='/watches/timex/'>Timex</a></li>
<li><a href='/watches/unisex/'>Unisex</a></li>
</ul></li><li class='top'><a href='/wine-beer/'><span>Wine, Beer, Spirits</span></a>
<ul class='mmenu-sub'><li><a href='/wine-beer/alcohol-testers-1/' class='fi'>Alcohol Testers</a></li>
<li><a href='/wine-beer/beer/'>Beer</a></li>
<li><a href='/wine-beer/red-wine-cabernet-merlot/'>Cabernet Merlot</a></li>
<li><a href='/wine-beer/red-wine-cabernet-sauvignon/'>Cabernet Sauvignon</a></li>
<li><a href='/wine-beer/white-wine-chardonnay/'>Chardonnay</a></li>
<li><a href='/wine-beer/cider/'>Cider</a></li>
<li><a href='/wine-beer/red-wine-merlot/'>Merlot</a></li>
<li><a href='/wine-beer/moscato/'>Moscato</a></li>
<li><a href='/wine-beer/port-dessert-wine/'>Port &amp; Dessert Wine</a></li>
<li><a href='/wine-beer/red-wine/'>Red Wine</a></li>
<li><a href='/wine-beer/red-wine-others/'>Red Wine - Others</a></li>
<li><a href='/wine-beer/red-wine-rose/'>Rose</a></li>
<li><a href='/wine-beer/white-wine-sauvignon-blanc/'>Sauvignon Blanc</a></li>
<li><a href='/wine-beer/white-wine-semillon-sauvignon-blanc/'>Semillon Sauvignon Blanc</a></li>
<li><a href='/wine-beer/red-wine-shiraz/'>Shiraz</a></li>
<li><a href='/wine-beer/red-wine-shiraz-blends/'>Shiraz Blends</a></li>
<li><a href='/wine-beer/sparkling-wine/'>Sparkling Wine</a></li>
<li><a href='/wine-beer/spirits-wine-based-liqueurs/'>Spirits &amp; Wine Based Liqueurs</a></li>
<li><a href='/wine-beer/white-wine/'>White Wine</a></li>
<li><a href='/wine-beer/white-wine-others/'>White Wine - Others</a></li>
<li><a href='/wine-beer/wines-mixed-cases/'>Wines Mixed Cases</a></li>
</ul></li><li class='top'><a href='/clearance-bin/'><span>Clearance Bin</span></a>
<ul class='mmenu-sub'><li><a href='/clearance-bin/home-lifestyle-clearance/' class='fi'>Appliances Clearance</a></li>
<li><a href='/clearance-bin/automotive-tools-clearance/'>Automotive &amp; Tools Clearance</a></li>
<li><a href='/clearance-bin/baby-clearance/'>Baby Clearance</a></li>
<li><a href='/clearance-bin/christmas-lights-clearance/'>Christmas Lights Clearance</a></li>
<li><a href='/clearance-bin/clothing-clearance/'>Clothing Clearance</a></li>
<li><a href='/clearance-bin/electronics-it-clearance/'>Electronics &amp; IT Clearance</a></li>
<li><a href='/clearance-bin/footwear-clearance/'>Footwear Clearance</a></li>
<li><a href='/clearance-bin/furniture-clearance/'>Furniture  Clearance</a></li>
<li><a href='/clearance-bin/gifts-novelties-clearance/'>Gifts &amp; Novelties Clearance</a></li>
<li><a href='/clearance-bin/health-beauty-clearance/'>Health &amp; Beauty Clearance </a></li>
<li><a href='/clearance-bin/handbags-luggage-clearance/'>Homewares Clearance</a></li>
<li><a href='/clearance-bin/manchester-clearance/'>Manchester Clearance</a></li>
<li><a href='/clearance-bin/musical-instruments-clearance/'>Musical Instruments Clearance</a></li>
<li><a href='/clearance-bin/outdoor-clearance/'>Outdoor Clearance</a></li>
<li><a href='/clearance-bin/pet-supplies-clearance/'>Pet Supplies Clearance</a></li>
<li><a href='/clearance-bin/sport-fitness-clearance/'>Sport &amp; Fitness Clearance</a></li>
<li><a href='/clearance-bin/supermarket-deals-clearance/'>Supermarket Deals Clearance</a></li>
<li><a href='/clearance-bin/toys-clearance/'>Toys Clearance</a></li>
<li><a href='/clearance-bin/watches-clearance/'>Watches Clearance</a></li>
<li><a href='/clearance-bin/wine-beer-1/'>Wine &amp; Beer</a></li>
</ul></li></ul>
</div>";
        $encode =   'UTF-8';
        $html   =   preg_replace("/<\/?div>/i", "", $html);  
        $html   =   preg_replace("/<\/?blockquote>/i", "\n", $html);  
        $html   =   preg_replace("/<li class='top'>/i", "\r\n", $html);
        $html   =   preg_replace("/<\/?li>/i", "\t", $html);
        $html   =   preg_replace("/\&nbsp\;/i", " ", $html);  
        $html   =   preg_replace("/\&nbsp/i", " ", $html);  
        $html   =   preg_replace("/\&amp\;/i", "&", $html);  
        $html   =   preg_replace("/\&amp/i", "&", $html);    
        $html   =   preg_replace("/\&lt\;/i", "<", $html);  
        $html   =   preg_replace("/\&lt/i", "<", $html);  
        $html   =   preg_replace("/\&ldquo\;/i", '"', $html);  
        $html   =   preg_replace("/\&ldquo/i", '"', $html);  
        $html   =   preg_replace("/\&lsquo\;/i", "'", $html);  
        $html   =   preg_replace("/\&lsquo/i", "'", $html);  
        $html   =   preg_replace("/\&rsquo\;/i", "'", $html);  
        $html   =   preg_replace("/\&rsquo/i", "'", $html);  
        $html   =   preg_replace("/\&gt\;/i", ">", $html);   
        $html   =   preg_replace("/\&gt/i", ">", $html);   
        $html   =   preg_replace("/\&rdquo\;/i", '"', $html);   
        $html   =   preg_replace("/\&rdquo/i", '"', $html);
        $html = strip_tags($html);  
        $html = html_entity_decode($html, ENT_QUOTES, $encode);  
        $html = preg_replace("/\&\#.*?\;/i", "", $html);    
        $file_name  =   "dealsdirct_category.xls";
        $f          =   fopen("logs/".$file_name, "w+");
        echo $html;
        @fwrite($f,$html);
        @fclose($f);
        die();
    }
    
    function createCreatoryAction(){
        $product_categories_model = new Databases_Tables_ProductCategories();
        $this->product_categories = $product_categories_model->BuildTree();
        $sql    =   'select * from product_categories where parent_id = 0';
        $data   =   $this->db->query($sql);
        if($data){
            $data_all   =   $data->fetchAll();
            $new    =   $this->getCategory($data_all[0],1);
        }
        echo $new;
        /*
        $file_name  =   "b2b_category.xls";
        $f          =   fopen("logs/".$file_name);
        @fwrite($f,$new);
        @fclose($f);
         * 
         */
        die;
    }
    
    function getCategory($catrgory_array,$i){
        switch ($i){
            case 1 :
                $tab    =   ",";
                break;
            case 2 :
                $tab    =   ",,";
                break;
            case 3 :
                $tab    =   ",,,";
                break;
            case 4 :
                $tab    =   ",,,,";
                break;
            default :
                $tab    =   "\t";
                break;
            
        }
        $i++;
        foreach ($this->product_categories as $key => $catrgory){
            if($catrgory['parent_id'] == $catrgory_array['category_id']){
                $new_category_array .=  $catrgory['category_id'].$tab.'"'.$catrgory['category_name'].'"'."\r\n";
                unset($this->product_categories[$key]);
                $new_category_array .=  $this->getCategory($catrgory,$i);
            }
        }
        return $new_category_array;
    }
}
