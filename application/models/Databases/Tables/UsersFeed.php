<?php

class Databases_Tables_UsersFeed extends Zend_Db_Table
{
    protected $_name = 'users_feed';
    var $user_id;
    var $feed_name;
    var $feed_extension;
    var $feed_delimeter;
    var $special_delimeter;
    var $feed_qualifier;
    var $feed_category; //array
    var $sku_included;
    var $sku_excluded;
    var $stock;
    var $sc_class;
    var $supplier_type;
    var $cost_protection;
    var $feed_column_definition; //array
    var $feed_product_type;
    
    function GetFeedInfo($user_id)
    {
        $result =   array();
        $row = $this->fetchRow("user_id='".$user_id."'");
        if($row){
            $result =   $row->toArray();
            if($result['sc_class']){
                $sc_class_array =   explode(',', $result['sc_class']);
                foreach( $sc_class_array as $sc_class){
                    $result['sc_class_array'][$sc_class]   =   $sc_class;
                }
            }
            if($result['supplier_type']){
                $supplier_type_array    =   explode(',', $result['supplier_type']);
                foreach ($supplier_type_array as $supplier_type){
                    $result['supplier_type_array'][$supplier_type] =   $supplier_type;
                }
            }
        }
        return $result;
    }
    
    function UpdateFeed()
    {
        $product_categories_model = new Databases_Tables_ProductCategories();
        $is_all = $product_categories_model->IsAll(count($this->feed_category));
        
        //just stock 0 as ALL to save storage
        if($is_all)
        {
            $category_string = NULL;
        }else{
            $category_string = implode(",", $this->feed_category);
        }
        
        $row = $this->fetchRow("user_id = '".$this->user_id."'");
        
        if($row->user_id) //update
        {
            $row->feed_name         =   $this->feed_name;
            $row->feed_extension    =   $this->feed_extension;
            $row->feed_delimeter    =   $this->feed_delimeter;
            $row->special_delimeter =   $this->special_delimeter;
            $row->feed_qualifier    =   $this->feed_qualifier;
            $row->feed_category     =   $category_string;
            $row->sku_included      =   $this->sku_included;
            $row->sku_excluded      =   $this->sku_excluded;
            $row->sc_class          =   $this->sc_class;
            $row->supplier_type     =   $this->supplier_type;
            $row->stock             =   $this->stock;
            $row->cost_protection   =   $this->cost_protection;
            $row->feed_product_type      =   $this->feed_product_type;
            $row->save();
            
            $users_feed_id = $row->users_feed_id;
        }else{ //insert
            $data = array(
                "user_id"           =>  $this->user_id,
                "feed_name"         =>  $this->feed_name,
                "feed_extension"    =>  $this->feed_extension,
                "feed_delimeter"    =>  $this->feed_delimeter,
                "special_delimeter" =>  $this->special_delimeter,
                "feed_qualifier"    =>  $this->feed_qualifier,
                "feed_category"     =>  $category_string,
                "sku_included"      =>  $this->sku_included,
                "sku_excluded"      =>  $this->sku_excluded,
                "sc_class"          =>  $this->sc_class,
                "supplier_type"     =>  $this->supplier_type,
                "stock"             =>  $this->stock,
                "cost_protection"   =>  $this->cost_protection,
                "feed_product_type" =>  $this->feed_product_type,
            );
            
            $users_feed_id = $this->insert($data);
        }
        
        //update users_feed_definition
        $user_feed_definition_model = new Databases_Tables_UsersFeedDefinition();
        $user_feed_definition_model->users_feed_id = $users_feed_id;
        $user_feed_definition_model->feed_column_definition = $this->feed_column_definition;
        
        if($user_feed_definition_model->UpdateFeedDefinition())
        {
            $result = 1;
        }else{
            $result = 0;
        }
        
        return $result;
    }
}