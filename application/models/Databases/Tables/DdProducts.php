<?php
class Databases_Tables_DdProducts extends Zend_Db_Table
{
    protected $_name = 'dd_products';
    
    var $product_id;
    var $product_code;
    var $product_title;
    var $brand;
    var $category_1;
    var $category_2;
    var $description;
    var $rrp;
    var $sell;
    var $freight;
    var $cost;
    var $weight;
    var $available;
    var $stock;
    var $image_1;
    var $image_2;
    var $image_3;
    var $image_4;
    var $image_5;
    var $image_6;
    var $length;
    var $width;
    var $height;
    var $despatch_pcode;
    var $courier;
    var $cc_price;
    var $cc_supplier_sku;
    var $add_time;
    var $update_time;
    
    var $product_update_array;
    
    function addDdProduct(){
        $data   =   array(
            'product_id'    =>  $this->product_id,
            'product_code'  =>  $this->product_code,
            'product_title' =>  $this->product_title,
            'brand'         =>  $this->brand,
            'category_1'    =>  $this->category_1,
            'category_2'    =>  $this->category_2,
            'description'   =>  $this->description,
            'rrp'           =>  $this->rrp,
            'sell'          =>  $this->sell,
            'freight'       =>  $this->freight,
            'cost'          =>  $this->cost,
            'weight'        =>  $this->weight,
            'available'     =>  $this->available,
            'stock'         =>  $this->stock,
            'image_1'       =>  $this->image_1,
            'image_2'       =>  $this->image_2,
            'image_3'       =>  $this->image_3,
            'image_4'       =>  $this->image_4,
            'image_5'       =>  $this->image_5,
            'image_6'       =>  $this->image_6,
            'length'        =>  $this->length,
            'width'         =>  $this->width,
            'height'        =>  $this->height,
            'despatch_pcode'=>  $this->despatch_pcode,
            'courier'       =>  $this->courier,
            'cc_price'      =>  $this->cc_price,
            'cc_supplier_sku'=> $this->cc_supplier_sku,
            'add_time'      =>  date('Y-m-d H:i:s'),
        );
        $product_id   =   $this->insert($data);
        return $product_id;
    }
    
    function updateDdProduct(){
        $product_id =   '';
        if($this->product_id){
            $where      =   " product_id = '".$this->product_id."' ";
            $product    =   $this->fetchRow($where);
            if($product->product_id){
                $product->stock         =   $this->stock;
                $product->update_time   =   date('Y-m-d H:i:s');
                $product->save();
                //$product_id   =   $product->product_id;
            }else{
                $product_id   =   $this->addDdProduct();
            }
        }
        return  $product_id;
    }
    
    function setStockZero(){
        $data   =   array(
            'stock' =>  0,
        );
        $where  =   ' 1=1 ';
        $this->update($data, $where);
    }
    
    function getAllDdProducts(){
        $data   =   array();
        $select = $this->select();
        $select->from($this->_name, array("product_code","product_title","brand","category_1","category_2","description","rrp","sell","freight","cost","weight","available","stock","image_1","image_2","image_3","image_4","image_5","image_6","length","width","height","despatch_pcode","courier"));
        $select->order("brand ASC");
        $data = $this->fetchAll($select);
        return $data;
    }
    
    function getDdProductPrice(){
        $result =   array();
        if($this->product_code){
            $where      =   " product_code = '".$this->product_code."-TP' ";
            $product    =   $this->fetchRow($where);
            if($product){
                $result =   $product;
            }
        }
        return $result;
    }
}

?>
