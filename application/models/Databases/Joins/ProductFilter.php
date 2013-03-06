<?php

class Databases_Joins_ProductFilter
{
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
    function Push($feed_info_array, $user_id)
    {
        $get_user_info = new Databases_Joins_GetUserInfo();
        $user_info = $get_user_info->GetUserInfo($user_id);
        
        $discount = (100 - $user_info['discount']) / 100; //get percentage
        
        //get markup
        $params_model = new Databases_Tables_Params();
        $cost_markup = $params_model->GetVal("cost_markup");
        
        $get_data_source = new Databases_Tables_Params();
        $data_source = $get_data_source->GetVal("product_info_table");
        
        if($data_source) // 1 or 2
        {
            $feed_category = $feed_info_array['users_feed']['feed_category'];
            $sku_included = $feed_info_array['users_feed']['sku_included'];
            $sku_excluded = $feed_info_array['users_feed']['sku_excluded'];
            $stock = $feed_info_array['users_feed']['stock'];
            $cost_protection = $feed_info_array['users_feed']['cost_protection'];
            
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
            $select->from($source_table, "*");
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
            $select->order("category_name ASC");
            $select->order("brand_name ASC");

            $data = $this->db->fetchAll($select);
            
            //update for discount/cost protection
            if(!empty($data))
            {
                foreach($data as $d_key => $d_val)
                {
                    $cal_result = $this->OfferPriceCalculation($d_val['offer_price'], $d_val['cost_price'], $discount, ($cost_markup/100));
                    
                    $data[$d_key]['offer_price'] = $cal_result[1]; //update price
                    
                    if($cost_protection && $cal_result[0])
                    {
                        $data[$d_key]['stock'] = 0; //erase stock
                    }
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
    
    function OfferPriceCalculation($original_offer_price, $original_cost_price, $merchant_discount, $cost_markup) //discount and markup are percentages and < 1
    {
        $offer_price_with_discount = $original_offer_price * $merchant_discount;
        
        $cost_price_with_markup = $original_cost_price * (1 + $cost_markup);
        
        $final_price = $offer_price_with_discount;
        
        if($offer_price_with_discount < $cost_price_with_markup)
        {
            $final_price = $cost_price_with_markup;
            
            if($final_price > $original_offer_price)
            {
                $final_price = $original_offer_price;
            }
        }
        
        if(($final_price / $original_offer_price) > $merchant_discount)
        {
            $affect_stock = 1;
        }else{
            $affect_stock = 0;
        }
        
        $result = array(0=>$affect_stock, 1=>$final_price);
        
        return $result;
    }
}