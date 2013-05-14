<?php

/*
 *  @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_CategoryService extends SoapClient{
    
    var $GetCategoryRequest =   array(
        'CategoryIDs'   =>  array(),
        'CategoryNames' =>  array(),
        'Pagination'    =>  array(),
    );
    
    var $PaginationType =   array(
        'EntriesPerPage'    =>  '',
        
    );
    
    var $EntriesPerPage =   '';
    var $PageNumber     =   '';
  
    
    private static $classmap = array();
    
    function __construct($wsdl , $options = array()) {
        $params_model   =   new Databases_Tables_Params();
        $web_service_url    =   $params_model->GetVal('web_service_url');
        $wsdl   =   $web_service_url."CategoryService.svc?wsdl";
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
    
    function WebServicesGetCategories()
    {

        $this->PaginationType['EntriesPerPage'] =   '';
        $this->PaginationType['PageNumber']     =   '';
        $req    =   $this->GetCategoryRequest;
        $req['Pagination']     =   $this->PaginationType;
        $response   =   $this->GetCategory(array('categoryRequest' => $req)); 
        $result     =   $this->object_array($response);
        return $result;
    }
}
?>
