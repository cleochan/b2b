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
    var $status;
    var $product_update_array;
    var $p_query_order  =   "add_time|DESC";
    var $p_qty_per_page =   50; //50 data / page
    var $p_current_page =   1; // start from the first page
    var $start_date;
    var $end_date;
    var $item_statuses;
    var $limit;
    var $product_id_array;
    
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
        $select->from($this->_name, array("product_id","product_code","product_title","brand","category_1","category_2","description","rrp","sell","freight","cost","weight","available","stock","image_1","image_2","image_3","image_4","image_5","image_6","length","width","height","despatch_pcode","courier", "status"));
        $select->where('status <> ?','-1');
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
    
    function Pagination()
    {
        //Get amount page qty
        $select = $this->select();
        $select->from($this->_name, array("count(product_id) as ct"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("add_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("add_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->product_code)
        {
            $select->where("product_code = ?", $this->merchant_ref);
            
            $cond[] = "product_code=".$this->merchant_ref;
        }
        if(isset($this->status)) //-1 == select all orders
        {
            $select->where("status = ?", $this->item_status);
            $cond[] = "status=".$this->item_status;
        }
        
        $result = $this->fetchRow($select);
        
        $total_page = ceil($result['ct'] / $this->p_qty_per_page);
        
        //Generate HTML
        if($total_page > 1)
        {
            if(1 == $this->p_current_page)
            {
                $cond[] = "p_current_page/".($this->p_current_page+1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/dd-product-list/".$params."'>Next >></a>";
            }elseif($total_page == $this->p_current_page){
                $cond[] = "p_current_page/".($this->p_current_page-1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/dd-product-list/".$params."'><< Previous</a>";
            }else{
                $cond[] = "p_current_page/".($this->p_current_page-1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/dd-product-list/".$params."'><< Previous</a>";
                $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                array_pop($cond);
                $cond[] = "p_current_page/".($this->p_current_page+1);
                
                $params_next = implode("/", $cond);
                $html .= "<a href='/admin/dd-product-list/".$params_next."'>Next >></a>";
            }
        }else{
            $html = "";
        }
        
        return $html;
    }
    
    function pushList(){
        $select = $this->select();
        $cond = array();
        if($this->start_date)
        {
            $select->where("add_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("add_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->product_code)
        {
            $select->where("product_code = ?", $this->product_code);
            
            $cond[] = "product_code=".$this->product_code;
        }
        if(isset($this->status)) //-1 == select all orders
        {
            $select->where("status = ?", $this->item_status);
            $cond[] = "status=".$this->item_status;
        }
        
        if($this->item_statuses && is_array($this->item_statuses)){
            $in_item_status = implode(',', $this->item_statuses);
            $select->where("status in (".$in_item_status.") ");
        }
        if($this->p_query_order)
        {
            $qorder = explode("|", $this->p_query_order);
            $select->order($qorder[0]." ".$qorder[1]);
        }
        if($this->p_qty_per_page && $this->p_current_page)
        {
            $select->limit($this->p_qty_per_page, $this->p_qty_per_page*($this->p_current_page-1));
        }
        if($this->limit)
        {
            $select->limit($this->limit);
            $select->order("add_time DESC");
        }
        
        $result= $this->fetchAll($select);
        
        return $result;
    }
    function updateProductStatus(){
        $where  =   ' 1=1 ';
        if($this->product_id_array){
            $where  .=   ' and product_id in ('.implode(',', $this->product_id_array).')';
        }
        
        $this->update(array('status'=>'0'), $where);
    }
    
    function updateProductStatusWithStatus(){
        $result =   '';
        $where  =   ' 1=1 ';
        if($this->product_id_array){
            $where  .=   ' and product_id in ('.implode(',', $this->product_id_array).')';
            $result =   $this->update(array('status'=>$this->status), $where);
        }
        return $result;
    }
    
    function getDdProductInfo(){
        $result =   array();
        if($this->product_id){
            $where      =   " product_id = '".$this->product_id."' ";
            $product    =   $this->fetchRow($where);
            if($product){
                $result =   $product;
            }
        }
        return $result;
    }
}

?>
