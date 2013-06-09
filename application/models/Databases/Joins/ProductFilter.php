<?php

class Databases_Joins_ProductFilter
{
    var $xml_array;
    var $data_source;
    var $product_id;
    var $supplier_sku;
    var $retailer_sku;
    var $product_name;
    var $retailer_account_id;
    var $wholesale_cost;
    var $street_price;
    var $estimated_shipping_cost;
    var $estimated_handling_fee;
    var $quantity_available;
    var $long_html_description;
    var $short_html_description;
    var $long_text_description;
    var $short_text_description;
    var $inventory_id;
    var $brand;
    var $brand_id;
    var $manufacturer;
    var $condition_id;
    var $last_update_date;
    var $mpn;
    var $upc;
    var $ean;
    var $isbn;
    var $gtin;
    var $country_of_origin;
    var $catalog;
    var $catalog_start_date;
    var $catalog_end_date;
    var $category;
    var $category_id;
    var $cross_sell_skus;
    var $package_weight;
    var $package_weight_units;
    var $package_length;
    var $package_height;
    var $package_depth;
    var $package_dimension_units;
    var $ships_freight;
    var $freight_class;
    var $ships_alone;
    var $max_ship_single_box;
    var $length;
    var $height;
    var $depth;
    var $dimension_units;
    var $weight;
    var $weight_units;
    var $dimension_description;
    var $min_purchase_quantity;
    var $max_purchase_quantity;
    var $bin_number;
    var $accessory_skus;
    var $keywords;
    var $pers_available;
    var $gift_wrap_available;
    var $details;
    var $features;
    var $specification;
    var $warranty;
    var $discontinue_flag;
    var $case_pack_length;
    var $case_pack_height;
    var $case_pack_depth;
    var $case_pack_units;
    var $case_pack_quantity;
    var $normal_count;
    var $repeat_count;
    
    
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
                    $select->where("quantity_available > ?", 0);
                    break;
                case 3: //out of stock
                    $select->where("quantity_available = ?", 0);
                    break;
                default :
                    break;
            }
            $select->order("category ASC");
            $select->order("brand ASC");
            
            $data = $this->db->fetchAll($select);
            
            //update for discount/cost protection
            if(!empty($data))
            {
                foreach($data as $d_key => $d_val)
                {
                    $cal_result = $this->OfferPriceCalculation($d_val['street_price'], $d_val['wholesale_cost'], $discount, ($cost_markup/100));
                    
                    $data[$d_key]['street_price'] = $cal_result[1]; //update price
                    
                    if($cost_protection && $cal_result[0])
                    {
                        $data[$d_key]['quantity_available'] = 0; //erase stock
                    }
                    
                    //format the other prices
                    $data[$d_key]['estimated_shipping_cost'] = number_format($d_val['estimated_shipping_cost'], 2);
                    //$data[$d_key]['list_price'] = number_format($d_val['list_price'], 2);
                    $data[$d_key]['quantity_available'] = (0 < $d_val['quantity_available'])?$d_val['quantity_available']:0;
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
    
    // Very sensitive function, don't change anything if you are not 100% sure.
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
        
        $result = array(0=>$affect_stock, 1=>number_format($final_price, 2));
        
        return $result;
    }
    
    function PriceFormat($val)
    {
        return number_format($val, 2);
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
            $product_select->from("product_info_".$data_source, array("product_id","supplier_sku", "street_price", "wholesale_cost", "estimated_shipping_cost", "estimated_handling_fee", "quantity_available","flat_rate_shipping","freight_class"));
            $product_select->where("supplier_sku = ?", $sku);
            $product = $this->db->fetchRow($product_select);
            if($product['supplier_sku'])
            {
                $offer_price_cal = $this->OfferPriceCalculation($product['street_price'], $product['wholesale_cost'], $discount, $cost_markup/100);
                
                $result['street_price'] = $offer_price_cal[1];
                $result['estimated_shipping_cost'] = $product['estimated_shipping_cost'];
                $result['estimated_handling_fee'] = $product['estimated_handling_fee'];
                $result['quantity_available'] = $product['quantity_available'];
                $result['flat_rate_shipping'] = $product['flat_rate_shipping'];
                $result['freight_class'] = $product['freight_class'];
                $result['product_id']    = $product['product_id'];
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
    
    function truncateProduct()
    {
        if($this->data_source == '1'){
            $truncate_table =   'product_info_2';
        }else
        {
            $truncate_table =   'product_info_1';
        }
        $this->db->query('truncate table '.$truncate_table);
        $this->db->query('truncate table product_info_repeat');
    }
    
    function AddProduct()
    {
        if($this->data_source == '1'){
            $source_table =   'product_info_2';
        }else
        {
            $source_table =   'product_info_1';
        }
        
        $product_select =   $this->db->select();
        
        $product_select->from($source_table,array('count(product_id) as count'));
        $product_select->where("product_id = ?", $this->product_id);
        $count  =   $this->db->fetchRow($product_select);
        if( $count['count'] == 0)
        {
            $data   =   array(

                'product_id'        =>  $this->product_id,
                'supplier_sku'      =>  $this->supplier_sku,
                'retailer_sku'      =>  $this->retailer_sku,
                'product_name'      =>  $this->product_name,
                'retailer_account_id'   =>  $this->retailer_account_id,
                'wholesale_cost'        =>  $this->wholesale_cost,
                'street_price'          =>  $this->street_price,
                'estimated_shipping_cost'   =>  $this->estimated_shipping_cost,
                'estimated_handling_fee'    =>  $this->estimated_handling_fee,
                'quantity_available'        =>  $this->quantity_available,
                'long_html_description'     =>  $this->long_html_description,
                'short_html_description'    =>  $this->short_html_description,
                'long_text_description'     =>  $this->long_text_description,
                'short_text_description'    =>  $this->short_text_description,
                'inventory_id'              =>  $this->inventory_id,
                'brand'                     =>  $this->brand,
                'brand_id'                  =>  $this->brand_id,
                'manufacturer'              =>  $this->manufacturer,
                'condition_id'              =>  $this->condition_id,
                'last_update_date'          =>  $this->last_update_date,
                'mpn'                       =>  $this->mpn,
                'upc'                       =>  $this->upc,
                'ean'                       =>  $this->ean,
                'isbn'                      =>  $this->isbn,
                'gtin'                      =>  $this->gtin,
                'country_of_origin'         =>  $this->country_of_origin,
                'catalog'                   =>  $this->catalog,
                'catalog_start_date'        =>  $this->catalog_start_date,
                'catalog_end_date'          =>  $this->catalog_end_date,
                'category'                  =>  $this->category,
                'category_id'               =>  $this->category_id,
                'cross_sell_skus'           =>  $this->cross_sell_skus,
                'package_weight'            =>  $this->package_weight,
                'package_weight_units'      =>  $this->package_weight_units,
                'package_length'            =>  $this->package_length,
                'package_height'            =>  $this->package_height,
                'package_depth'             =>  $this->package_depth,
                'package_dimension_units'   =>  $this->package_dimension_units,
                'ships_freight'             =>  $this->ships_freight,
                'freight_class'             =>  $this->freight_class,
                'ships_alone'               =>  $this->ships_alone,
                'max_ship_single_box'       =>  $this->max_ship_single_box,
                'length'                    =>  $this->length,
                'height'                    =>  $this->height,
                'depth'                     =>  $this->depth,
                'dimension_units'           =>  $this->dimension_units,
                'weight'                    =>  $this->weight,
                'weight_units'              =>  $this->weight_units,
                'dimension_description'     =>  $this->dimension_description,
                'min_purchase_quantity'     =>  $this->min_purchase_quantity,
                'max_purchase_quantity'     =>  $this->max_purchase_quantity,
                'bin_number'                =>  $this->bin_number,
                'accessory_skus'            =>  $this->accessory_skus,
                'keywords'                  =>  $this->keywords,
                'pers_available'            =>  $this->pers_available,
                'gift_wrap_available'       =>  $this->gift_wrap_available,
                'details'                   =>  $this->details,
                'features'                  =>  $this->features,
                'specification'             =>  $this->specification,
                'warranty'                  =>  $this->warranty,
                'discontinue_flag'          =>  $this->discontinue_flag,
                'case_pack_length'          =>  $this->case_pack_length,
                'case_pack_height'          =>  $this->case_pack_height,
                'case_pack_depth'           =>  $this->case_pack_depth,
                'case_pack_units'           =>  $this->case_pack_units,
                'case_pack_quantity'        =>  $this->case_pack_quantity,
            );
            $this->normal_count++;
            $this->db->insert($source_table,$data);
        }else{
            $data   =   array(
                'product_id'    =>  $this->product_id,
                'supplier_sku'  =>  $this->supplier_sku,
                'product_name'  =>  $this->product_name,
                'add_date'      =>  date('Y-m-d H:i:s'),
            );
            $this->repeat_count++;
            $this->db->insert('product_info_repeat',$data);
        }
        $count['normal_count']   =   $this->normal_count;
        $count['repeat_count']   =   $this->repeat_count;
        return  $count;
    }
    
    function getProductAll()
    {
        $params_model   =   new Databases_Tables_Params();
        $data_source    =   $params_model->GetVal("product_info_table");
        $products_select =   $this->db->select();
        $products_select->from("product_info_".$data_source);
        $products       =   $this->db->fetchAll($products_select);
        if($products)
        {
            return $products;
        }else
        {
            return FALSE;
        }
    }
    
    function getEstimatedShippingCost($product_id,$zip,$qty){
        $params_model   =   new Databases_Tables_Params();
        $param_postage_api_url    =   $params_model->GetVal("postage_api_url");
        $postage_api_url    =   $param_postage_api_url.'?pid='.$product_id.'&zip='.$zip.'&qty='.$qty;
        $string =   file_get_contents($postage_api_url);
        $string =   str_replace("<font size='18' color='#000000'>Postage</font><br><font size='26' color='#000000'><b>$",'',$string);
        $estimated_shipping_cost    =   str_replace("</b></font>",'',$string);
        if($estimated_shipping_cost){
            return  $estimated_shipping_cost;
        }else{
            return 0;
        }
    }
            
    function updateEstimatedShippingCost($postage_api_url,$product_id)
    {
        $params_model   =   new Databases_Tables_Params();
        $data_source    =   $params_model->GetVal("product_info_table");
        $string =   file_get_contents($postage_api_url);
        $string =   str_replace("<font size='18' color='#000000'>Postage</font><br><font size='26' color='#000000'><b>$",'',$string);
        $estimated_shipping_cost    =   str_replace("</b></font>",'',$string);
        $where  =   $this->db->quoteInto('product_id = ?', $product_id);
        $set    =   array (
            'estimated_shipping_cost' => $estimated_shipping_cost,
        );
        $row    =   $this->db->update("product_info_".$data_source, $set, $where);
        if($row){
            return $estimated_shipping_cost;
        }else{
            return FALSE;
        }
    }
}