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
    var $status;
    var $error_message;
    var $update_start_date;
    var $update_end_date;
    var $p_qty_per_page;
    var $item_statuses;
    function addDdOrder(){
        $data   =   array(
            //'b2b_order_id'      =>  $this->b2b_order_id,
            //'cc_order_id'       =>  $this->cc_order_id,
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
            'status'            =>  $this->status,
            'error_message'     =>  $this->error_message,         
            'add_time'          =>  date('Y-m-d H:i:s'),
        );
        $order_id   =   $this->insert($data);
        return $order_id;
    }
    
    function updateDdorder(){
        $result   =   '';
        if($this->b2b_order_id){
            $where  =   " b2b_order_id = '".$this->b2b_order_id."' and  product_code ='".$this->product_code."'";
            $order  =   $this->fetchRow($where);
            if($order->order_id){
                $order->tracking_number =   $this->tracking_number;
                $order->shipping_date   =   $this->shipping_date;
                $order->courier         =   $this->courier;
                $order->update_time     =   date('d/m/Y');
                $order->status          =   $this->status;
                $order->save();
                $result   =   "Order: ".$this->cc_order_id.' Shipping data update success at: '.date('Y-m-d H:i:s');
            }
        }
        return $result;
    }
    
    function updateDdOrderCcOrderID(){
        if($this->b2b_order_id && $this->cc_order_id){
            $where  =   " b2b_order_id = '".$this->b2b_order_id."' ";
            $select =   $this->select();
            $select->where($where);
            $orders  =   $this->fetchAll($select);
            if($orders){
                $data   =   array(
                    'cc_order_id'   =>  $this->cc_order_id,
                    'status'        =>  $this->status,
                );
                $this->update($data, $where);
            }
        }
    }
    
    function getDdOrderInfo(){
        $result   =   '';
        if($this->b2b_order_id){
            $where  =   " b2b_order_id = '".$this->b2b_order_id."' and  product_code ='".$this->product_code."'";
            $result  =   $this->fetchRow($where);
        }
        return $result;
    }
    
    function updateDdOrderB2bOrderId(){
        $result =   '';
        if($this->order_id){
            $where  =   " order_id = '".$this->order_id."' ";
            $result  =   $this->fetchRow($where);
            if($result->order_id && $this->b2b_order_id){
                $result->b2b_order_id   =   $this->b2b_order_id;
                $result->save();
            }
        }
    }
    function getDdorders(){
        $select =   $this->select();
        if($this->update_start_date)
        {
            $select->where("update_time >= ?", $this->update_start_date." 00:00:00");
        }
        if($this->update_end_date)
        {
            $select->where("update_time <= ?", $this->update_end_date." 23:59:59");
        }
        if($this->item_statuses && is_array($this->item_statuses)){
            $in_item_status = implode(',', $this->item_statuses);
            $select->where("status in (".$in_item_status.") ");
        }
        $orders  =   $this->fetchAll($select);
        if($orders){
            return  $orders->toArray();
        }else{
            return  FALSE;
        }
    }
    
}