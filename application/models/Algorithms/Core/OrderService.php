<?php
/**
 * Interact with WebService of CrazySales To Place Order and Set Order Status to Crazysales
 * @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_OrderService extends SoapClient{
    
    /**
     * Money Type
     * @var MoneyType
     */
    var $moneyType;
    
    /**
     * Quantity Tpe
     * @var QuantityTpe
     */
    var $quantityType;
    
    /**
     * Message Type
     * @var array
     */
    var $messageType;
    
    /**
     * CrazySalesOrderType
     * @var CrazySalesOrderType
     */
    var $crazySalesOrderType;
    
    /**
     *  CrazySalesOrderItemType
     * @var CrazySalesOrderItemType
     */
    var $crazySalesOrderItemType;
    
    /**
     *  CrazySalesOrderStatusType
     * @var CrazySalesOrderStatusType
     */
    var $crazySalesOrderStatusType;
    
    var $OrderIDs;
    
    var $OrderStatus;
    
    private static $classmap = array();
    
    /**
     * __construct()
     * @param array $options
     */
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
            'cache_wsdl'=>WSDL_CACHE_NONE,
            "features" => SOAP_SINGLE_ELEMENT_ARRAYS,
         );
        parent::__construct($wsdl, $options);
    }
    
    /**
     * Change Oject To Array
     * @param array $array
     * @return array $array;
     */
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
    
    /**
     * Place Order to CrazySales
     * @param CrazySalesOrderItemType $crazySalesOrderItemType The item of the orders need to give in the controller
     * @return array $result
     */
    function WebServicePlaceOrder(){
        $this->crazySalesOrderType->OrderItems  =   $this->crazySalesOrderItemType;
        $req = new PlaceOrderRequest();
        $req->OrderRequests = array($this->crazySalesOrderType);
        $response   =   $this->PlaceOrder(array('request' => $req));
        $response   =   $this->object_array($response);
        $orders_info    =   $response['PlaceOrderResult']['Orders'];
        $order_number   =   $orders_info['CrazySalesOrderType'][0]['OrderNumber'];
        $message_info   =   $response['PlaceOrderResult']['Messages'];
        $result['order_number'] =   $order_number;
        $result['MessageType']  =   $message_info['MessageType'];
        return $result;
    }
    
    /**
     * Set Order Status to CrazySales with Webservice
     * 
     * the crazySalesOrderStatusType must has OrderNumber, StatusID
     * 
     * @param CrazySalesOrderStatusType $crazySalesOrderStatusType The Status info of the orders
     * @return array $result
     */
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
    
    /**
     * Get Orders Inform from Crazysales with Webservice
     * @param OrderIDs array
     * @return  array $result
     */
    function WebServiceGetOrderInfo(){
        $resulr     =   array();
        $getOrderInfoRequest    =   new GetOrderInfoRequest();
        $getOrderInfoRequest->OrderIDs  =   $this->OrderIDs;
        $result     =   array();
        $response   =   $this->GetOrder(array('request'=>$getOrderInfoRequest));
        $response   =   $this->object_array($response);
        $message_info   =   $response['GetOrderResult']['Messages'];
        $result['MessageType']  =   $message_info['MessageType']?$message_info['MessageType']:'';
        $result['Orders']       =   $response['GetOrderResult']['Orders'];
        return $result;
    }
    
    function WebServiceGetOrderStatus(){
        $result     =   array();
        $getOrderStatusRequest  =   new GetOrderStatusRequest();
        $getOrderStatusRequest->OrderIDs =   $this->OrderIDs;
        $result     =   array();
        $response   =   $this->GetOrderStatus(array('request'=>$getOrderStatusRequest));
        $response   =   $this->object_array($response);
        $message_info           =   $response['GetOrderStatusResult']['Messages'];
        $result['MessageType']  =   $message_info['MessageType']?$message_info['MessageType']:'';
        $result['OrderStatus']  =   $response['GetOrderStatusResult']['OrderStatus'];
        return $result;
    }
}