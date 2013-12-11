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
        print_r($productFilter->getDDProductCategoryInfo($cat_id));
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
    
    function improtDdProductAction(){
        $dd_products_model   =   new Databases_Tables_DdProducts();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $fp = fopen('feed_20131106_121418_crazysales_datafeed.csv', "r");
        while (($data = fgetcsv($fp, 5000, ",")) !== FALSE) {
            $data_array[] = $data;
        }
        fclose($fp);
        foreach($data_array as $da_key => $da_val)
        {
            $product_code   =   $da_val[0];
            $suppliert_sku  =   substr(trim( $da_val[0]), 0, -3);
            $cc_product_info    =   $product_filter_model->getProductInfo($suppliert_sku);
            $dd_products_model->product_id      =   $cc_product_info['product_id'];
            $dd_products_model->product_code    =   rtrim($da_val[0],'"');
            $dd_products_model->product_title   =   rtrim($da_val[1],'"');
            $dd_products_model->brand           =   rtrim($da_val[2],'"');
            $dd_products_model->category_1      =   rtrim($da_val[3],'"');
            $dd_products_model->category_2      =   rtrim($da_val[4],'"');
            $dd_products_model->description     =   rtrim($da_val[5],'"');
            $dd_products_model->rrp             =   rtrim($da_val[6],'"');
            $dd_products_model->sell            =   rtrim($da_val[7],'"');
            $dd_products_model->freight         =   rtrim($da_val[8],'"');
            $dd_products_model->cost            =   rtrim($da_val[9],'"');
            $dd_products_model->weight          =   rtrim($da_val[10],'"');
            $dd_products_model->available       =   rtrim($da_val[11],'"');
            $dd_products_model->stock           =   rtrim($da_val[12],'"');
            $dd_products_model->image_1         =   rtrim($da_val[13],'"');
            $dd_products_model->image_2         =   rtrim($da_val[14],'"');
            $dd_products_model->image_3         =   rtrim($da_val[15],'"');
            $dd_products_model->image_4         =   rtrim($da_val[16],'"');
            $dd_products_model->image_5         =   rtrim($da_val[17],'"');
            $dd_products_model->image_6         =   rtrim($da_val[18],'"');
            $dd_products_model->length          =   rtrim($da_val[19],'"');
            $dd_products_model->width           =   rtrim($da_val[20],'"');
            $dd_products_model->height          =   rtrim($da_val[21],'"');
            $dd_products_model->despatch_pcode  =   rtrim($da_val[22],'"');
            $dd_products_model->courier         =   rtrim($da_val[23],'"');
            $dd_products_model->cc_supplier_sku =   $cc_product_info['supplier_sku'];
            $dd_products_model->cc_price        =   $cc_product_info['supplier_price'];
            $dd_products_model->addDdProduct();
        }
        die;
    }
    
    function processOrderAction(){
        $fp = fopen('crazysales_picking_20131118-112022.csv', "r");
        while (($data = fgetcsv($fp, 5000, ",")) !== FALSE) {
            $data_array[] = $data;
        }
        unset( $data_array[0]);
        fclose($fp);
        $titile_array   =   array("Your Record # (Optional)","Buyer First Name (Required when Pickup N)","Buyer Last Name (Required when Pickup N)","Buyer Company (Optional)","Buyer Address Line 1 (Required when Pickup N)","Buyer Address Line 2 (Optional)","Buyer Suburb (Required when Pickup N)","Buyer State (Required when Pickup N)","Buyer Postal Code (Required when Pickup N)","Buyer Country (Required when Pickup N)","Buyer Phone (Optional)","Supplier Item Code (Required)","Merchant SKU (Optional)","Qty (Required)","Shipping Method (Optional)","Shipping Instruction (Optional)","Comments (Optional)","Merchant Company (Required)","Pick Up (Required)");
        $f_dd_order_new =   @fopen('dd-'.date('Y-m-d').'.csv','w');
        @fputcsv($f_dd_order_new, $titile_array);
        foreach($data_array as $da_key => $da_val)
        {
            $supplier_sku    =   substr(trim($da_val[9]), 0, -3);
            //Validation
            $full_name_array    =   array_values(array_filter(explode(' ', trim($da_val[1]))));
            /*
            $getorders_model->shipping_first_name   =   $full_name_array[0];
            $getorders_model->shipping_last_name    =   $full_name_array[1];
            $getorders_model->shipping_company      =   trim($da_val[18]);
            $getorders_model->merchant_company      =   'Test Company';
            //$getorders_model->merchant_company      =   'DealsDirect';
            $getorders_model->shipping_address_1    =   trim($da_val[3]).' '. trim($da_val[4]);
            $getorders_model->shipping_suburb       =   trim($da_val[5]);
            $getorders_model->shipping_state        =   trim($da_val[6]);
            $getorders_model->shipping_postcode     =   trim($da_val[7]);
            $getorders_model->shipping_country      =   'AU';
            $getorders_model->shipping_phone        =   trim($da_val[8]);
            $getorders_model->supplier_sku          =   $supplier_sku;
            $getorders_model->quantity              =   trim($da_val[11]);
            $getorders_model->operator_id           =   '1';
            $getorders_model->pick_up               =   'N';
             * 
             */
            $post_code  =   trim($da_val[7]);
            if(strlen($post_code)==3){
                $post_code  =   '0'.$post_code;
            }
            $csv_data   =   array(
                trim($da_val[0]),
                $full_name_array[0],
                $full_name_array[1],
                trim($da_val[2]),
                trim($da_val[3]),
                trim($da_val[4]),
                trim($da_val[5]),
                trim($da_val[6]),
                $post_code,
                'AU',
                trim($da_val[8]),
                $supplier_sku,
                trim($da_val[9]),
                trim($da_val[11]),
                '',
                '',
                '',
                'DealsDirect',
                'N'
            );
            @fputcsv($f_dd_order_new, $csv_data);
        }
         fclose($f_dd_order_new);
         die;
    }
    
    function ddOrdersListAction(){
        $dd_order_model =   new Databases_Tables_DdOrders();
        $dd_order_model->order_by   =   ' order_id DESC ';
        $dd_orders      =   $dd_order_model->getDdorders();
        $html   =   '<table width="100%">'
                .'<tr>
                    <td width="5.2%">oNum</td>
                    <td width="5.2%">Buyer_Full_Name</td>
                    <td width="5.2%">Company</td>
                    <td width="5.2%">Address_Line_1</td>
                    <td width="5.2%">Address_Line_2</td>
                    <td width="5.2%">Suburb</td>
                    <td width="5.2%">State</td>
                    <td width="5.2%">Post_Code</td>
                    <td width="5.2%">Phone_Num</td>
                    <td width="5.2%">Product_Code</td>
                    <td width="5.2%">Product_Title</td>
                    <td width="5.2%">Cost</td>
                    <td width="5.2%">Freight</td>
                    <td width="5.2%">Tracking_Number</td>
                    <td width="5.2%">Shipping_Date</td>
                    <td width="5.2%">Courier</td>
                    <td width="5.2%">Action</td>
                 </tr>';
        if($dd_orders){
            foreach ($dd_orders as $dd_order){
                $html   .=  '<tr>';
                $html   .=  '<td width="5.2%">'.$dd_order['o_num'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['buyer_full_name'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['company'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['address_line_1'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['address_line_2'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['suburb'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['state'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['post_code'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['phone_num'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['product_code'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['product_title'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['cost'].'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['freight'].'</td>';
                $tracking_num   =   ($dd_order['tracking_number'])?$dd_order['tracking_number']:$dd_order['error_message'];
                $html   .=  '<td width="5.2%">'.$tracking_num.'</td>';
                $html   .=  '<td width="5.2%">'.$dd_order['shipping_date'].'</td>';
                if($dd_order['status']=='5'){
                    $courier    =   'Cancelled';
                }else{
                    $courier    =   $dd_order['courier'];
                }
                $html   .=  '<td width="5.2%">'.$courier.'</td>';
                if($dd_order['status']=='5'){
                    $action_html    =   '<a href="/plugin/delete-dd-order/order_id/'.$dd_order['order_id'].'" onclick="return confirm('."'Sure to delete order ".$dd_order['o_num']."'".')">Delete</a>';
                }else{
                    $action_html    =   '';
                }
                $html   .=  '<td width="5.2%">'.$action_html.'</td>';
                $html   .=  '</tr>';
            }
        }
        $html   .=  '</table>';
        echo $html;
        die;
    }
    
    function deleteDdOrderAction(){
        $dd_order_model =   new Databases_Tables_DdOrders();
        $params =   $this->_request->getParams();
        if($params['order_id']){
            $dd_order_model->order_id   =   $params['order_id'];
            $dd_order_model->deleteDdOrder();
        }
        $this->_redirect('/plugin/dd-orders-list');
    }
    
    function sentEmailAction(){
        $email_model    =   new Algorithms_Core_Email();
        $email_model->sentEmail();
        die;
    }
    /*
    function updateFinancialAction(){
        $purchase_order_model   =   new Databases_Tables_PurchaseOrder();
        $orders_model       =   new Databases_Joins_GetOrders();
        $orders_model->start_date               =   '2013-11-07';
        $orders_model->end_date                 =   '2013-11-15';
        $orders_model->user_id                  =   8;
        $orders_model->item_statuses            =   array(1,3,4);
        $this->db = Zend_Registry::get("db");
        $sql    =   "SELECT * from purchase_order p left join logs_orders o on o.purchase_order_id=p.purchase_order_id where (p.issue_time >= '2013-11-07 00:00:00') AND (p.issue_time <= '2013-11-12 23:59:59') AND (p.user_id = 8) AND (o.item_status in (1,3,4) )";
        $orders =  $this->db->fetchAll($sql);
        $logs_financial         =   new Databases_Tables_LogsFinancial();
        foreach ($orders as $order){
            $purchase_order_array[$order['purchase_order_id']]  =   $order['purchase_order_id'];
        }
        $purchase_order_ids = implode(',', $purchase_order_array);
        $purchase_order_model->purchase_order_ids    =   $purchase_order_ids;
        $purchase_orders =   $purchase_order_model->GetPurchaseOrder();
        $total  =   0;
        foreach ($purchase_orders as $purchase_order){
            $total +=   $purchase_order['order_amount'];
            $logs_financial->action_type    =   1; //place order
            $logs_financial->user_id        =   8;
            $logs_financial->action_affect  =   2; //deduct
            $logs_financial->action_value   =   $purchase_order['order_amount'];
            // $logs_financial->trans_id = $place_order_return['logs_orders_id'];
            $logs_financial->AddLog();
        }
        die();
    }
    **/
    
    function testGetOrderWsdlAction(){
        $orders_webservice_model    =   new Algorithms_Core_OrderService();
        $orders_webservice_model->OrderIDs      =   array('42383530','42383514','42383555');
        $orders_status_result_array    =   $orders_webservice_model->WebServiceGetOrderStatus();
        print_r($orders_status_result_array);
        die();
    }
    
    function insertDdProductsAction(){
        $f_logs_feeds  =   @fopen("logs/orderslogs/processddorders".date('YmdHis').".txt", "w+");
        $dd_products_model      =   new Databases_Tables_DdProducts();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $dir_array  =   array();
        if (false != ($handle = opendir ( 'dd_feed' ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                if ($file != "." && $file != "..") {
                    $dir_array[$i]  =   $file;
                    $i++;
                }
            }
            closedir ( $handle );
        }
        krsort($dir_array);
        foreach ($dir_array as $file_name){
            
            if (($handle = fopen('dd_feed/'.$file_name, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                    $data_array[] = $data;
                }
                fclose($handle);
            }
            unset($data_array[0]);
            @fwrite($f_logs_feeds, "\r\n".'Processing '.$file_name.':'.date("Y-m-d H:i:s")."\r\n");
            foreach($data_array as $da_key => $da_val)
            {
                $supplier_sku    =   substr(trim($da_val[0]), 0, -3);
                $product_info   =   $product_filter_model->GetSkuPrices($supplier_sku, 8);
                $dd_products_model->product_id      =   $product_info['product_id'];
                $dd_products_model->product_code    =   trim($da_val[0],'"');
                $dd_products_model->product_title   =   trim($da_val[1],'"');
                $dd_products_model->brand           =   trim($da_val[2],'"');
                $dd_products_model->category_1      =   trim($da_val[3],'"');
                $dd_products_model->category_2      =   trim($da_val[4],'"');
                $dd_products_model->description     =   trim($da_val[5],'"');
                $dd_products_model->rrp             =   trim($da_val[6],'"');
                $dd_products_model->sell            =   trim($da_val[7],'"');
                $dd_products_model->freight         =   trim($da_val[8],'"');
                $dd_products_model->cost            =   trim($da_val[9],'"');
                $dd_products_model->weight          =   trim($da_val[10],'"');
                $dd_products_model->available       =   trim($da_val[11],'"');
                $dd_products_model->stock           =   0;
                $dd_products_model->image_1         =   trim($da_val[13],'"');
                $dd_products_model->image_2         =   trim($da_val[14],'"');
                $dd_products_model->image_3         =   trim($da_val[15],'"');
                $dd_products_model->image_4         =   trim($da_val[16],'"');
                $dd_products_model->image_5         =   trim($da_val[17],'"');
                $dd_products_model->image_6         =   trim($da_val[18],'"');
                $dd_products_model->length          =   trim($da_val[19],'"');
                $dd_products_model->width           =   trim($da_val[20],'"');
                $dd_products_model->height          =   trim($da_val[21],'"');
                $dd_products_model->despatch_pcode  =   '3171';
                if($da_val[19] > 105 || $da_val[20] > 105 || $da_val[121] > 105 || $da_val[10] > 32){
                    $dd_products_model->courier         =   trim(3,'"');
                }else{
                    $dd_products_model->courier         =   trim(1,'"');
                }
                $dd_products_model->cc_supplier_sku =   $product_info['supplier_sku'];
                $dd_products_model->cc_price        =   $product_info['supplier_price'];
                $product_id                         =   $dd_products_model->updateDdProduct();
                if($product_id){
                    @fwrite($f_logs_feeds, 'add sku '.$supplier_sku.':'.date("Y-m-d H:i:s")."\r\n");
                }
            }
        }
        die();
    }
}
