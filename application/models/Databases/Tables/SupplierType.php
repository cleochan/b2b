<?php
/**
 * @author Tim TimWu@crazysales.com.au
 */
class Databases_Tables_SupplierType extends Zend_Db_Table
{
    protected $_name    =   'supplier_type';
    var $supplier_type_id;
    var $supplier_type;
    var $supplier_type_array;
    
   function __construct() {
        parent::__construct();
        $this->db = Zend_Registry::get("db");
    }
    
    function AddSupplierAType(){
        $this->db->query('truncate table '.$this->_name);
        if($this->supplier_type_array){
            foreach ($this->supplier_type_array as $supplier_type){
                if($supplier_type['supplier_type']){
                    $this->insert($supplier_type);
                }
            }
        }
    }
    
    function GetSupplierType(){
        $result =   array();
        $select = $this->select();
        $select->order("supplier_type_id ASC");
        $data = $this->fetchAll($select);
        if($data){
            $suoolier_type_list =   $data->toArray();
            foreach ($suoolier_type_list as $supplier_type){
                $result[$supplier_type['supplier_type']] =   $supplier_type['supplier_type'];
            }
        }
        return $result;
    }
}

?>
