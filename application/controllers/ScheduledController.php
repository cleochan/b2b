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
    
    function refreshProductsAction()
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
    
    /**
     * @author Tim Wu<TimWu@crazysales.com.au>
     */
    function reprocessOrderAction()
    {
        $getorder_model         =   new Databases_Joins_GetOrders();
        $order_webservice_model =   new Algorithms_Core_OrderService();
        $system_params_model    =   new Databases_Tables_Params();
        $getuser_info_model     =   new Databases_Joins_GetUserInfo();
        
        $order_status_pending       =   $system_params_model->GetVal('order_status_pending');
        $order_api_trying_times     =   $system_params_model->GetVal('order_api_trying_times');
        $order_api_trying_interval  =   $system_params_model->GetVal('order_api_trying_interval');
        
        $merchant_ref_pool = array();
        
        $getorder_model->item_status    =   0;
        $getorder_model->order_api_trying_times     =   $order_api_trying_times;
        $getorder_model->order_api_trying_interval  =   $order_api_trying_interval;
        
        $orders_pending_list    =   $getorder_model->getPendinglist();
        if($orders_pending_list)
        {
            foreach ($orders_pending_list as $key => $order_pending)
            {
                $user_info   =   $getuser_info_model->GetUserInfo($order_pending['user_id']);
                $merchant_email =   $user_info['email'];
                $order_webservice_model->crazySalesOrderType['RetailerAccountEmail']   =   $merchant_email;

                $order_webservice_model->crazySalesOrderType['PaymentTypeID']          =   1; //PaymentTypeID is unknown,need to check the PaymentTypeID

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
                }else
                {
                    $order_pending['api_trying_times']  +=  1;
                    $getorder_model->order_api_trying_times =   $order_pending['api_trying_times'];
                    $getorder_model->api_response           =   $response_data['MessageType']['Description'];
                }
                $place_order_return = $getorder_model->updatePendingOrder(); // Transaction ID for financial table
                    //update merchant ref pool
                $merchant_ref_pool = $place_order_return['merchant_ref_pool'];
                print_r($place_order_return);
            }
        }

        die();
    }
}