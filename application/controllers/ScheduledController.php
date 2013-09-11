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
                        $crazySalesOrderItemType->ShipCost           =   $ship_cost;

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
        $user_list  =   $user_model->GetUserList();
        $logs_path              =   $params_model->GetVal('logs_path');
        $f_logs_feeds  =   @fopen($logs_path."feedslogs/refreshfeeds".date('YmdHis').".txt", "w+");
        @fwrite($f_logs_feeds, 'Refresh Feeds Begin at:'.date("Y-m-d H:i:s")."\n");
        if($user_list){
            $model = new Algorithms_Core_Feed();            
            foreach($user_list as $user){
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
      
}