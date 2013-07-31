<?php
class Databases_Joins_OperateOrders {
    var $purchase_order_ids;
    var $order_api_trying_times;
    
    function PlaceOrder()
    {
        $purchase_order_model   =   new Databases_Tables_PurchaseOrder();
        $getorder_model         =   new Databases_Joins_GetOrders();
        $logs_orders_model      =   new Databases_Tables_LogsOrders();
        $user_info_model        =   new Databases_Joins_GetUserInfo();
        $order_service_model    =   new Algorithms_Core_OrderService();
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        $getorders_model        =   new Databases_Joins_GetOrders();
        $logs_financial         =   new Databases_Tables_LogsFinancial();
        $order_core_model       =   new Algorithms_Core_Order();
        $crazy_sales_order_status_array     =   array();
        $crazy_sales_order_status_array1    =   array();
        $crazySalesOrderItemTypeArray       =   array();
        $order_fail_num =   0;
        $order_success_num  =   0;
        $result =   array(
            'order_fail'    =>  0,
            'order_success' =>  0,
        );
        if($this->purchase_order_ids){
            $purchase_order_model->purchase_order_ids    =   $this->purchase_order_ids;
            $purchase_orders =   $purchase_order_model->GetPurchaseOrder();
        }else{
            $getorder_model->item_status    =   0;
            if($this->order_api_trying_times)
            {
                $getorder_model->order_api_trying_times     =   $this->order_api_trying_times;
            }
            $purchase_orders    =   $getorder_model->getPendinglist();
        }
        
        if($purchase_orders)
        {
            foreach ($purchase_orders as $purchase_order)
            {
                $crazySalesOrderType        =   new CrazySalesOrderType();
                $moeney_type                =   new MoneyType();
                $order_discount             =   new MoneyType();
                $order_amount_money_type    =   new MoneyType();

                $crazySalesOrderType->PaymentTypeID          =   $purchase_order['payment_type_id']; 
                $crazySalesOrderType->RetailerAccountEmail   =   $_SESSION["Zend_Auth"]["storage"]->email;
                $crazySalesOrderType->ShipFirstName          =   $purchase_order['shipping_first_name'];
                $crazySalesOrderType->ShipAddress_1          =   $purchase_order['shipping_address_1'];
                $crazySalesOrderType->ShipAddress_2          =   $purchase_order['shipping_address_2'];
                $crazySalesOrderType->ShipCity               =   $purchase_order['shipping_suburb'];
                $crazySalesOrderType->ShipState              =   $purchase_order['shipping_state'];
                $crazySalesOrderType->ShipZipCode            =   $purchase_order['shipping_postcode'];
                $crazySalesOrderType->ShipCountryCode        =   $purchase_order['shipping_country'];
                $crazySalesOrderType->ShipPhone              =   $purchase_order['shipping_phone'];
                $crazySalesOrderType->SiteID                 =   6;

                $user_info  =   $user_info_model->GetUserInfo($purchase_order['user_id']);
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
                        $order_service_model->crazySalesOrderType  =   $crazySalesOrderType;                    
                        $crazySalesOrderItemTypeArray[$logs_order['merchant_ref']][]   =   $crazySalesOrderItemType;
                    }
                }
                $order_service_model->crazySalesOrderItemType   =   $crazySalesOrderItemTypeArray[$logs_order['merchant_ref']];
                $response_data   =   $order_service_model->WebServicePlaceOrder();
                if($response_data['order_number']) 
                {
                    foreach ($logs_orders as $logs_order)
                    {
                        $product_filter_model->updateQuantityAvailable($logs_order['supplier_sku'], $logs_order['quantity']);
                    }
                    $getorders_model->main_order_id =   $response_data['order_number'];
                    $getorders_model->item_status   =   0;
                    $getorders_model->api_response  =   'Pending';

                    //Update Financial Info
                    $logs_financial->user_id = $purchase_order['user_id'];
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $purchase_order['order_amount'];
                    // $logs_financial->trans_id = $place_order_return['logs_orders_id'];
                    //$logs_financial->AddLog();

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
                    $getorders_model->item_status   =   2;
                    $getorders_model->api_response  =   $response_data['MessageType']['Description'];
                }
                $getorders_model->logs_order_ids    =  $logs_order_ids;
                $getorders_model->purchase_order_id   =   $purchase_order['purchase_order_id'];
                $getorders_model->UpdateOrder();
            }

            $order_service_model->crazySalesOrderStatusType =   $crazy_sales_order_status_array1;
            $result_message =   $order_service_model->WebServiceSetOrderStatus();
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
                    unset($crazy_sales_order_status_array[$message_main_order_id]);
                    $order_fail_num++;
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
                        unset($crazy_sales_order_status_array[$message_main_order_id]);
                        $order_fail_num++;
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
                    $logs_orders_model->UpdateLogsOrderStatus();
                    $logs_financial->user_id = $crazy_sales_order->PurchaseOrderUserId;
                    $logs_financial->action_type = 1; //place order
                    $logs_financial->action_affect = 2; //deduct
                    $logs_financial->action_value = $crazy_sales_order->order_amount;
                    // $logs_financial->trans_id = $place_order_return['logs_orders_id'];
                    $logs_financial->AddLog();
                    $order_success_num++;
                }
            }
        }
        $result['order_success']    =   $order_success_num;
        $result['order_fail']       =   $order_fail_num;
        return $result;
    }
    
    
}