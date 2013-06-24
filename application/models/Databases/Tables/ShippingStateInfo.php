<?php
class Databases_Tables_ShippingStateInfo extends Zend_Db_Table{
    protected $_name = 'shipping_state_info';
    var $id;
    var $post_code;
    var $shipping_suburb;
    var $shipping_state;
    var $shipping_state_info    =   array();
    
    function GetShippingStateInfo()
    {
        $select =   $this->select();
        $select->from($this->_name,array('count(post_code) as count'));
        $select->where('post_code = ?',$this->post_code);
        $select->where('shipping_suburb = ?', $this->shipping_suburb);
        $select->where('shipping_state = ?', $this->shipping_state);
        $result =   $this->fetchRow($select);
        return $result['count'];
    }
}

?>
