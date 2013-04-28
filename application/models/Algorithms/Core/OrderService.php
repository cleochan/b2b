<?php

class Algorithms_Core_OrderService extends SoapClient{
    
    var $moneyType  =   array(
        'Value' =>  '', // decimal
    );
    
    var $quantityType   =   array(
        'Value' =>  '', // int
    );
    
    var $messageType    =   array(
        'Action'        =>  '', // string
        'Created'       =>  '', // dateTime
        'Description'   =>  '', // string
        'Level'         =>  '', // string
    );
    
    var $crazySalesOrderType    =   array(
        'RetailerAccountEmail'  =>  '', // string
        'PaymentTypeID'         =>  '', // int
        'ShipFirstName'         =>  '', // string
        'ShipAddress_1'         =>  '', // string
        'ShipAddress_2'         =>  '', // string
        'ShipCompany'           =>  '', // string
        'ShipState'             =>  '', // string
        'ShipZipCode'           =>  '', // string
        'ShipCountryCode'       =>  '', // string
        'ShipPhone'             =>  '', // string
    );
    
    var $crazySalesOrderItemType    =   array(
        'ExpectedItemCost'      =>  '', // MoneyType
        'FinalItemCost'         =>  '', // MoneyType
        'ItemSku'               =>  '', // string
        'Quantity'              =>  '', // QuantityType
        'ShipMethod'            =>  '', // string
        'TrackingNumber'        =>  '', // string
    );
    
    var $crazySalesOrderStatusType  =   array(
        'AdminID'       =>  '', // int
        'Comment'       =>  '', // string
        'Level'         =>  '', // int
        'OrderNumber'   =>  '', // int
        'StatusName'    =>  '', // string
    );
    
    var $placeOrderRequest  =   array(
        'OrderRequests' =>  ''  // ArrayOfCrazySalesOrderType
    );
    
    var $placeOrderResponse =   array(
        'Orders'    =>  '', // ArrayOfCrazySalesOrderType
    );
    
    var $generalResponse    =   array(
        'Messages'  =>  '', // ArrayOfMessageType
    );
    
    var $placeOrder =   array(
        'request'   =>  '', // PlaceOrderRequest
    );
    
    private static $classmap = array();
    
    function __construct($wsdl = "http://10.0.0.186:8743/OrderService.svc?wsdl", $options = array()) {
        foreach(self::$classmap as $key => $value) {
            if(!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }
    
    function object_array($array){
        if(is_object($array)){
          $array = (array)$array;
        }
        if(is_array($array)){
          foreach($array as $key=>$value){
            $array[$key] = $this->object_array($value);
          }
        }
        return $array;
    }
    
    function WebServicePlaceOrder(){
        $this->crazySalesOrderType['OrderItems']    =   $this->crazySalesOrderItemType;
        $this->placeOrderRequest['OrderRequests']   =   array($this->crazySalesOrderType);
        
        $response   =   $this->PlaceOrder(array('request' => $this->placeOrderRequest));
        $response   =   $this->object_array($response);
        $orders_info    =   $response['PlaceOrderResult']['Orders'];
        $order_number   =   $orders_info['CrazySalesOrderType']['OrderNumber'];
        $message_info   =   $response['PlaceOrderResult']['Messages'];
        $result['order_number'] =   $order_number;
        $result['MessageType']  =   $message_info['MessageType'];
        return $result;
    }
 
}

?>