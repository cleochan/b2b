<?php
class Databases_Tables_DdOrders extends Zend_Db_Table {
    protected $_name = 'dd_orders';
    
    var $order_id;
    var $b2b_order_id;
    var $cc_order_id;
    var $o_num;
    var $buyer_full_name;
    var $company;
    var $address_line_1;
    var $address_line_2;
    var $suburb;
    var $state;
    var $post_code;
    var $phone_num;
    var $product_code;
    var $product_title;
    var $qty;
    var $cart_id;
    var $ref_num;
    var $cost;
    var $freight;
    var $tracking_number;
    var $shipping_date;
    var $courier;
    var $add_time;
    var $update_time;
    
    function addDdOrder(){
        $data   =   array(
            'b2b_order_id'      =>  $this->b2b_order_id,
            'cc_order_id'       =>  $this->cc_order_id,
            'o_num'             =>  $this->o_num,
            'buyer_full_name'   =>  $this->buyer_full_name,
            'company'           =>  $this->company,
            'address_line_1'    =>  $this->address_line_1,
            'address_line_2'    =>  $this->address_line_2,
            'suburb'            =>  $this->suburb,
            'state'             =>  $this->state,
            'post_code'         =>  $this->post_code,
            'phone_num'         =>  $this->phone_num,
            'product_code'      =>  $this->product_code,
            'product_title'     =>  $this->product_title,
            'qty'               =>  $this->qty,
            'cart_id'           =>  $this->cart_id,
            'ref_num'           =>  $this->ref_num,
            'cost'              =>  $this->cost,
            'freight'           =>  $this->freight,
            'tracking_number'   =>  $this->tracking_number,
            'shipping_date'     =>  $this->shipping_date,
            'courier'           =>  $this->courier,
            'add_time'          =>  date('Y-m-d H:i:s'),
        );
        $order_id   =   $this->insert($data);
        return $order_id;
    }
    
    function updateDdorder(){
        $result   =   '';
        if($this->cc_order_id){
            $where  =   " cc_order_id = '".$this->cc_order_id."' and  product_code ='".$this->product_code."'";
            $order  =   $this->fetchRow($where);
            if($order->order_id){
                $order->tracking_number =   $this->tracking_number;
                $order->shipping_date   =   $this->shipping_date;
                $order->courier         =   $this->courier;
                $order->update_time     =   date('Y-m-d H:i:s');
                $order->save();
                $result   =   "Order: ".$this->cc_order_id.' Shipping data update success at: '.date('Y-m-d H:i:s');
            }
        }
        return $result;
    }
    
    function updateDdOrderCcOrderID(){
        $order_id   =   '';
        if($this->b2b_order_id && $this->cc_order_id){
            $where  =   " b2b_order_id = '".$this->b2b_order_id."' ";
            $order  =   $this->fetchRow($where);
            if($order->order_id){
                $order->cc_order_id =   $this->cc_order_id;
                $order->save();
                $order_id   =   $order->order_id;
            }
        }
        return $order_id;
    }
    
    function getDdOrderInfo(){
        $result   =   '';
        if($this->cc_order_id){
            $where  =   " cc_order_id = '".$this->cc_order_id."' and  product_code ='".$this->product_code."'";
            $result  =   $this->fetchRow($where);
        }
        return $result;
    }
}