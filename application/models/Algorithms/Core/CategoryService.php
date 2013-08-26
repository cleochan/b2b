<?php

/**
 * Interact with WebService of CrazySales To Get Categories Data from CrazySales Database
 * @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_CategoryService extends SoapClient{
    
    /**
     * GetCategoryRequest 
     *  CategoryIDs     ArrayOfint
     *  CategoryNames   ArrayOfstring
     *  Pagination      PaginationType
     * @var array 
     */
    var $GetCategoryRequest =   array(
        'CategoryIDs'   =>  array(),
        'CategoryNames' =>  array(),
        'Pagination'    =>  array(),
    );
    
    /**
     *  PaginationType
     * @var array
     */
    var $PaginationType =   array(
        'EntriesPerPage'    =>  '',
        
    );
    
    /**
     *  Entries Per Page
     * @var int 
     */
    var $EntriesPerPage =   '';
    
    /**
     *  Page Number
     * @var int
     */
    var $PageNumber     =   '';
  
    
    private static $classmap = array();
    
    /**
     * @param array $options
     */
    function __construct($options = array()) {
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
    
    /**
     * Change Oject To Array
     * @param array $array
     * @return array $array;
     */
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
    
    /**
     * Get Category Data from CrazySales Database
     * 
     * PageNumber and EntriesPerPage need to be get in the controller
     * 
     * @param int $EntriesPerPage Entries Per Page
     * @param int $PageNumber Page Number
     * @return array $result;
     */
    function WebServicesGetCategories()
    {

        $this->PaginationType['EntriesPerPage'] = $this->EntriesPerPage;
        $this->PaginationType['PageNumber']     = $this->PageNumber;
        $req    =   $this->GetCategoryRequest;
        $req['Pagination']     =   $this->PaginationType;
        try{
            $response   =   $this->GetCategory(array('categoryRequest' => $req)); 
        }  catch (Exception $e){
            $response   =   array();
        }
        $result     =   $this->object_array($response);
        return $result;
    }
}
?>