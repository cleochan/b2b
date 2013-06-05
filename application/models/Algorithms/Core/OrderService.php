<?php

class Algorithms_Core_OrderService extends SoapClient{
    
    var $moneyType;
    
    var $quantityType;
    
    var $messageType;
    
    var $crazySalesOrderType;
    
    var $crazySalesOrderItemType;
    
    private static $classmap = array();
    
    function __construct($options = array()) {
        $params_model   =   new Databases_Tables_Params();
        $web_service_url    =   $params_model->GetVal('web_service_url');
        $wsdl   =   $web_service_url."OrderService.svc?wsdl";
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
        $this->crazySalesOrderType->OrderItems  =   array($this->crazySalesOrderItemType);
        $req = new PlaceOrderRequest();
        $req->OrderRequests = array($this->crazySalesOrderType);
        print_r(array('request' => $req));
        exit();
        $response   =   $this->PlaceOrder(array('request' => $req));
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
