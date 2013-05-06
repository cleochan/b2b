<?php

class ScheduledController extends Zend_Controller_Action
{
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
    
    function refreshProductAction()
    {
        /**
         * truncate the secondary table
         * send initial request
         */
        $params_model = new Databases_Tables_Params();
        $secondary = $params_model->GetSecondaryProductTableName();
        
        if($secondary['table_num'])
        {
            $product_filter_model = new Databases_Joins_ProductFilter();
            if($product_filter_model->TruncateProductTable($secondary['table_num']))
            {
                $product_filter_model->PostXmlToRefreshProducts(); 
            }
        }
        
        echo "End.";
        die;
    }
    
    function refreshOrdersAction() 
    {
        try{
        $getorder_model         =   new Databases_Joins_GetOrders();
        $order_webservice_model =   new Algorithms_Core_OrderService();
        $system_params_model    =   new Databases_Tables_Params();
        $getuser_info_model     =   new Databases_Joins_GetUserInfo();
        
        $order_api_trying_times     =   $system_params_model->GetVal('order_api_trying_times');
        $order_api_trying_interval  =   $system_params_model->GetVal('order_api_trying_interval');
        $logs_path                  =   $system_params_model->GetVal('logs_path');
        $logs_contents              =   ' ';
        $merchant_ref_pool = array();
        $getorder_model->item_status    =   0;
        $getorder_model->order_api_trying_times     =   $order_api_trying_times;
        $getorder_model->order_api_trying_interval  =   $order_api_trying_interval;
        $orders_pending_list    =   $getorder_model->getPendinglist();
        if($orders_pending_list)
        {
            foreach ($orders_pending_list as  $order_pending)
            {
                $user_info   =   $getuser_info_model->GetUserInfo($order_pending['user_id']);
                $merchant_email =   $user_info['email'];
                $order_webservice_model->crazySalesOrderType['RetailerAccountEmail']   =   $merchant_email;
                $order_webservice_model->crazySalesOrderType['PaymentTypeID']          =   1; 
                $order_webservice_model->crazySalesOrderType['ShipFirstName']          =   $order_pending['shipping_first_name'];
                $order_webservice_model->crazySalesOrderType['ShipAddress_1']          =   $order_pending['shipping_address_1'];
                $order_webservice_model->crazySalesOrderType['ShipAddress_2']          =   $order_pending['shipping_address_2'];
                $order_webservice_model->crazySalesOrderType['ShipCity']               =   $order_pending['shipping_suburb'];
                $order_webservice_model->crazySalesOrderType['ShipState']              =   $order_pending['shipping_state'];
                $order_webservice_model->crazySalesOrderType['ShipZipCode']            =   $order_pending['shipping_postcode'];
                $order_webservice_model->crazySalesOrderType['ShipCountryCode']        =   $order_pending['shipping_country'];
                $order_webservice_model->crazySalesOrderType['ShipPhone']              =   $order_pending['shipping_phone'];
                $order_webservice_model->crazySalesOrderType['orderAmount']            =   $order_pending['order_amount'];
                $order_webservice_model->crazySalesOrderItemType['Quantity']           =   $order_pending['quantity'];
                $order_webservice_model->crazySalesOrderItemType['ItemSku']            =   $order_pending['supplier_sku'];
                $getorder_model->purchase_order_id  =   $order_pending['purchase_order_id'];
                $getorder_model->logs_orders_id     =   $order_pending['logs_orders_id'];
                $getorder_model->merchant_ref_pool  =   $merchant_ref_pool;
                $getorder_model->merchant_ref       =   $order_pending['merchant_ref'];
                $response_data   =   $order_webservice_model->WebServicePlaceOrder();
                if($response_data['order_number'])
                {
                    $getorder_model->main_order_id  =   $response_data['order_number'];
                    $getorder_model->item_status    =   1;
                    
                }elseif($response_data['MessageType']['Description'])
                {
                    $getorder_model->item_status    =   2;
                    $getorder_model->order_api_trying_times =   $order_pending['api_trying_times'];
                    $getorder_model->api_response           =   $response_data['MessageType']['Description'];
                }
                else
                {
                     $order_pending['api_trying_times']  +=  1;
                     $getorder_model->order_api_trying_times    =   $order_pending['api_trying_times'];
                     $getorder_model->api_response   =  "time out";
                }
                
                $logs_contents   .=   'OrderNumber:'.$response_data['order_number'].' ItemStatus:'.$getorder_model->item_status.' DateTime:'.date('Y-m-d H:i:s').' ApiResponse:'.$getorder_model->api_response." \n ";
                $place_order_return = $getorder_model->updatePendingOrder(); 
                //print_R($place_order_return);  
                //$merchant_ref_pool = $place_order_return['merchant_ref_pool'];
            }
            $f  =   fopen($logs_path."orderslogs/refreshorders".date('YmdHis').".txt", "w+");
            @fwrite($f, $logs_contents);
            @fclose($f);
        }
          }  catch (Zend_Exception $exp){
            var_dump($exp->getMessage());
        }

        die();
    }
    
    function refreshProductsAction()
    {
        $product_webservice_model   =   new Algorithms_Core_ProductService();
        $params_model           =   new Databases_Tables_Params();
        $productFilter_model    =   new Databases_Joins_ProductFilter();
        
        $data_source            =   $params_model->GetVal("product_info_table");
        $entries_perpage        =   $params_model->GetVal("product_request_qty_per_page");
        $logs_path              =   $params_model->GetVal('logs_path');
        $productFilter_model->data_source   =   $data_source;
        $TotalNumberOfEntries   =   '';
        $TotalNumberOfPages     =   '';
        $logs_contents         =   ' ';
        $page_now   =   1;
        $paginationType =   array(
            'EntriesPerPage'   =>  $entries_perpage,
            'PageNumber'       =>  $page_now,
        );
        $product_webservice_model->EntriesPerPage =   $paginationType['EntriesPerPage'];
        $productFilter_model->truncateProduct();
        do
        {
            
            $product_webservice_model->PageNumber =   $page_now;
            $product_webservice_model->PaginationType   =   $paginationType;
            $reponse_data  =   $product_webservice_model->WebServicesGetProducts();
            $TotalNumberOfEntries   =   $reponse_data['GetProductsResult']['PaginationResult']['TotalNumberOfEntries'];
            $TotalNumberOfPages     =   $reponse_data['GetProductsResult']['PaginationResult']['TotalNumberOfPages'];
            $product_list_data      =   $reponse_data['GetProductsResult']['Products']['CrazySalesProductType'];
            foreach ($product_list_data as $product_data){                
                $productFilter_model->product_id    =   $product_data['ProductID'];
                $productFilter_model->supplier_sku  =   $product_data['SupplierSku'];
                $productFilter_model->retailer_sku  =   '';
                $productFilter_model->product_name  =   $product_data['ProductName'];
                $productFilter_model->retailer_account_id   =   '';
                $productFilter_model->wholesale_cost    =   $product_data['Cost']['Value'];
                $productFilter_model->street_price      =   $product_data['StreetPrice']['Value'];
                $productFilter_model->estimated_shipping_cost   =   $product_data['EstimatedShippingCost']['Value'];
                $productFilter_model->estimated_handling_fee    =   $product_data['EstimatedHandlingCost']['Value'];
                $productFilter_model->quantity_available        =   $product_data['QuantityAvailable']['Value'];
                $productFilter_model->long_html_description     =   '';
                $productFilter_model->inventory_id              =   '';
                $productFilter_model->long_html_description     =   '';
                $productFilter_model->short_html_description    =   '';
                $productFilter_model->long_text_description     =   '';
                $productFilter_model->short_text_description    =   '';
                $productFilter_model->brand                     =   $product_data['Brand'];
                $productFilter_model->brand_id                  =   '';
                $productFilter_model->manufacturer              =   $product_data['Manufacturer'];
                $productFilter_model->condition_id              =   '';
                $productFilter_model->last_update_date          =   $product_data['LastUpdateDate']['Value'];
                $productFilter_model->mpn                       =   $product_data['MPN'];
                $productFilter_model->upc                       =   $product_data['UPC'];
                $productFilter_model->ean                       =   $product_data['EAN'];
                $productFilter_model->isbn                      =   $product_data['ISBN'];
                $productFilter_model->gtin                      =   $product_data['GTIN'];
                $productFilter_model->country_of_origin         =   $product_data['CountryOfOrigin'];
                $productFilter_model->catalog                   =   $product_data['Catalog'];
                $productFilter_model->catalog_start_date        =   $product_data['CatalogStartDate'];
                $productFilter_model->catalog_end_date          =   $product_data['CatalogEndDate'];
                $productFilter_model->category                  =   $product_data['Category']['CategoryName'];
                $productFilter_model->category_id                  =   $product_data['Category']['CategoryID'];
                $productFilter_model->cross_sell_skus           =   $product_data['CrossSellSkus'];
                $productFilter_model->package_weight            =   $product_data['PackageWeight'];
                $productFilter_model->package_weight_units      =   '';
                $productFilter_model->package_length            =   '';
                $productFilter_model->package_height            =   '';
                $productFilter_model->package_depth             =   '';
                $productFilter_model->package_dimension_units   =   $product_data['PackageDimension']['Units'];
                $productFilter_model->ships_freight             =   '';
                $productFilter_model->freight_class             =   '';
                $productFilter_model->ships_alone               =   '';
                $productFilter_model->max_ship_single_box       =   $product_data['MaxShippingSingleBox'];
                $productFilter_model->length                    =   $product_data['ProductDimension']['Length'];
                $productFilter_model->height                    =   $product_data['ProductDimension']['Width'];
                $productFilter_model->depth                     =   $product_data['ProductDimension']['Depth'];
                $productFilter_model->dimension_units           =   $product_data['ProductDimension']['Units'];
                $productFilter_model->weight                    =   $product_data['ProductWeight']['Value'];
                $productFilter_model->weight_units              =   $product_data['ProductWeight']['Units'];
                $productFilter_model->dimension_description     =   '';
                $productFilter_model->min_purchase_quantity     =   $product_data['MinPurchaseQuantity'];
                $productFilter_model->max_purchase_quantity     =   $product_data['MaxPurchaseQuantity'];
                $productFilter_model->bin_number                =   $product_data['BinNumber'];
                $productFilter_model->accessory_skus            =   $product_data['AccessorySkus'];
                $productFilter_model->keywords                  =   $product_data['Keywords'];
                $productFilter_model->pers_available            =   '';
                $productFilter_model->gift_wrap_available       =   '';
                $productFilter_model->details                   =   '';
                $productFilter_model->features                  =   $product_data['Features'];
                $productFilter_model->specification             =   $product_data['Specification'];
                $productFilter_model->warranty                  =   $product_data['Warranty'];
                $productFilter_model->discontinue_flag          =   $product_data['DiscontinueFlag']['Value'];
                $productFilter_model->case_pack_length          =   $product_data['CasePackDimension']['Length'];
                $productFilter_model->case_pack_height          =   $product_data['CasePackDimension']['Width'];
                $productFilter_model->case_pack_depth           =   $product_data['CasePackDimension']['Depth'];
                $productFilter_model->case_pack_units           =   $product_data['CasePackDimension']['Units'];
                $productFilter_model->case_pack_quantity        =   $product_data['CasePackQuantity']['Value'];
                $productFilter_model->AddProduct();
            }
            $logs_contents  .=   'page'.$page_now.'Date:'.date('Y-m-d H:i:s')."\n";
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
        $f  =   @fopen($logs_path."productslogs/refreshproducts".date('YmdHis').".txt", "w+");
        @fwrite($f, $logs_contents);
        @fclose($f);
        die();
    }    
}