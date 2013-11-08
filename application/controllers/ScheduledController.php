<?php

class ScheduledController extends Zend_Controller_Action
{
    function preDispatch() {
        $params_model   =   new Databases_Tables_Params();
        $permit_ip      =   $params_model->GetVal('permit_ip');
        $permit_ip_array    = explode(',', $permit_ip);
        $ip =   $this->getIP();
        if(!in_array($ip, $permit_ip_array)){
            //exit("Invalid Action.");
        }
    }
    function indexAction()
    {
        echo "Invalid Action";
        die;
    }
    
    function placeOrderAction()
    {
        $logs_orders = new Databases_Tables_LogsOrders();
        $logs_orders->PostXmlToPlaceOrder();
        
        die;
    }
    
    /**
     * Refresh Pending Orders With WebService And Create logs
     * get pending orders
     * start loop
     * WebServicePlaceOrder
     * finish loop
     * WebServiceSetOrderStatus
     * update financial table
     * update params table
     * create log file
     */
    function refreshOrdersAction() 
    {
        $getorder_model         =   new Databases_Joins_GetOrders();
        $order_webservice_model =   new Algorithms_Core_OrderService();
        $system_params_model    =   new Databases_Tables_Params();
        $getuser_info_model     =   new Databases_Joins_GetUserInfo();
        $logs_financial         =   new Databases_Tables_LogsFinancial();
        $crazySalesOrderItemTypeArray   =   array();

        $order_api_trying_times     =   $system_params_model->GetVal('order_api_trying_times');
        $logs_path                  =   $system_params_model->GetVal('logs_path');
        $f  =   fopen($logs_path."orderslogs/refreshorders".date('YmdHis').".txt", "w+");
        $logs_contents              =   'Refresh Pending Orders Begin at:'.date("Y-m-d H:i:s").".\n";
        $getorder_model->item_status    =   0;
        $getorder_model->order_api_trying_times     =   $order_api_trying_times;
        $purchase_orders    =   $getorder_model->getPendinglist();
        $purchase_order_model   =   new Databases_Tables_PurchaseOrder();
        $logs_orders_model      =   new Databases_Tables_LogsOrders();
        $order_core_model       =   new Algorithms_Core_Order();

        if(!empty($purchase_orders))
        {
            foreach ($purchase_orders as $purchase_order)
            {
                $crazySalesOrderType        =   new CrazySalesOrderType();
                $moeney_type                =   new MoneyType();
                $order_discount             =   new MoneyType();
                $order_amount_money_type    =   new MoneyType();
                $user_info  =   $getuser_info_model->GetUserInfo($purchase_order['user_id']);
                $crazySalesOrderType->PaymentTypeID          =   $purchase_order['payment_type_id']; 
                $crazySalesOrderType->RetailerAccountEmail   =   $user_info['email'];
                $crazySalesOrderType->ShipFirstName          =   $purchase_order['shipping_first_name'];
                $crazySalesOrderType->ShipAddress_1          =   $purchase_order['shipping_address_1'];
                $crazySalesOrderType->ShipAddress_2          =   $purchase_order['shipping_address_2'];
                $crazySalesOrderType->ShipCity               =   $purchase_order['shipping_suburb'];
                $crazySalesOrderType->ShipState              =   $purchase_order['shipping_state'];
                $crazySalesOrderType->ShipZipCode            =   $purchase_order['shipping_postcode'];
                $crazySalesOrderType->ShipCountryCode        =   $purchase_order['shipping_country'];
                $crazySalesOrderType->ShipPhone              =   $purchase_order['shipping_phone'];
                $crazySalesOrderType->SiteID                 =   6;
                

                $order_amount_money_type->Value    =   round($purchase_order['order_amount'],2);                                  
                $order_discount->Value  =   round($purchase_order['discount_amount'],2);
                if($crazySalesOrderType)
                {
                    //$crazySalesOrderType->OrderDiscount =   $order_discount;
                    $crazySalesOrderType->PointsRate    =   0.00;
                    $crazySalesOrderType->OrderAmount            =   $order_amount_money_type;
                    $moeney_type->Value =   round($purchase_order['shipping_cost'],2);
                    $crazySalesOrderType->ShippingCost           =   $moeney_type;
                    $crazySalesOrderType->BillingAddress_1       =   $user_info['address'];
                    $crazySalesOrderType->BillingZipCode         =   $user_info['post_code'];
                    $crazySalesOrderType->BillingState           =   $user_info['state'];
                    $crazySalesOrderType->BillingCity            =   $user_info['suburb'];
                    $crazySalesOrderType->BillingCompany         =   $user_info['company'];      
                }
                if($purchase_order['pickup']==1)
                {
                    $crazySalesOrderType->ShipMethod    =   'PickUp';
                }else{
                    $crazySalesOrderType->ShipMethod    =   'Shipping';
                }
                $logs_orders_model->purchase_order_id   =   $purchase_order['purchase_order_id'];
                $logs_orders    =   $logs_orders_model->GetLogsOrderList();
                if($logs_orders)
                {
                    foreach ($logs_orders as $logs_order)
                    {
                        $logs_order_ids             =   array();
                        $logs_order_ids[]           =   $logs_order['logs_orders_id'];
                        $crazySalesOrderItemType    =   new CrazySalesOrderItemType();
                        $expected_item_cost =   new MoneyType();
                        $final_item_cost    =   new MoneyType();
                        $final_ship_cost    =   new MoneyType();
                        $ship_cost          =   new MoneyType();
                        $quantityType       =   new QuantityType();
                        $expected_item_cost->Value   =   round($logs_order['expected_item_cost'],2);
                        $crazySalesOrderItemType->ExpectedItemCost   =   $expected_item_cost;
                        $final_item_cost->Value   =   round($logs_order['final_item_cost'],2);
                        $crazySalesOrderItemType->FinalItemCost      =   $final_item_cost;
                        $final_ship_cost->Value   =   round($logs_order['final_ship_cost'],2);
                        $crazySalesOrderItemType->FinalShipCost      =   $final_ship_cost;
                        $ship_cost->Value   =    round($logs_order['ship_cost'],2);
                        $crazySalesOrderItemType->ShipCarrier      =   $logs_order['shipping_courier'];
                        $crazySalesOrderItemType->ShipCost         =   $ship_cost;

                        $quantityType->Value    =   $logs_order['quantity'];
                        $crazySalesOrderItemType->Quantity  =   $quantityType;
                        $crazySalesOrderItemType->ItemSku   =   $logs_order['supplier_sku'];                   
                        $crazySalesOrderItemTypeArray[$logs_order['merchant_ref']][]   =   $crazySalesOrderItemType;
                    }
                }
                $order_webservice_model->crazySalesOrderType  =   $crazySalesOrderType; 
                $order_webservice_model->crazySalesOrderItemType   =   $crazySalesOrderItemTypeArray[$logs_order['merchant_ref']];
                $response_data   =   $order_webservice_model->WebServicePlaceOrder();
                if($response_data['order_number']) 
                {
                    $getorder_model->main_order_id =   $response_data['order_number'];
                    $getorder_model->item_status   =   0;
                    
                    $crazySalesOrderStatusType  =   new CrazySalesOrderStatusType();
                    $crazySalesOrderStatusType1 =   new CrazySalesOrderStatusType1();
                    $crazySalesOrderStatusType->OrderNumber     =   $response_data['order_number'];
                    $crazySalesOrderStatusType->OrderAmount     =   $purchase_order['order_amount'];
                    $crazySalesOrderStatusType->PurchaseOrderId =   $purchase_order['purchase_order_id'];
                    $crazySalesOrderStatusType->PurchaseOrderUserId =   $purchase_order['user_id'];

                    $crazySalesOrderStatusType1->OrderNumber     =   $response_data['order_number'];
                    $crazySalesOrderStatusType1->StatusID        =   3;
                    $crazy_sales_order_status_array1[]           =   $crazySalesOrderStatusType1;

                    $crazy_sales_order_status_array[$response_data['order_number']] =   $crazySalesOrderStatusType;

                }elseif($response_data['MessageType']['Description'])
                {
                    $getorder_model->item_status   =   2;
                    $getorder_model->api_response  =   $response_data['MessageType']['Description'];
                }else
                {
                     $getorder_model->api_response   =  "Time out.";
                }
                $getorder_model->logs_order_ids    =  $logs_order_ids;
                $getorder_model->purchase_order_id   =   $purchase_order['purchase_order_id'];
                $getorder_model->UpdateOrder();

                $logs_contents   .=   'purchase_order_id: '.$purchase_order['purchase_order_id'].', OrderNumber: '.$response_data['order_number'].', DateTime: '.date('Y-m-d H:i:s').', ApiResponse: '.$getorder_model->api_response." \n ";
            }
            $order_webservice_model->crazySalesOrderStatusType =   $crazy_sales_order_status_array1;
            $result_message =   $order_webservice_model->WebServiceSetOrderStatus();
            if($result_message['MessageType'])
            {
                if($result_message['MessageType']['Description']){
                    $message_main_order_id = $order_core_model->ValueAdjustmentReader($result_message['MessageType']['Description']);
                    $purchase_order_model->main_db_order_id =   $message_main_order_id;
                    $purchase_order_info                =   $purchase_order_model->GetPurchaseOrderInMainOrderId();
                    $logs_orders_model->purchase_order_id   =   $purchase_order_info['purchase_order_id'];
                    $logs_orders_model->api_response    =   $result_message['MessageType']['Description'];
                    $logs_orders_model->item_status     =   2;
                    $logs_orders_model->UpdateLogsOrderStatus();
                    $logs_contents   .=   'OrderNumber: '.$message_main_order_id.', OrderStatus: Rejected, DateTime: '.date('Y-m-d H:i:s').' ApiResponse: '.$result_message['MessageType']['Description']." \n ";
                    unset($crazy_sales_order_status_array[$message_main_order_id]);
                }else{
                    foreach ($result_message['MessageType'] as $message_type)
                    {
                        $message_main_order_id = $order_core_model->ValueAdjustmentReader($message_type['Description']);
                        $purchase_order_model->main_db_order_id =   $message_main_order_id;
                        $purchase_order_info                    =   $purchase_order_model->GetPurchaseOrderInMainOrderId();
                        $logs_orders_model->purchase_order_id   =   $purchase_order_info['purchase_order_id'];
                        $logs_orders_model->api_response    =   $message_type['Description'];
                        $logs_orders_model->item_status     =   2;
                        $logs_orders_model->UpdateLogsOrderStatus();
                        $logs_contents   .=   'OrderNumber: '.$message_main_order_id.' OrderStatus: Rejected, DateTime: '.date('Y-m-d H:i:s').' ApiResponse: '.$message_type['Description']." \n ";
                        unset($crazy_sales_order_status_array[$message_main_order_id]);
                    }
                }
            }
            if(!empty($crazy_sales_order_status_array))
            {
                foreach ($crazy_sales_order_status_array as $crazy_sales_order)
                {
                    $purchase_order_model->main_db_order_id =   $crazy_sales_order->OrderNumber;
                    $logs_orders_model->purchase_order_id   =   $crazy_sales_order->PurchaseOrderId;
                    $logs_orders_model->api_response    =   '';
                    $logs_orders_model->item_status     =   1;
                    $logs_contents   .=   'OrderNumber: '.$crazy_sales_order->OrderNumber.' OrderStatus: Approved, DateTime: '.date('Y-m-d H:i:s').', ApiResponse: '.$message_type['Description']." \n ";
                    $logs_orders_model->UpdateLogsOrderStatus();
                    $logs_financial->user_id = $crazy_sales_order->PurchaseOrderUserId;
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $crazy_sales_order->order_amount;
                    // $logs_financial->trans_id = $place_order_return['logs_orders_id'];
                    $logs_financial->AddLog();
                }
            }
        }else{
            $logs_contents  .=  "No Pending Orders.\n";
        }
        $system_params_model->UpdateVal('pending_order_refresh_time',date('Y-m-d H:i:s'));
        @fwrite($f, $logs_contents);
        @fwrite($f,"Refresh Orders Completed at: ".date('Y-m-d H:i:s').".\n");
        @fclose($f);
        die();
    }
    
    /**
     * Get Products From CrazySales with WebServices And Create logs
     * get params table info
     * truncate product table data
     * start loog
     * WebServicesGetProducts
     * insert into prodcuts
     * finish loop
     * update product table param
     * update estimated shipping cost
     * create log file
     */
    function refreshProductsAction()
    {
        $params_model   =   new Databases_Tables_Params();
        $logs_path              =   $params_model->GetVal('logs_path');
        $f  =   @fopen($logs_path."productslogs/refreshproducts".date('YmdHis').".txt", "w+");
        @fwrite($f, 'Refresh Products Begin at:'.date("Y-m-d H:i:s")."\n");
        @fwrite($f, "initialize wdsl start ....\n");
        $product_webservice_model   =   new Algorithms_Core_ProductService();
        @fwrite($f, 'initialize wdsl succeed :'.date("Y-m-d H:i:s")."\n");
        $productFilter_model        =   new Databases_Joins_ProductFilter();
        $data_source                =   $params_model->GetVal("product_info_table");
        $entries_perpage            =   $params_model->GetVal("product_request_qty_per_page");
        $param_postage_api_url      =   $params_model->GetVal('postage_api_url');
        $shipping_courier_model     =   new Databases_Tables_ShippingCourier();
        $supplier_type_model        =   new Databases_Tables_SupplierType();
        $shipping_courier_array     =   array();
        $supplier_type_array        =   array();
        
        $productFilter_model->data_source   =   $data_source;
        $TotalNumberOfEntries   =   '';
        $TotalNumberOfPages     =   '';
        $logs_contents          =   ' ';
        $page_now   =   1;
        $paginationType =   array(
            'EntriesPerPage'   =>   $entries_perpage,
            'PageNumber'       =>   $page_now,
        );
        $product_webservice_model->EntriesPerPage =   $paginationType['EntriesPerPage'];
        @fwrite($f, 'Truncate Product Data : '.date("Y-m-d H:i:s")."\n");
        $productFilter_model->truncateProduct();
        $count  =   array(
            'normal_count'  =>  0,
            'repeat_count'  =>  0,
        );
        $has=1;
       
            do
            {
                set_time_limit(3600);
                $product_webservice_model->PageNumber =   $page_now;
                $product_webservice_model->PaginationType   =   $paginationType;
                try{
                    $reponse_data  =   $product_webservice_model->WebServicesGetProducts();
                }  catch (Zend_Exception $e){
                    $logs_contents  =   ' page:'.$page_now.'/'.$TotalNumberOfPages .'  Faild!  Date:'.date('Y-m-d H:i:s') ."\n";
                    $page_now++;
                    continue;
                }
                
                if(!$TotalNumberOfEntries && !$TotalNumberOfPages){
                    $TotalNumberOfEntries   =   $reponse_data['GetProductsResult']['PaginationResult']['TotalNumberOfEntries'];
                    $TotalNumberOfPages     =   $reponse_data['GetProductsResult']['PaginationResult']['TotalNumberOfPages'];
                }
                if ($has)
                {
                    $has=0;
                    @fwrite($f, 'TotalNumberOfPages : '.$TotalNumberOfPages."\n");
                    @fwrite($f, 'TotalNumberOfEntries : '.$TotalNumberOfEntries."\n");
                    @fwrite($f, 'EntriesPerPage : '.$paginationType['EntriesPerPage']."\n");

                }
                $product_list_data      =   $reponse_data['GetProductsResult']['Products']['CrazySalesProductType'];

                foreach ($product_list_data as $product_data){
                    $productFilter_model->normal_count  =   $count['normal_count'];
                    $productFilter_model->repeat_count  =   $count['repeat_count'];
                    $productFilter_model->product_id    =   $product_data['ProductID'];
                    $productFilter_model->supplier_sku  =   $product_data['SupplierSku'];
                    $productFilter_model->retailer_sku  =   $product_data['RetailerSku'];
                    $productFilter_model->product_name  =   $product_data['ProductName'];
                    $productFilter_model->wholesale_cost    =   $product_data['Cost']['Value'];
                    $productFilter_model->street_price      =   $product_data['StreetPrice']['Value'];
                    $productFilter_model->supplier_price      =   $product_data['SupplierPrice']['Value'];
                    $productFilter_model->estimated_shipping_cost   =   $product_data['EstimatedShippingCost']['Value'];
                    $productFilter_model->estimated_handling_fee    =   $product_data['EstimatedHandlingCost']['Value'];
                    $productFilter_model->quantity_available        =   $product_data['QuantityAvailable']['Value']?$product_data['QuantityAvailable']['Value']:0;
                    $productFilter_model->long_html_description     =   $product_data['Description'];
                    $productFilter_model->brand                     =   $product_data['Brand'];
                    $productFilter_model->manufacturer              =   $product_data['Manufacturer'];
                    $productFilter_model->last_update_date          =   $product_data['LastUpdateDate']['Value'];
                    $productFilter_model->mpn                       =   $product_data['MPN'];
                    $productFilter_model->upc                       =   $product_data['UPC'];
                    $productFilter_model->ean                       =   $product_data['EAN'];
                    $productFilter_model->isbn                      =   $product_data['ISBN'];
                    $productFilter_model->gtin                      =   $product_data['GTIN'];
                    $productFilter_model->country_of_origin         =   $product_data['CountryOfOrigin'];
                    $productFilter_model->catalog                   =   $product_data['Catalog'];
                    $productFilter_model->catalog_start_date        =   $product_data['CatalogStartDate']['Value'];
                    $productFilter_model->catalog_end_date          =   $product_data['CatalogEndDate']['Value'];
                    $productFilter_model->category                  =   $product_data['Category']['CategoryName'];
                    $productFilter_model->category_id               =   $product_data['Category']['CategoryID'];
                    $productFilter_model->cross_sell_skus           =   $product_data['CrossSellSkus'];
                    $productFilter_model->package_weight            =   $product_data['PackageWeight']['Value'];
                    $productFilter_model->package_weight_units      =   $product_data['PackageWeight']['Units'];
                    $productFilter_model->package_length            =   $product_data['PackageDimension']['Length'];
                    $productFilter_model->package_height            =   $product_data['PackageDimension']['Width'];
                    $productFilter_model->package_depth             =   $product_data['PackageDimension']['Depth'];
                    $productFilter_model->package_dimension_units   =   $product_data['PackageDimension']['Units'];
                    $productFilter_model->sc_class             	    =   $product_data['ShippingCourier']['ClassID'];
                    $productFilter_model->shipping_courier          =   $product_data['ShippingCourier']['Name'];
                    $productFilter_model->max_ship_single_box       =   $product_data['MaxShippingSingleBox'];
                    $productFilter_model->length                    =   $product_data['ProductDimension']['Length'];
                    $productFilter_model->height                    =   $product_data['ProductDimension']['Width'];
                    $productFilter_model->depth                     =   $product_data['ProductDimension']['Depth'];
                    $productFilter_model->dimension_units           =   $product_data['ProductDimension']['Units'];
                    $productFilter_model->weight                    =   round($product_data['ProductWeight']['Value'],8);
                    $productFilter_model->weight_units              =   $product_data['ProductWeight']['Units'];
                    $productFilter_model->min_purchase_quantity     =   $product_data['MinPurchaseQuantity'];
                    $productFilter_model->max_purchase_quantity     =   $product_data['MaxPurchaseQuantity'];
                    $productFilter_model->bin_number                =   $product_data['BinNumber'];
                    $productFilter_model->accessory_skus            =   $product_data['AccessorySkus'];
                    $productFilter_model->keywords                  =   $product_data['Keywords'];
                    $productFilter_model->features                  =   $product_data['Features'];
                    $productFilter_model->specification             =   $product_data['Specification'];
                    $productFilter_model->warranty                  =   $product_data['Warranty'];
                    $productFilter_model->discontinue_flag          =   $product_data['DiscontinueFlag']['Value'];
                    $productFilter_model->case_pack_length          =   $product_data['CasePackDimension']['Length'];
                    $productFilter_model->case_pack_height          =   $product_data['CasePackDimension']['Width'];
                    $productFilter_model->case_pack_depth           =   $product_data['CasePackDimension']['Depth'];
                    $productFilter_model->case_pack_units           =   $product_data['CasePackDimension']['Units'];
                    $productFilter_model->case_pack_quantity        =   $product_data['CasePackQuantity']['Value'];
                    if($product_data['ProductImages']['CrazySalesProductPictureType']['Path']){
                        $productFilter_model->imageURL0             =   @$product_data['ProductImages']['CrazySalesProductPictureType']['Path'];
                        $productFilter_model->imageURL1             =   '';
                        $productFilter_model->imageURL2             =   '';
                        $productFilter_model->imageURL3             =   '';
                        $productFilter_model->imageURL4             =   '';
                        $productFilter_model->imageURL5             =   '';
                    }else{
                        foreach ($product_data['ProductImages']['CrazySalesProductPictureType'] as $picture_data){
                            if($picture_data['DefalutFlag']==1){
                                $productFilter_model->imageURL0     =   $picture_data['Path'];
                                break;
                            }
                        }
                        if(!$productFilter_model->imageURL0){
                            $productFilter_model->imageURL0         =   @$product_data['ProductImages']['CrazySalesProductPictureType'][0]['Path'];
                        }
                        $productFilter_model->imageURL1             =   @$product_data['ProductImages']['CrazySalesProductPictureType'][1]['Path'];
                        $productFilter_model->imageURL2             =   @$product_data['ProductImages']['CrazySalesProductPictureType'][2]['Path'];
                        $productFilter_model->imageURL3             =   @$product_data['ProductImages']['CrazySalesProductPictureType'][3]['Path'];
                        $productFilter_model->imageURL4             =   @$product_data['ProductImages']['CrazySalesProductPictureType'][4]['Path'];
                        $productFilter_model->imageURL5             =   @$product_data['ProductImages']['CrazySalesProductPictureType'][5]['Path'];
                    }
                    $productFilter_model->product_code_type         =   $product_data['ProductCodeType'];
                    if($product_data['Category']['CategoryName'] && $product_data['Category']['CategoryID']){
                        $count  =    $productFilter_model->AddProduct();
                    }else{
                        $logs_contents  = " >>Insert Product Error SKU: ".$product_data['SupplierSku']." CategoryName: ".$product_data['Category']['CategoryName']." CategoryID: ".$product_data['Category']['CategoryID']."\n";
                        @fwrite($f, $logs_contents);
                    }

                    $shipping_courier_array[$product_data['ShippingCourier']['ClassID']]['sc_class']           =   $product_data['ShippingCourier']['ClassID'];
                    $shipping_courier_array[$product_data['ShippingCourier']['ClassID']]['shipping_courier']   =   $product_data['ShippingCourier']['Name'];
                    $supplier_type_array[$product_data['CountryOfOrigin']]['supplier_type'] =   $product_data['CountryOfOrigin'];
                }
                $logs_contents  =   ' page:'.$page_now.'/'.$TotalNumberOfPages .'  succeed!  Date:'.date('Y-m-d H:i:s') ."\n";
                @fwrite($f, $logs_contents);
                $page_now++;

            }while($page_now <= $TotalNumberOfPages);
            $page_now--;
            if($page_now == $TotalNumberOfPages)
            {
                if($data_source == '1')
                {
                    $params_model->UpdateVal('product_info_table', '2');
                }else
                {
                    $params_model->UpdateVal('product_info_table', '1');
                }
                $params_model->UpdateVal('product_info_table_refresh_time',date('Y-m-d H:i:s'));
            }
            @fwrite($f, "Refresh Products normal:".$count['normal_count'] ."  Product Repeat:".$count['repeat_count']."\n");
        
            $products_all       =   $productFilter_model->getProductAll();
            if($products_all)
            {
                $logs_postage   =   '';
                @fwrite($f, 'Update Estimated Shipping Cost Start : '.date("Y-m-d H:i:s")."\n");
                foreach ($products_all as $product)
                {
                    $postage_api_url    =   $param_postage_api_url.'?pid='.$product['product_id'].'&zip=4270&qty=1';
                    $result =   $productFilter_model->updateEstimatedShippingCost($postage_api_url,$product['product_id']);
                }
                @fwrite($f, 'Update Estimated Shipping Cost End : '.date("Y-m-d H:i:s")."\n");
            }
            if($shipping_courier_array){
                @fwrite($f, 'Update Shipping Courier Data Start : '.date("Y-m-d H:i:s")."\n");
                $shipping_courier_model->shipping_courier_array =   $shipping_courier_array;
                $shipping_courier_model->AddShippingCourier();
            }
            
            if($supplier_type_array){
                @fwrite($f, 'Update Supplier Type Data Start : '.date("Y-m-d H:i:s")."\n");
                $supplier_type_model->supplier_type_array       =   $supplier_type_array;
                $supplier_type_model->AddSupplierAType();
            }
            
            @fwrite($f, $logs_postage);
            @fwrite($f, "Refresh Products Completed.\n");
            @fclose($f);
        die();
    }
    
    /**
     * Get Categories Data From Crazysales Database with WebServices
     * get params
     * truncate category
     * start loog
     * WebServicesGetCategories
     * insert into category
     * finish loop
     * update category refresh params
     * create log file
     */
    function refreshCategoriesAction()
    {
        $category_webservice_model  =   new Algorithms_Core_CategoryService();
        $params_model               =   new Databases_Tables_Params();
        $category_model             =   new Databases_Tables_ProductCategories();
        $entries_perpage        =   $params_model->GetVal("product_request_qty_per_page");
        $logs_path              =   $params_model->GetVal('logs_path');
        $f  =   @fopen($logs_path."categorieslogs/refreshcategories".date('YmdHis').".txt", "w+");
        @fwrite($f, 'Refresh Catrgories Begin at:'.date("Y-m-d H:i:s")."\n");
        $logs_contents  =   '';
        $page_now   =   1;
        $paginationType =   array(
            'EntriesPerPage'   =>   $entries_perpage,
            'PageNumber'       =>   $page_now,
        );
        $category_model->truncateCategory();
        $category_model->category_id    =  1;
        $category_model->category_name  =  'ROOT';
        $category_model->parent_id      =   '';
        $category_webservice_model->EntriesPerPage =   300;
        $category_model->addCategory();
        $category_num   =   1;
        do{
            $category_webservice_model->PageNumber =   $page_now;
            $category_webservice_model->PaginationType   =   $paginationType;
            $reponse_data  =   $category_webservice_model->WebServicesGetCategories();
            if(!empty($reponse_data)){
                $category_list_data      =   $reponse_data['GetCategoryResult']['Categories']['CrazySalesCategoryType'];
                foreach ($category_list_data as $category_data){
                    $category_model->category_id    =   $category_data['CategoryID'];
                    $category_model->category_name  =   $category_data['CategoryName'];
                    $category_model->parent_id      =   $category_data['ParentID'];
                    $category_model->addCategory();
                    $category_num++;
                    @fwrite($f,' CategoryID:'.$category_data['CategoryID'].' , CategoryName:'.$category_data['CategoryName'].' ,                 Date:'.date('Y-m-d H:i:s')."\n");
                }
            }  else {
                break;
            }
            $page_now++;
        }while($page_now<=10);
        
        $params_model->UpdateVal('product_categories_table_refresh_time',date('Y-m-d H:i:s'));
        @fwrite($f, $logs_contents);
        @fwrite($f,"Refresh Categories Completed.\n");
        @fclose($f);
        die();
    }
    
    
    function paypalCheckAction()
    {
        if($_POST)
        {
            foreach($_POST as $key => $value){ 
                $value = urlencode (stripslashes($value)); 
                $req.= "&$key=$value" ;    
            } 
            $paypal_log_model   =   new Databases_Tables_PaypalLogs();
            $paypal_log_model->user_id  =   '';
            $paypal_log_model->params   =   $req;
            $paypal_log_model->errors   =   '';
            $paypal_log_model->AddParams();
            echo 1;
        }else 
        {
            echo 0;
        }
        die;
    }
    
    /**
     * Check The Post Data from Paypal And Recharge For The Merchant
     * get post data
     * fsockopen paypal
     * start loop
     * update financial table
     * finish loop
     */
    function paypalNotifyAction ()
    {
        $system_params_model    =   new Databases_Tables_Params();        
        $paypal_url         =   $system_params_model->GetVal('paypal_url');
        
        $req = 'cmd=_notify-validate'; 
        foreach($_POST as $key => $value){ 
            $value = urlencode (stripslashes($value)); 
            $req.= "&$key=$value" ;    
        }  
        // post back to PayPal system to validate           
        $header.= "POST /cgi-bin/webscr HTTP/1.0\r\n" ; 
        $header.=  "Host: ".$paypal_url."\r\n" ; 
        $header.= "Content-Type:application/x-www-form-urlencoded\r\n" ; 
        $header.= "Content-Length:" .strlen($req)."\r\n\r\n";   

        $fp = fsockopen('ssl://'.$paypal_url, 443, $errno, $errstr, 30);    

        $user_id =  $this->_request->getParam('userid');
        $txn_id = $_POST['txn_id']; 
        $mc_gross = $_POST['mc_gross'];
       
        if(!$fp){ 
             
        }else{  
            fputs($fp, $header.$req); 
            while (!feof($fp)){ 
                    $logs_financial = new Databases_Tables_LogsFinancial();
                    $res = fgets($fp, 1024);       
                    if (strcmp($res, "VERIFIED") == 0) { 
                        
                        $logs_financial->user_id        =   $user_id;
                        $logs_financial->action_type    =   2; //Adjustment
                        $logs_financial->action_affect  =   1; //Recharge
                        $logs_financial->action_value   =   $mc_gross;
                        $logs_financial->trans_id       =   $txn_id;
                        $logs_financial->AddLog();
                    }   
                    else if (strcmp ($res, "INVALID") == 0) {
                        
                    }   
                }   
                fclose ($fp);   
            } 
            
           $paypal_log_model   =   new Databases_Tables_PaypalLogs();
            $paypal_log_model->user_id  =   $user_id;
            $paypal_log_model->params   =   $req;
            $paypal_log_model->errors   =   $res;
            $paypal_log_model->AddParams();
        die;
    }
    
    function bpayCheckAction()
    {
        if($_POST)
        {
            $user_model =   new Databases_Joins_GetUserInfo();
            $logs_financial = new Databases_Tables_LogsFinancial();
            $bpay_ref   =   $_POST['Customer_reference_number'];
            $user_info  =   $user_model->GetUserId($bpay_ref);
            $user_id    =   $user_info['user_id'];
            $logs_financial->user_id        =   $user_id;
            $logs_financial->action_type    =   2; //Adjustment
            $logs_financial->action_affect  =   1; //Recharge
            $logs_financial->action_value   =   $_POST['Amount'];
            $logs_financial->AddLog();
            foreach ($_POST as $key => $value){
                $value  =   stripslashes($value); 
                $req    .=  "&$key=$value" ;
            }
            $logs_bpay_model   =   new Databases_Tables_LogsBpay();
            $logs_bpay_model->params   =   $req;
            $logs_bpay_model->add_time = date('Y-m-d H:i:s');
            $logs_bpay_model->AddParams();
            echo 1;
        }else 
        {
            echo 0;
        }
        die;
    }
    
    /**
     * Update Products' Postage
     */
    function refreshProductsPostAction()
    {
            $params_model           =   new Databases_Tables_Params();
            $productFilter_model    =   new Databases_Joins_ProductFilter();
            $param_postage_api_url    =   $params_model->GetVal('postage_api_url');
            $logs_path              =   $params_model->GetVal('logs_path');
            $products_all       =   $productFilter_model->getProductAll();
            if($products_all)
            {
                $f_psotage  =   @fopen($logs_path."productslogs/refreshpostage".date('YmdHis').".txt", "w+");
                $logs_postage   =   '';
                foreach ($products_all as $product)
                {
                    $postage_api_url    =   $param_postage_api_url.'?pid='.$product['product_id'].'&zip=4270&qty=1';
                    $result =   $productFilter_model->updateEstimatedShippingCost($postage_api_url,$product['product_id']);
                    if($result){
                        $logs_postage   .=   'product_id:'.$product['product_id']." sku:".$product['supplier_sku'].' update estimated_shipping_cost:'.$result."\r\n";
                    }else{
                        $logs_postage   .=   'product_id:'.$product['product_id']." sku:".$product['supplier_sku']." update estimated_shipping_cost faild\r\n";
                    }
                }
                @fwrite($f_psotage, $logs_postage);
                @fclose($f_psotage);
            }
            die('update success');
      }
    function getIP()
    {
        $ip =   '';
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        return $ip;
    }
    
    /**
     * Create Merchants' Feed
     * get merchant list
     * start loop
     * refresh merchant feed
     * finish loop
     * create log file
     */
    function refreshFeedsAction()
    {
        $user_model =   new Databases_Joins_GetUserInfo();
        $params_model           =   new Databases_Tables_Params();
        $logs_feeds =   '';
        $user_list  =   $user_model->GetUserList(2,1);
        $logs_path              =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."feedslogs/refreshfeeds".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Refresh Feeds Begin at:'.date("Y-m-d H:i:s")."\n");
        if($user_list){
            $model = new Algorithms_Core_Feed();            
            foreach($user_list as $user){
                if($user['user_id']==8){
                    die;
                }
                $model->user_id_array = array($user['user_id']);
                $result = $model->Publish();
                $logs_feeds   .=   ' Generate Feed:'.$result."\n";
            }
            @fwrite($f_logs_feeds, $logs_feeds);
            @fclose($f_logs_feeds);
        }
        $params_model->UpdateVal('merchant_feed_refresh_time',date('Y-m-d H:i:s'));
        die('Refresh Feeds Complete.');
    }
    /**
     * Process Order Of Dealsdirect
     */
    function processDdOrdersAction()
    {
        $params_model           =   new Databases_Tables_Params();
        $dd_orders_model        =   new Databases_Tables_DdOrders();
        $logs_path              =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."feedslogs/processddorders".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Process DD Orders Begin at:'.date("Y-m-d H:i:s")."\r\n");
        $merchant_ftp_array     =   array(
            'ftp_host'      =>  'interface.dealsdirect.com.au',
            'ftp_port'      =>  '21',
            'ftp_user'      =>  'tp_crazysales',
            'ftp_pass'      =>  '3Ws5maLm',
            'order_path'    =>  'incoming/orders/',
        );
        $new_order_file_name    =   '';
        $local_order_path       =   'DD_orders/';
        $ftp                    =   new Algorithms_Core_Ftp($merchant_ftp_array['ftp_host'], $merchant_ftp_array['ftp_port'], $merchant_ftp_array['ftp_user'], $merchant_ftp_array['ftp_pass']);
        @fwrite($f_logs_feeds, 'Download CSV file at:'.date("Y-m-d H:i:s")."\r\n");
        $new_order_file_name    =   $ftp->getNewestFile($merchant_ftp_array['order_path']);
        $local_order_path       .=  $new_order_file_name;
        $download_order_path    =   $merchant_ftp_array['order_path'].$new_order_file_name;
        $down_result            =   $ftp->copy_file($download_order_path, $local_order_path);
        /*
        $local_order_path       =   'DD_orders/crazysales_picking_20131007-111752.csv';
        $down_result            =   TRUE;
        **/
        if($down_result){
            $product_filter_model   =   new Databases_Joins_ProductFilter();
            $getorders_model        =   new Databases_Joins_GetOrders();
            $group_instance_balance_array = array();
            $data_array = array();
            $merchant_ref_pool = array();
            @fwrite($f_logs_feeds, 'Process Orders Begin at:'.date("Y-m-d H:i:s")."\r\n");
            if (($handle = fopen($local_order_path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                    $data_array[] = $data;
                }
                fclose($handle);
                if(!empty($data_array))
                {
                    unset($data_array[0]);
                    foreach($data_array as $da_key => $da_val)
                    {
                        $supplier_sku    =   substr(trim($da_val[9]), 0, -3);
                        @fwrite($f_logs_feeds, 'Process Orders:'.$da_val[0].' Begin at:'.date("Y-m-d H:i:s")."\r\n");
                        //Validation
                        $full_name_array    = explode(' ', trim($da_val[1]));
                        $getorders_model->shipping_first_name   =   $full_name_array[0];
                        $getorders_model->shipping_last_name    =   $full_name_array[1];
                        $getorders_model->shipping_company      =   trim($da_val[18]);
                        //$getorders_model->merchant_company      =   'Test Company';
                        $getorders_model->merchant_company      =   'DealsDirect';
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
                        $getorders_model->group_instance_balance_array = $group_instance_balance_array;
                        $params_array   =   array(
                            'free_shipping' => 1
                        );
                        $getorders_model->params_array          =   $params_array;
                        /**
                         * @var $getorders_model Databases_Joins_GetOrders
                         * @todo Check Order 
                         */
                        $check_result = $getorders_model->PlaceOrderCheck();
                        if("Y" == $check_result[1]) //passed the validation
                        {
                            $order_amount = $check_result['order_amount'];
                            $instant_balance = $check_result['instant_balance'];
                            $user_id = $check_result['user_id'];

                            //update instant balance
                            $group_instance_balance_array[$user_id] = $instant_balance;
                            //Insert Into Orders
                            $getorders_model->merchant_ref          =   $da_val[0];
                            $getorders_model->order_amount          =   $order_amount;
                            $getorders_model->user_id = $user_id;
                            $getorders_model->ip = $ip;
                            $getorders_model->shipping_first_name   =   $full_name_array[0];
                            $getorders_model->shipping_last_name    =   $full_name_array[1];
                            $getorders_model->shipping_company      =   trim($da_val[18]);
                            $getorders_model->shipping_address_1    =   trim($da_val[3]).' '. trim($da_val[4]);
                            $getorders_model->shipping_address_2    =   '';
                            $getorders_model->shipping_suburb       =   trim($da_val[5]);
                            $getorders_model->shipping_state        =   trim($da_val[6]);
                            $getorders_model->shipping_postcode     =   trim($da_val[7]);
                            $getorders_model->shipping_country      =   'AU';
                            $getorders_model->shipping_phone        =   trim($da_val[8]);
                            $getorders_model->shipping_fax          =   '';
                            $getorders_model->supplier_sku          =   $supplier_sku;
                            $getorders_model->merchant_sku          =   trim($da_val[9]);
                            $getorders_model->quantity              =   trim($da_val[11]);
                            $getorders_model->shipping_method       =   '';
                            $getorders_model->shipping_instruction  =   '';
                            $getorders_model->serial_no             =   '';
                            $getorders_model->comments              =   '';
                            $getorders_model->pick_up               =   'N';
                            $getorders_model->merchant_ref_pool     =   $merchant_ref_pool;
                            $getorders_model->discount_amount       =   round($check_result['discount_amount'],2);
                            $getorders_model->shipping_cost         =   round($check_result['shipping_cost'],2);

                            $sku_prices_info    =   $product_filter_model->GetSkuPrices($supplier_sku, $user_id);
                            
                            $getorders_model->expected_item_cost    =   round($sku_prices_info['supplier_price'],2);
                            $getorders_model->final_item_cost       =   round($sku_prices_info['supplier_price'],2);
                            $getorders_model->final_ship_cost       =   round($check_result['shipping_cost'],2);
                            $getorders_model->ship_cost             =   round($check_result['shipping_cost'],2);
                            $getorders_model->payment_type_id       =   9;
                            $getorders_model->item_amount           =   round($sku_prices_info['supplier_price'],2) + round($check_result['shipping_cost'],2);
                            $getorders_model->shipping_courier      =   trim($sku_prices_info['shipping_courier']);
                            $getorders_model->sc_class              =   trim($sku_prices_info['sc_class']);
                            /**
                             * @todo PlaceOrder
                             */
                            $place_order_return = $getorders_model->PlaceOrder(); // Transaction ID for financial table
                            
                            /**
                             * @todo Add DD order
                             */
                            
                            $dd_orders_model->b2b_order_id      =   $place_order_return['purchase_order_id'];
                            $dd_orders_model->o_num             =   sprintf('%1.0f', $da_val[0]);
                            $dd_orders_model->buyer_full_name   =   trim($da_val[1]);
                            $dd_orders_model->company           =   trim($da_val[2]);
                            $dd_orders_model->address_line_1    =   trim($da_val[3]);
                            $dd_orders_model->address_line_2    =   trim($da_val[4]);
                            $dd_orders_model->suburb            =   trim($da_val[5]);
                            $dd_orders_model->state             =   trim($da_val[6]);
                            $dd_orders_model->post_code         =   trim($da_val[7]);
                            $dd_orders_model->phone_num         =   trim($da_val[8]);
                            $dd_orders_model->product_code      =   trim($da_val[9]);
                            $dd_orders_model->product_title     =   trim($da_val[10]);
                            $dd_orders_model->qty               =   trim($da_val[11]);
                            $dd_orders_model->cart_id           =   trim($da_val[12]);
                            $dd_orders_model->ref_num           =   trim($da_val[13]);
                            $dd_orders_model->cost              =   trim($da_val[14]);
                            $dd_orders_model->freight           =   trim($da_val[15]);
                            $dd_orders_model->addDdOrder();
                            //update merchant ref pool
                            $merchant_ref_pool = $place_order_return['merchant_ref_pool'];
                        }else{
                            @fwrite($f_logs_feeds, $check_result[2].' at:'.date("Y-m-d H:i:s")."\r\n");
                        }
                    }
                }
                $purchase_order_ids =   implode(',',$merchant_ref_pool);
                $operate_orders_model   =   new Databases_Joins_OperateOrders();
                $operate_orders_model->purchase_order_ids   =   $purchase_order_ids;
                $result = $operate_orders_model->PlaceOrder();
                if($result['orders']){
                    foreach ($result['orders'] as $key => $order){
                        $dd_orders_model->b2b_order_id  =   $order['purchase_order_id'];
                        $dd_orders_model->cc_order_id   =   $order['main_order_id'];
                        $dd_orders_model->updateDdOrderCcOrderID();
                    }
                }
            }
            
        }else{
            @fwrite($f_logs_feeds, "Download $new_order_file_name Faild at: ".date("Y-m-d H:i:s")."\r\n");
        }
        @fwrite($f_logs_feeds, "Process Orders Complete at: ".date("Y-m-d H:i:s")."\r\n");
        @fclose($f_logs_feeds);
        die('Process Orders Complete.');
        
    }
    
    function updateDdOrdersAction()
    {
        $user_ids  =   array('8');
        $time_now   = time();
        $time_24_before =   $time_now   -   86400;
        $start_time     =   date('Y-m-d', $time_24_before);
        $orders_model   =   new Databases_Joins_GetOrders();
        $dd_order_model =   new Databases_Tables_DdOrders();
        $params_model   =   new Databases_Tables_Params();
        $orders_webservice_model    =   new Algorithms_Core_OrderService();
        $feed_model                 =   new Algorithms_Core_Feed();
        //$orders_model->start_date   =   $start_time;
        //$orders_model->limit        =   2;
        $orders_model->item_status  =   2;
        $logs_path     =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."feedslogs/updateddorders".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Update DD Orders Begin at:'.date("Y-m-d-H:i:s")."\r\n");
        $dd_order_new_filename  =   'crazysales_shipping_'.date('Ymd-His').'.csv';
        $dd_order_new_path  =   'DD_orders_new/';
        $f_dd_order_new =   @fopen($dd_order_new_path.$dd_order_new_filename,'w');
        $titile_array   =   array(' oNum', ' Buyer_Full_Name', ' Company', ' Address_Line_1', ' Address_Line_2', ' Suburb', ' State', ' Post_Code', ' Phone_Num', ' Product_Code', ' Product_Title', ' Qty', ' Cart_ID', ' Ref_Num', ' Cost', ' Freight', ' Tracking_Number', ' Shipping_Date', ' Courier');
        @fputcsv($f_dd_order_new, $titile_array);
        if($user_ids && is_array($user_ids)){
            foreach($user_ids as $user_id){
                $orders_model->user_id  =   $user_id;
                $user_orders    =   $orders_model->PushList();
                if($user_orders && is_array($user_orders)){
                    foreach ($user_orders as $order){
                        $order_ids[$order['purchase_order_id']]  =   $order['main_db_order_id'];
                    }
                    $OrderStatus    =   $order_ids;
                    if($order_ids && is_array($order_ids)){
                        $orders_webservice_model->OrderIDs      =   $order_ids;
                        $orders_webservice_model->OrderStatus   =   $OrderStatus;
                        $orders_status_result_array    =   $orders_webservice_model->WebServiceGetOrderStatus();
                        $orders_info_result_array      =   $orders_webservice_model->WebServiceGetOrderInfo();
                        if($orders_status_result_array['MessageType']){
                            foreach ($orders_status_result_array['MessageType'] as $message_type){
                                @fwrite($f_logs_feeds, $message_type['Description'].$message_type['Created']."\r\n");
                            }
                        }
                        if($orders_status_result_array['OrderStatus']['CrazySalesOrderStatusType']){
                            foreach ($orders_status_result_array['OrderStatus']['CrazySalesOrderStatusType'] as $order_status_info){
                                if($order_status_info['StatusID']==4 && $order_status_info['Status']=='Sent'){
                                    $orders_sent_array[$order_status_info['OrderNumber']]   =   $order_status_info['OrderNumber'];
                                }
                            }
                        }
                        $update_success_data    =   array();
                        @fwrite($f_logs_feeds, "Update Order data at: ".date("Y-m-d H:i:s")."\r\n");
                        if($orders_info_result_array['Orders']['CrazySalesOrderType']){
                            foreach ($orders_info_result_array['Orders']['CrazySalesOrderType'] as $orders_info){
                                if(in_array($orders_info['OrderNumber'], $orders_sent_array)){
                                    if($orders_info['OrderItems']['CrazySalesOrderItemType']){
                                        foreach ($orders_info['OrderItems']['CrazySalesOrderItemType'] as $order_item){
                                            $dd_order_model->cc_order_id        =   $order_item['OrderNumber'];
                                            $dd_order_model->product_code       =   $order_item['ItemSku'].'-TP';
                                            $dd_order_model->tracking_number    =   $order_item['TrackingNumber'];
                                            $dd_order_model->shipping_date      =   $order_item['ShipDate']['Value'];
                                            $dd_order_model->courier            =   $order_item['ShipCarrier'];
                                            $update_result   =   $dd_order_model->updateDdorder();
                                            if($update_result){
                                                $update_success_data[$order_item['OrderNumber']]['product_code']    =   $order_item['ItemSku'].'-TP';
                                                @fwrite($f_logs_feeds, $update_result."\r\n");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        //get the data of update success and create a new csv file and upload
                        if($update_success_data){
                            @fwrite($f_logs_feeds, "Create csv file and upload at: ".date("Y-m-d H:i:s")."\r\n");
                            foreach ($update_success_data as $order_number => $order_data){
                                $dd_order_model->cc_order_id   =   $order_number;
                                $dd_order_model->product_code   =   $order_data['product_code'];
                                $result =   $dd_order_model->getDdOrderInfo();
                                if($result){
                                    $order_upload_data  =   array(
                                        'oNum'              =>  $result['o_num'],
                                        'Buyer_Full_Name'   =>  $result['buyer_full_name'],
                                        'Company'           =>  $result['company'],
                                        'Address_Line_1'    =>  $result['address_line_1'],
                                        'Address_Line_2'    =>  $result['address_line_2'],
                                        'Suburb'            =>  $result['suburb'],
                                        'State'             =>  $result['state'],
                                        'Post_Code'         =>  $result['post_code'],
                                        'Phone_Num'         =>  $result['phone_num'],
                                        'Product_Code'      =>  $result['product_code'],
                                        'Product_Title'     =>  $result['product_title'],
                                        'Qty'               =>  $result['qty'],
                                        'Cart_ID'           =>  $result['cart_id'],
                                        'Ref_Num'           =>  $result['ref_num'],
                                        'Cost'              =>  $result['cost'],
                                        'Freight'           =>  $result['freight'],
                                        'Tracking_Number'   =>  $result['tracking_number'],
                                        'Shipping_Date'     =>  $result['shipping_date'],
                                        'Courier'           =>  $result['courier'],
                                    );
                                    @fputcsv($f_dd_order_new, $order_upload_data);
                                    
                                }
                            }
                            $feed_model->uploadFtpFile(array($dd_order_new_filename), 'shipping');
                        }else{
                            @fwrite($f_logs_feeds, "No csv file upload at: ".date("Y-m-d H:i:s")."\r\n");
                        }
                    }
                    unset($order_ids);
                }else{
                    @fwrite($f_logs_feeds, "No Order to Update at: ".date("Y-m-d H:i:s")."\r\n");
                }
            }
        }
        @fwrite($f_logs_feeds, "Update Orders Complete at: ".date("Y-m-d H:i:s")."\r\n");
        @fclose($f_logs_feeds);
        die();
    }
    
    function updateApprovedOrdersAction(){
        $orders_model   =   new Databases_Joins_GetOrders();
        $params_model   =   new Databases_Tables_Params();
        $orders_webservice_model    =   new Algorithms_Core_OrderService();
        $logs_path     =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."orderslogs/update-orders".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Update Approved Orders Begin at:'.date("Y-m-d-H:i:s")."\r\n");
        $time_now   = time();
        $time           =   strtotime('-6 day', $time_now);//a week
        $orders_model->item_status  =   1;
        $orders_model->start_date   =   date('Y-m-d', $time);  
        $orders_model->end_date     =   date('Y-m-d', $time_now);
        @fwrite($f_logs_feeds, 'Get Approved Orders Begin at:'.date("Y-m-d-H:i:s")."\r\n");
        $user_orders    =   $orders_model->PushList();
        if($user_orders && is_array($user_orders)){
            foreach ($user_orders as $order){
                $order_ids[$order['purchase_order_id']]  =   $order['main_db_order_id'];
            }
            if($order_ids && is_array($order_ids)){
                @fwrite($f_logs_feeds, 'Get Orders Status With WSDL Begin at:'.date("Y-m-d-H:i:s")."\r\n");
                $orders_webservice_model->OrderIDs      =   array_values($order_ids);
                $orders_webservice_model->OrderStatus   =   array_values($order_ids);
                $orders_status_result_array    =   $orders_webservice_model->WebServiceGetOrderStatus();
                $orders_info_result_array      =   $orders_webservice_model->WebServiceGetOrderInfo();
                if($orders_status_result_array['MessageType']){
                    foreach ($orders_status_result_array['MessageType'] as $message_type){
                        @fwrite($f_logs_feeds, $message_type['Description'].$message_type['Created']."\r\n");
                    }
                }
                if($orders_status_result_array['OrderStatus']['CrazySalesOrderStatusType']){
                    foreach ($orders_status_result_array['OrderStatus']['CrazySalesOrderStatusType'] as $order_status_info){
                        $orders_status_array[$order_status_info['OrderNumber']]   =   $order_status_info['StatusID'];
                    }
                }
                @fwrite($f_logs_feeds, "Update Order data at: ".date("Y-m-d H:i:s")."\r\n");
                if($orders_info_result_array['Orders']['CrazySalesOrderType']){
                    foreach ($orders_info_result_array['Orders']['CrazySalesOrderType'] as $orders_info){
                            if($orders_info['OrderItems']['CrazySalesOrderItemType']){
                                foreach ($orders_info['OrderItems']['CrazySalesOrderItemType'] as $order_item){
                                    @fwrite($f_logs_feeds, 'Upadte '.$order_item['OrderNumber'].' at:'.date("Y-m-d-H:i:s")."\r\n");
                                    $orders_model->main_order_id        =   $order_item['OrderNumber'];
                                    $orders_model->supplier_sku         =   $order_item['ItemSku'];
                                    $orders_model->tracking_number      =   $order_item['TrackingNumber'];
                                    $orders_model->shipping_date        =   $order_item['ShipDate']['Value'];
                                    $orders_model->shipping_courier     =   $order_item['ShipCarrier'];
                                    $orders_model->item_status          =   $orders_status_array[$order_item['OrderNumber']];
                                    $update_result   =   $orders_model->UpdateApprovedOrders();
                                }
                            }
                    }
                }
            }
        }
        @fwrite($f_logs_feeds, 'Upadte Orders Finish at:'.date("Y-m-d-H:i:s")."\r\n");
        die;
    }
    
    function createOrdersCsvfileAction(){
        $user_ids   =   array('7');
        $user_file_name_array   =   array(
            '7' =>  'crazysales_dispatched_order_7.csv',
            '2' =>  'crazysales_dispatched_order_2.csv',
        );
        $order_new_path  =   'orders_new/';
        $user_titile_array  =   array(
            '7' =>   array('crazysales order id', 'apus_order', 'SKU', 'Qty', 'Tracking_Number', 'date dispatched', 'Courier', 'shipping_cost', 'order_amount'),
            '2' =>   array('crazysales order id', 'apus_order', 'SKU', 'Qty', 'Tracking_Number', 'date dispatched', 'Courier', 'shipping_cost', 'order_amount'),
        );
        $time_now   = time();
        $user_order_days_array    =   array(
            '7' =>  '-2 day',
            '2' =>  '-2 day',
        );
        $orders_model   =   new Databases_Joins_GetOrders();
        $params_model   =   new Databases_Tables_Params();
        $logs_path     =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."orderslogs/create-orders-csvfile".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Create Orders Csv file Begin at:'.date("Y-m-d-H:i:s")."\r\n");
        foreach ($user_ids as $user_id){
            @fwrite($f_logs_feeds, 'Create Orders user_id :'.$user_id.' Csv file Begin at:'.date("Y-m-d-H:i:s")."\r\n");
            $orders_model->item_status  =   3;
            $orders_model->user_id      =   $user_id;
            $time                       =   strtotime($user_order_days_array[$user_id], $time_now);
            $orders_model->start_date   =   date('Y-m-d', $time);  
            $orders_model->end_date     =   date('Y-m-d', $time_now);
            @fwrite($f_logs_feeds, 'Get user_id :'.$user_id.' Shipping Orders Begin at:'.date("Y-m-d-H:i:s")."\r\n");
            $user_shipping_orders       =   $orders_model->PushList();
            $f_order_new =   @fopen($order_new_path.$user_file_name_array[$user_id],'w');
            @fputcsv($f_order_new, $user_titile_array[$user_id]);
            if($user_shipping_orders && is_array($user_shipping_orders)){
                @fwrite($f_logs_feeds, 'Create user_id :'.$user_id.' Csv file Begin at:'.date("Y-m-d-H:i:s")."\r\n");
                foreach ($user_shipping_orders as $user_shipping_order){
                    $shipping_order_data    =   array(
                        'crazysales order id'   =>  $user_shipping_order['main_db_order_id'],
                        'apus_order'            =>  $user_shipping_order['merchant_ref'],
                        'SKU'                   =>  $user_shipping_order['supplier_sku'],
                        'Qty'                   =>  $user_shipping_order['quantity'],
                        'Tracking_Number'       =>  $user_shipping_order['tracking_number'],
                        'date dispatched'       =>  $user_shipping_order['shipping_date'],
                        'Courier'               =>  $user_shipping_order['shipping_courier'],
                        'shipping_cost'         =>  $user_shipping_order['final_ship_cost'],
                        'order_amount'          =>  $user_shipping_order['order_amount'],
                    );
                    @fputcsv($f_order_new, $shipping_order_data);
                }
                @fclose($f_order_new);
                @fwrite($f_logs_feeds, 'Create user_id :'.$user_id.' Csv file Finish at:'.date("Y-m-d-H:i:s")."\r\n");
            }
        }
        @fwrite($f_logs_feeds, 'Create Orders Csv file Finish at:'.date("Y-m-d-H:i:s")."\r\n");
        die;
    }
}