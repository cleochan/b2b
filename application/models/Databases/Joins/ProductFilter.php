<?php

class Databases_Joins_ProductFilter
{
    var $xml_array;
    
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
        $data_source = $params_model->GetVal("product_info_table");
        
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
            }else{
                $category_array = array();
            }
            
            if($sku_included)
            {
                $sku_included_array = explode(",", $sku_included);
            }else{
                $sku_included_array = array();
            }
            
            if($sku_excluded)
            {
                $sku_excluded_array = explode(",", $sku_excluded);
            }else{
                $sku_excluded_array = array();
            }
            
            $source_table = "product_info_".$data_source;

            $select = $this->db->select();
            $select->from($source_table, "*");
            if(!empty($category_array))
            {
                $select->where("category_id IN (?)", $category_array);
            }
            if(!empty($sku_included_array))
            {
                $select->where("supplier_sku IN (?)", $sku_included_array);
            }
            if(!empty($sku_excluded_array))
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
    
    function GetSkuPrices($sku, $user_id)
    {
        $result = array();
        
        $get_user_info = new Databases_Joins_GetUserInfo();
        $user_info = $get_user_info->GetUserInfo($user_id);
        
        $discount = (100 - $user_info['discount']) / 100; //get percentage
        
        //get markup
        $params_model = new Databases_Tables_Params();
        $cost_markup = $params_model->GetVal("cost_markup");
        $data_source = $params_model->GetVal("product_info_table");
        
        if($data_source && $sku) // 1 or 2
        {
            $product_select = $this->db->select();
            $product_select->from("product_info_".$data_source, array("supplier_sku", "offer_price", "cost_price", "shipping"));
            $product_select->where("supplier_sku = ?", $sku);
            $product = $this->db->fetchRow($product_select);
            
            if($product['supplier_sku'])
            {
                $offer_price_cal = $this->OfferPriceCalculation($product['offer_price'], $product['cost_price'], $discount, $cost_markup);
                
                $result['offer_price'] = $offer_price_cal[1];
                $result['shipping'] = $product['shipping'];
            }
        }
        
        return $result;
    }
    
    function TruncateProductTable($num)
    {
        $result = FALSE;
        
        if(in_array($num, array(1,2)))
        {
            if($this->db->query("truncate table product_info_".$num))
            {
                $result = TRUE;
            }
        }
        
        return $result;
    }
    
    function PostXmlToRefreshProducts()
    {
        $params_model = new Databases_Tables_Params();
        $product_request_qty_per_page = $params_model->GetVal("product_request_qty_per_page");
        $product_request_page_amount = $params_model->GetVal("product_request_page_amount");
        $product_request_current_page = $params_model->GetVal("product_request_current_page");
        
        $original_xml_array = array(
            "product_request_qty_per_page" => $product_request_qty_per_page,
            "product_request_page_amount" => $product_request_page_amount,
            "product_request_page" => ($product_request_current_page + 1)
        );
        
        $api_model = new Algorithms_Core_Api();
        $api_model->api_target = 1; //Internal Admin
        $api_model->api_type = 1; //GetProductList
        $api_model->original_xml_array = $original_xml_array;

        $api_model->Push();
    }
    
    function InsertNewProductsFromApi()
    {
        $result = FALSE;
        
        $params_model = new Databases_Tables_Params();
        $secondary = $params_model->GetSecondaryProductTableName();
        
        if(!empty($this->xml_array))
        {
            foreach($this->xml_array as $xml_array)
            {
                $row = array(
                    "supplier_sku" => $xml_array['supplier_sku'],
                    "brand_id" => $xml_array['brand_id'],
                    "brand_name" => $xml_array['brand_name'],
                    "mpn" => $xml_array['mpn'],
                    "stock" => $xml_array['stock'],
                    "offer_price" => $xml_array['offer_price'],
                    "cost_price" => $xml_array['cost_price'],
                    "product_name" => $xml_array['product_name'],
                    "features1" => $xml_array['features1'],
                    "features2" => $xml_array['features2'],
                    "features3" => $xml_array['features3'],
                    "features4" => $xml_array['features4'],
                    "features5" => $xml_array['features5'],
                    "product_details" => $xml_array['product_details'],
                    "specification" => $xml_array['specification'],
                    "dimension" => $xml_array['dimension'],
                    "colour" => $xml_array['colour'],
                    "size" => $xml_array['size'],
                    "factory_url" => $xml_array['factory_url'],
                    "package_content" => $xml_array['package_content'],
                    "warranty" => $xml_array['warranty'],
                    "category_id" => $xml_array['category_id'],
                    "category_name" => $xml_array['category_name'],
                    "weight" => $xml_array['weight'],
                    "image_url_1" => $xml_array['image_url_1'],
                    "image_url_2" => $xml_array['image_url_2'],
                    "image_url_3" => $xml_array['image_url_3'],
                    "image_url_4" => $xml_array['image_url_4'],
                    "image_url_5" => $xml_array['image_url_5'],
                    "pm" => $xml_array['pm'],
                    "options" => $xml_array['options'],
                    "search_keyword" => $xml_array['search_keyword'],
                    "list_price" => $xml_array['list_price'],
                    "shipping" => $xml_array['shipping']
                );
                
                try {
                    $this->db->insert($secondary['table_name'], $row);
                } catch (Zend_Exception $e) {
                    echo "Message: " . $e->getMessage() . "\n";
                }
            }
            
            $result = TRUE;
        }
        
        return $result;
    }
}