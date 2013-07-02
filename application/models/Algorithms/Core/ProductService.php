<?php

/**
 * 
 * @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_ProductService extends SoapClient{
    
    var $PaginationType =   array(
        'EntriesPerPage'    =>  '',
        'PageNumber'        =>  '',
    );
    
    var $PaginationRequest  =   array(
        'DetailsLevel'      =>  '',
        'Pagination'        =>  '',
    );
   
    var $GetProductsRequest =   array(
        'CategoryIDs'       =>  '',
        'Pagination'        =>  '',
    );
    var $ProductDetailsLevelType    =   array(
        'AdditionalFlag'    =>  '',
        'OptionFlag'        =>  '',
        'PackageFlag'       =>  '',
        'PictureFlag'       =>  '',
        'ProductCodeFlag'   =>  '',
        'PromotionFlag'     =>  '',
        'PurchaseFlag'      =>  '',
        'ShippingDetailsFlag'   =>  '',
        'WarehouseFlag'     =>  '',
    );
    
    var $EntriesPerPage;
    var $PageNumber;
    
    private static $classmap = array();
    
    function __construct($options = array()) {
        $params_model   =   new Databases_Tables_Params();
        $web_service_url    =   $params_model->GetVal('web_service_url');
        $wsdl   =   $web_service_url."ProductService.svc?wsdl";
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
        $this->ProductDetailsLevelType['AdditionalFlag']    =   0;
        $this->ProductDetailsLevelType['OptionFlag']        =   0;
        $this->ProductDetailsLevelType['PackageFlag']       =   0;
        $this->ProductDetailsLevelType['PictureFlag']       =   1;
        $this->ProductDetailsLevelType['ProductCodeFlag']   =   0;
        $this->ProductDetailsLevelType['PromotionFlag']     =   0;
        $this->ProductDetailsLevelType['PurchaseFlag']      =   0;
        $this->ProductDetailsLevelType['ShippingDetailsFlag']    =   0;
        $this->ProductDetailsLevelType['WarehouseFlag']     =   0;
        $this->PaginationType['EntriesPerPage'] =   $this->EntriesPerPage;
        $this->PaginationType['PageNumber']     =   $this->PageNumber;
        $req    =   $this->GetProductsRequest;
        $req['Pagination']      =   $this->PaginationType;
        $req['DetailsLevel']    =   $this->ProductDetailsLevelType;
        $response   =   $this->GetProducts(array('request' => $req)); 
        $result     =   $this->object_array($response);
        return $result;
    }
}