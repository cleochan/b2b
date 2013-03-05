<?php

class Databases_Tables_UsersFeed extends Zend_Db_Table
{
    protected $_name = 'users_feed';
    var $user_id;
    var $feed_name;
    var $feed_extension;
    var $feed_delimeter;
    var $feed_qualifier;
    var $feed_category; //array
    var $sku_included;
    var $sku_excluded;
    var $stock;
    var $feed_column_definition; //array
    
    function GetFeedInfo($user_id)
    {
        $row = $this->fetchRow("user_id='".$user_id."'");
        
        return $row;
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
            $row->feed_name = $this->feed_name;
            $row->feed_extension = $this->feed_extension;
            $row->feed_delimeter = $this->feed_delimeter;
            $row->feed_qualifier = $this->feed_qualifier;
            $row->feed_category = $category_string;
            $row->sku_included = $this->sku_included;
            $row->sku_excluded = $this->sku_excluded;
            $row->stock = $this->stock;
            $row->save();
            
            $users_feed_id = $row->users_feed_id;
        }else{ //insert
            $data = array(
                "user_id" => $this->user_id,
                "feed_name" => $this->feed_name,
                "feed_extension" => $this->feed_extension,
                "feed_delimeter" => $this->feed_delimeter,
                "feed_qualifier" => $this->feed_qualifier,
                "feed_category" => $category_string,
                "sku_included" => $this->sku_included,
                "sku_excluded" => $this->sku_excluded,
                "stock" => $this->stock
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