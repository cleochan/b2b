<?php

class Algorithms_Core_OrderService extends SoapClient{
    
    var $moneyType;
    
    var $quantityType;
    
    var $messageType;
    
    var $crazySalesOrderType;
    
    var $crazySalesOrderItemType;
    
    var $crazySalesOrderStatusType;
    
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
        $options = array( 
            'encoding' => 'UTF-8',
            'soap_version'=>SOAP_1_1, 
            'exceptions'=>true, 
            'trace'=>1, 
            'cache_wsdl'=>WSDL_CACHE_NONE 
         );
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
        $this->crazySalesOrderType->OrderItems  =   $this->crazySalesOrderItemType;
        $req = new PlaceOrderRequest();
        $req->OrderRequests = array($this->crazySalesOrderType);
        $response   =   $this->PlaceOrder(array('request' => $req));
        $response   =   $this->object_array($response);
        $orders_info    =   $response['PlaceOrderResult']['Orders'];
        $order_number   =   $orders_info['CrazySalesOrderType']['OrderNumber'];
        $message_info   =   $response['PlaceOrderResult']['Messages'];
        $result['order_number'] =   $order_number;
        $result['MessageType']  =   $message_info['MessageType'];
        return $result;
    }
    
    function WebServiceSetOrderStatus(){
        $req    =   new SetOrderStatusRequest();
        $logs_wsdl_order_status_model   =   new Databases_Tables_LogsWsdlOrderStatus();
        $req->OrderStatus   =   $this->crazySalesOrderStatusType;
        $logs_wsdl_order_status_model->status_resquest  = Zend_Json::encode($this->crazySalesOrderStatusType);
        $response   =   $this->SetOrderStatus(array('request'=>$req));
        $logs_wsdl_order_status_model->status_message_response  = Zend_Json::encode($response);
        $logs_wsdl_order_status_model->AddLogs();
        $response   =   $this->object_array($response);
        $message_info   =   $response['SetOrderStatusResult']['Messages'];
        $result['MessageType']  =   $message_info['MessageType'];
        if(!$result['MessageType'])
        {
            $result['MessageType']  =   array();
        }
        return $result;
    }
}