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
    
    function __construct($wsdl = "http://10.0.0.186:8743/CategoryService.svc?wsdl", $options = array()) {
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
