<?php
class Databases_Tables_OrdersFiles extends Zend_Db_Table {
    protected $_name    =   'orders_files';
    var $order_file_id;
    var $add_time;
    var $file_name;
    var $user_id;
    
    function addOrderFile(){
        $data   =   array(
            'add_time'  =>  date('Y-m-d H:i:s'),
            'file_name' =>  $this->file_name,
            'user_id'   =>  $this->user_id,
        );
        $order_file_id  =   $this->insert($data);
        return $order_file_id;
    }
    
    function getOrderFile(){
        $result =   '';
        if($this->user_id && $this->file_name){
            $where  =   " user_id = '".$this->user_id . "' and file_name = '".$this->file_name."'";
            $order_file_data    =   $this->fetchRow($where);
            if($order_file_data){
                return $order_file_data;
            }else{
                return $result;
            }
        }else{
            return $result;
        }
    }
}

?>
