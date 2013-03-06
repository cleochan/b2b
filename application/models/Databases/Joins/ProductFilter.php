<?php

class Databases_Joins_ProductFilter
{
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
    function Push($feed_info_array)
    {
        $get_data_source = new Databases_Tables_Params();
        $data_source = $get_data_source->GetVal("product_info_table");
        
        $brand_model = new Databases_Tables_ProductBrands();
        $brands_array = $brand_model->GetAllBrands();
        
        $category_model = new Databases_Tables_ProductCategories();
        $categories_array = $category_model->GetAllCategories();
        
        if($data_source) // 1 or 2
        {
            $feed_category = $feed_info_array['users_feed']['feed_category'];
            $sku_included = $feed_info_array['users_feed']['sku_included'];
            $sku_excluded = $feed_info_array['users_feed']['sku_excluded'];
            $stock = $feed_info_array['users_feed']['stock'];
            
            if($feed_category)
            {
                $category_array = explode(",", $feed_category);
            }
            
            if($sku_included)
            {
                $sku_included_array = explode(",", $sku_included);
            }
            
            if($sku_excluded)
            {
                $sku_excluded_array = explode(",", $sku_excluded);
            }
            
            $source_table = "product_info_".$data_source;

            $select = $this->db->select();
            $select->from($source_table." as p", "*");
            $select->joinLeft("product_brands as b", "b.brand_id=p.brand", "brand_name");
            $select->joinLeft("product_categories as c", "c.category_id=p.category", "category_name");
            if($category_array)
            {
                $select->where("category IN (?)", $category_array);
            }
            if($sku_included_array)
            {
                $select->where("supplier_sku IN (?)", $sku_included_array);
            }
            if($sku_excluded_array)
            {
                $select->where("supplier_sku NOT IN (?)", $sku_excluded_array);
            }
            switch ($stock)
            {
                case 2: //in stock
                    $select->where("stock > ?", 0);
                    break;
                case 3: //out of stock
                    $select->where("stock = ?", 0);
                    break;
                default :
                    break;
            }
            $select->order("p.category ASC");
            $select->order("p.brand ASC");

            $data = $this->db->fetchAll($select);
            
            //update brand and category
            if(!empty($data))
            {
                foreach($data as $d_key => $d_val)
                {
                    $data[$d_key]['brand'] = $brands_array[$d_val['brand']];
                    $data[$d_key]['category'] = $categories_array[$d_val['category']];
                }
            }
                
        }else{
            $data = array();
        }
        
        return $data;
    }
    
    function GetFeedDefinitionInfo($users_feed_id)
    {
        $rows = $this->db->select();
        $rows->from("users_feed_definition as f", "*");
        $rows->joinLeft("feed_dictionary as d", "f.feed_dictionary_id=d.feed_dictionary_id", "column_product_mapping");
        $rows->where("f.users_feed_id = ?", $users_feed_id);
        $rows->order("f.sequence ASC");
        $data = $this->db->fetchAll($rows);
        
        return $data;
    }
}