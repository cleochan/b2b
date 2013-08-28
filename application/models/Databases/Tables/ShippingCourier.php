<?php
/**
 * @author Tim TimWu@crazysales.com.au
 */
class Databases_Tables_ShippingCourier extends Zend_Db_Table
{
    protected $_name    =   'shipping_courier';
    var $sc_class;
    var $shipping_courier;
    var $shipping_courier_array =   array();
    
    function __construct() {
        parent::__construct();
        $this->db = Zend_Registry::get("db");
    }
    
    function GetShippingCouriers(){
        $result = array();
        $select = $this->select();
        $select->order("sc_class ASC");
        $data = $this->fetchAll($select);
        if($data){
            $shipping_courier_array =   $data->toArray();
            foreach ($shipping_courier_array as $shipping_courier){
                $result[$shipping_courier['sc_class']]  =   $shipping_courier['shipping_courier'];
            }
        }
        return  $result;
    }
    
    function AddShippingCourier(){
        $this->db->query('truncate table '.$this->_name);
        if($this->shipping_courier_array){
            foreach ($this->shipping_courier_array as $shipping_courier){
                if($shipping_courier){
                    $this->insert($shipping_courier);
                }
            }
        }
    }
}

?>
