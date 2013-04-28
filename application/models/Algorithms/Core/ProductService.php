<?php

class Algorithms_Core_ProductService extends SoapClient{

   var $MoneyType   =   array(
       'Value'  =>  '', //decimal
   );
   
   var $MessageType =   array(
       'Action'     =>  '',// string
       'Created'    =>  '',// dateTime
       'Description'    =>  '',//string
       'Level'      =>  '', 
   );
   
   var $CategoryType    =   array(
       'CategoryIDs'    =>  ''  //array of int
   );
   
   var $EntriesPerPage;
   var $PageNumber;
   var $GetProductsRequest  =   array(
       'ProductRequests'    =>  '',
   );
   
   var $GetProductsResponse =   array(
       'Product'    =>  '',
   );
   
    private static $classmap = array();
    
    function __construct($wsdl = "http://10.0.0.186:8743/ProductService.svc?wsdl", $options = array()) {
        foreach(self::$classmap as $key => $value) {
            if(!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }
    
    function object_array($array){
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value){
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }
    
    function WebServicesGetProducts()
    {
        $PaginationType['EntriesPerPage']   =   10;
        $PaginationType['PageNumber']       =   1;
        $req        = $this->GetProductsRequest;
        $cattype    =   $this->CategoryType;
        $cattype['PaginationType']  =   $PaginationType;
        $req['ProductRequests']     =   array($cattype);
        $response   =   $this->GetProducts(array('request' => $req)); 
        $result     =   $this->object_array($response);
        return $result;
    }
}

?>
