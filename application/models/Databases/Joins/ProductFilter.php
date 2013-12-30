<?php
/**
 * Resource model for product handling
 * @category    Databases
 * @package     Databases_Joins
 */
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
    var $supplier_price;
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
    var $sc_class;
    var $shipping_courier;
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
    
    var $imageURL0;
    var $imageURL1;
    var $imageURL2;
    var $imageURL3;
    var $imageURL4;
    var $imageURL5;
    var $options;
    var $dimension;
    var $description;
    var $product_code_type;
    var $images;
    var $images_path;
    var $express_post_2000;
    var $postage2000;
    var $all_category_data;
    var $dd_all_category_data;
    
    var $dd_new_product_ids_array;
    
    var $order_skus_array;
    
    function __construct(){
        $this->db = Zend_Registry::get("db");
        $category_model     =   new Databases_Tables_ProductCategories();
        $dd_category_model  =   new Databases_Tables_DdCategories();
        $this->dd_all_category_data =   $dd_category_model->getAllCategoryArray();
        $this->all_category_data  =   $category_model->getAllCategoryArray();  
    }
    
    /**
     * Get product data with feed_info
     * get product data
     * start loop
     * OfferPriceCalculation
     * finish loop
     * return array
     * @param array $feed_info_array
     * @param int $user_id
     * @return array
     */
    function Push($feed_info_array, $user_id)
    {
        $get_user_info = new Databases_Joins_GetUserInfo();
        $user_info = $get_user_info->GetUserInfo($user_id);
        
        $discount = (100 - $user_info['discount']) / 100; //get percentage
        
        //get markup
        $params_model = new Databases_Tables_Params();
        $product_category_mode  =   new Databases_Tables_ProductCategories();
        $cost_markup = $params_model->GetVal("cost_markup");
        $data_source = $params_model->GetVal("product_info_table");
        $shipping_courier   =   $params_model->GetVal('shipping_courier');
        if($data_source) // 1 or 2
        {
            $feed_category = $feed_info_array['users_feed']['feed_category'];
            $sku_included = $feed_info_array['users_feed']['sku_included'];
            $sku_excluded = $feed_info_array['users_feed']['sku_excluded'];
            $stock = $feed_info_array['users_feed']['stock'];
            $sc_class   =   $feed_info_array['users_feed']['sc_class'];
            $supplier_type  =   $feed_info_array['users_feed']['supplier_type'];
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
            /*for dealsdirect feed*/
            if($user_id == 8)
            {
                $select->join('b2b_dd_category', 'b2b_dd_category.category_id = '.$source_table.'.category_id', 'dd_category_id');
                $select->where("supplier_sku not REGEXP '([\s\S]*)(\/)([\s\S]*)'");
                $select->where("supplier_sku not REGEXP '([\s\S]*)[\+]([\s\S]*)'");
                //$select->where("char_length(product_name) <= 55 ");
                $select->where('quantity_available >= ?',40);
                $select->where("length > ?", 0);
                $select->where("height > ?", 0);
                $select->where("depth > ?", 0);
            }
            
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
            
            if($sc_class)
            {
                $sc_class_array =   explode(",", $sc_class);
                $select->where("sc_class IN (?)", $sc_class_array);
            }
            
            if($supplier_type)
            {
                $supplier_type_array    =   explode(",", $supplier_type);
                $select->where("country_of_origin IN (?)", $supplier_type_array);
            }
            
            switch ($stock)
            {
                case 2: //in stock
                    $select->where("quantity_available > ?", 0);
                    $select->where("sc_class in ($shipping_courier)");
                    break;
                case 3: //out of stock
                    $select->where("quantity_available = ?", 0);
                    break;
                default :
                    break;
            }
            $select->where("product_code_type <> 'PART' or product_code_type is null");
            $select->order("category ASC");
            $select->order("brand ASC");
            
            $data = $this->db->fetchAll($select);

            //update for discount/cost protection
            if(!empty($data))
            {
                foreach($data as $d_key => $d_val)
                {
                    /*
                    $images_array   = explode('|', $d_val['images']);
                    $i =    0;
                    foreach ($images_array as $image_info){
                        $d_val['imageURL'.$i]   =   $d_val['images_path'].$image_info;
                        $i++;
                    }
                     */
                    if($user_id == 8){//dealsdirect's price is use street_price not supplier_price
                        $cal_result = $this->OfferPriceCalculation($d_val['street_price'], $d_val['wholesale_cost'], $discount, ($cost_markup/100));
                    }else{
                        $cal_result = $this->OfferPriceCalculation($d_val['supplier_price'], $d_val['wholesale_cost'], $discount, ($cost_markup/100));
                    }
                    
                    if($d_val['category_id']){
                        if($user_id == 8){
                            $cal_result[1]  =   round(($cal_result[1] / 11) * 10, 2); //将Cost的GST去处，算法是在原值上除以11再乘以10, just for dealsdirect
                            $category_array = $this->getDDProductCategoryInfo($d_val['dd_category_id']);
                        }else{
                            $category_array = $this->getProductCategoryInfo($d_val['category_id']);
                        }

                        $data[$d_key]['main_category']  =   @$category_array[0];
                        $data[$d_key]['sub_category']   =   @$category_array[1];
                        $data[$d_key]['bottom_category']=   @$category_array[2];
                    }
                    $data[$d_key]['original_supplier_price'] = $d_val['supplier_price']; //keep original price
                    $data[$d_key]['supplier_price'] = $cal_result[1]; //update price
                    
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
    
    /**
     * Get User Feed Definition Info 
     * @param int $users_feed_id
     * @return array
     */
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
    /**
     * calculate supplier price
     * 
     * @param float $original_offer_price
     * @param float $original_cost_price
     * @param float $merchant_discount
     * @param float $cost_markup
     * @return int
     */
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
    
    /**
     * Get product price info
     * get merchant discount
     * get product info
     * calculate supplier price
     * return result array
     * @param string $sku
     * @param int $user_id
     * @return array
     */
    function GetSkuPrices($sku, $user_id)
    {
        $result = array();
        
        $get_user_info = new Databases_Joins_GetUserInfo();
        $dd_product_model   =   new Databases_Tables_DdProducts();
        $user_info = $get_user_info->GetUserInfo($user_id);
        
        $discount = (100 - $user_info['discount']) / 100; //get percentage
        
        //get markup
        $params_model = new Databases_Tables_Params();
        $cost_markup = $params_model->GetVal("cost_markup");
        $data_source = $params_model->GetVal("product_info_table");
        if($data_source && $sku) // 1 or 2
        {
            $product_select = $this->db->select();
            $product_select->from("product_info_".$data_source, array("product_id","product_name","supplier_sku", "street_price","supplier_price", "wholesale_cost", "estimated_shipping_cost", "estimated_handling_fee", "quantity_available","sc_class", "shipping_courier", "length", "height", "depth", "weight"));
            $product_select->where("supplier_sku = ?", $sku);
            $product = $this->db->fetchRow($product_select);
            if($product['supplier_sku'])
            {
                if($user_id == 8){ //dealsdirect's price is use street_price not supplier_price
                    $dd_product_model->product_code =   $sku;
                    $dd_product_info    =   $dd_product_model->getDdProductPrice();
                    if($dd_product_info){
                        $offer_price_cal[0] =   0;
                        $offer_price_cal[1] = round($dd_product_info['cost'] * 1.1, 2);
                    }else{
                        $offer_price_cal = $this->OfferPriceCalculation($product['street_price'], $product['wholesale_cost'], $discount, $cost_markup/100);
                    }
                }else{
                    $offer_price_cal = $this->OfferPriceCalculation($product['supplier_price'], $product['wholesale_cost'], $discount, $cost_markup/100);
                }
                $result['supplier_price'] = $offer_price_cal[1];
                $result['estimated_shipping_cost'] = $product['estimated_shipping_cost'];
                $result['estimated_handling_fee'] = $product['estimated_handling_fee'];
                $result['quantity_available'] = $product['quantity_available'];
                $result['wholesale_cost'] = $product['wholesale_cost'];
                if($user_id == 8){
                    if($product['length'] >= 105 || $product['height'] >= 105 || $product['depth'] >= 105 || $product['weight'] >= 32){
                        if($product['sc_class']==3){
                            $result['sc_class'] =   22;
                            $result['shipping_courier'] =   'eParcel - Deals Direct';
                        }else{
                            $result['sc_class'] =   23;
                            $result['shipping_courier'] =   'Allied Express - Deals Direct';
                        }
                    }else{
                        $result['sc_class'] =   22;
                        $result['shipping_courier'] =   'eParcel - Deals Direct';
                    }
                }else{
                    $result['sc_class']         = $product['sc_class'];
                    $result['shipping_courier'] =   $product['shipping_courier'];
                }
                $result['supplier_sku']   =   $product['supplier_sku'];
                $result['product_id']    = $product['product_id'];
                $result['product_name']    = $product['product_name'];
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
    
    /**
     * Add new product 
     * @return array
     */
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
                'wholesale_cost'        =>  $this->wholesale_cost,
                'street_price'          =>  $this->street_price,
                'supplier_price'        =>  $this->supplier_price,
                'estimated_shipping_cost'   =>  $this->estimated_shipping_cost,
                'estimated_handling_fee'    =>  $this->estimated_handling_fee,
                'quantity_available'        =>  $this->quantity_available,
                'long_html_description'     =>  $this->long_html_description,
                'brand'                     =>  $this->brand,
                'manufacturer'              =>  $this->manufacturer,
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
                'sc_class'                  =>  $this->sc_class,
                'shipping_courier'          =>  $this->shipping_courier,
                'max_ship_single_box'       =>  $this->max_ship_single_box,
                'length'                    =>  $this->length,
                'height'                    =>  $this->height,
                'depth'                     =>  $this->depth,
                'dimension_units'           =>  $this->dimension_units,
                'weight'                    =>  $this->weight,
                'weight_units'              =>  $this->weight_units,
                'min_purchase_quantity'     =>  $this->min_purchase_quantity,
                'max_purchase_quantity'     =>  $this->max_purchase_quantity,
                'bin_number'                =>  $this->bin_number,
                'accessory_skus'            =>  $this->accessory_skus,
                'keywords'                  =>  $this->keywords,
                'features'                  =>  $this->features,
                'specification'             =>  $this->specification,
                'warranty'                  =>  $this->warranty,
                'discontinue_flag'          =>  $this->discontinue_flag,
                'case_pack_length'          =>  $this->case_pack_length,
                'case_pack_height'          =>  $this->case_pack_height,
                'case_pack_depth'           =>  $this->case_pack_depth,
                'case_pack_units'           =>  $this->case_pack_units,
                'case_pack_quantity'        =>  $this->case_pack_quantity,
                'imageURL0'                 =>  $this->imageURL0,
                'imageURL1'                 =>  $this->imageURL1,
                'imageURL2'                 =>  $this->imageURL2,
                'imageURL3'                 =>  $this->imageURL3,
                'imageURL4'                 =>  $this->imageURL4,
                'imageURL5'                 =>  $this->imageURL5,
                'images'                    =>  $this->images,
                'images_path'               =>  $this->images_path,
                'product_code_type'         =>  $this->product_code_type,
                'express_post_2000'         =>  $this->express_post_2000,
                'postage2000'               =>  $this->postage2000,
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
    
    /**
     * Get Estimated ShippingCost
     * get postage_api_url
     * get the html string from postage_api_url
     * string replace
     * return float
     * @param int $product_id
     * @param string $zip
     * @param int $qty
     * @return float
     */
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
    
    /**
     * Update Estimated ShippingCost
     * get postage_api_url
     * get the html string from postage_api_url
     * string replace
     * Update Estimated ShippingCost
     * @param string $postage_api_url
     * @param int $product_id
     * @return int
     */
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
    
    /**
     * Get Product Info by SKU
     * get product table params
     * get product sku
     * select product info by sku
     * return result array
     * @param string $sku
     * @return array
     */
    function getProductInfo($sku)
    {
        $result =   array();
        $result['product_name']    = "";
        $result['imageURL0']    = "";
        
        $params_model = new Databases_Tables_Params();
        $data_source = $params_model->GetVal("product_info_table");
        if($data_source && $sku) // 1 or 2
        {
            $product_select = $this->db->select();
            $product_select->from("product_info_".$data_source, array("product_id","product_name","supplier_sku", 'supplier_price', "street_price", "wholesale_cost", "estimated_shipping_cost", "estimated_handling_fee", "quantity_available","sc_class","imageURL0"));
            $product_select->where("supplier_sku = ?", $sku);
            $product = $this->db->fetchRow($product_select);
            if($product['supplier_sku'])
            {
                $result['product_name']     =   $product['product_name'];
                $result['imageURL0']        =   $product['imageURL0'];
                $result['supplier_price']   =   $product['supplier_price'];
                $result['product_id']       =   $product['product_id'];
                $result['supplier_sku']     =   $product['supplier_sku'];
            }
        }
        
        return $result;
    }
    
    /**
     * Update Quantity Available
     * get product table params
     * get product sku
     * update product Quantity by sku
     * @param string $sku
     * @param int $quantity_available
     */
    function updateQuantityAvailable($sku,$quantity_available)
    {
        $params_model = new Databases_Tables_Params();
        $data_source = $params_model->GetVal("product_info_table");
        if($data_source && $sku) // 1 or 2
        {
            $product_select = $this->db->select();
            $product_select->from("product_info_".$data_source);
            $product_select->where("supplier_sku = ?", $sku);
            $row = $this->db->fetchRow($product_select);
            $row['quantity_available'] -= $quantity_available;
            $where = $this->db->quoteInto('supplier_sku = ?', $sku);
            $set    =   array(
                'quantity_available'    =>  $row['quantity_available'],
            );
            $this->db->update("product_info_".$data_source, $set, $where);
        }
    }
    
    /**
     * Get Product Category Info IN Recursion
     * @param int $category_id
     * @return array
     */
    function getProductCategoryInfo($category_id){
        $categorys = array();
        if($category_id)
        {
            $parent_category_list   =   $this->all_category_data[$category_id];
            if($parent_category_list && $parent_category_list['parent_id'] !== '1')
            {
                $categorys  =   $this->getProductCategoryInfo($parent_category_list['parent_id']);
            }
            $categorys[]   =   $parent_category_list['category_name'];
        }
        return $categorys;
    }
    
    function getDDProductCategoryInfo($category_id){
        $categorys = array();
        if($category_id)
        {
            $parent_category_list   =   $this->dd_all_category_data[$category_id];
            if($parent_category_list && $parent_category_list['parent_id'] !== '0')
            {
                $categorys  =   $this->getDDProductCategoryInfo($parent_category_list['parent_id']);
            }
            $categorys[]   =   $parent_category_list['category_name'];
        }
        return $categorys;
    }
    /**
     * Get the name of image
     * @param string $url image path
     * @return string image name
     */
    public function getFileName($url){
        if(is_string($url) &&  strlen($url)>0){
            return substr($url,strrpos($url, '/')+1);
        }
        return '';
    }
    /**
     * Get New Product Image data between product_info_table
     */
    function getNewProductInfo(){
        $result =   array();
        $params_model = new Databases_Tables_Params();
        $data_source = $params_model->GetVal("product_info_table");
        
        if($data_source == '1'){
            $old_data_source    =   '2';
        }else{
            $old_data_source    =   '1';
        }
        $source_table = "product_info_".$data_source;
        $old_source_table = "product_info_".$old_data_source;
        if($this->dd_new_product_ids_array){
            $new_product_ids    = implode(',', $this->dd_new_product_ids_array);
            if($new_product_ids){
                $sql    =   'select * from '. $source_table. ' where product_id in ('.$new_product_ids.')';
                $data   =   $this->db->query($sql);
                if($data){
                    $data_all   =   $data->fetchAll();
                    if ($data_all){
                        foreach ($data_all as $key => $data){
                            $result['product_image'][$key]['imageURL0'] =   $data['imageURL0'];
                            $result['product_image'][$key]['imageURL1'] =   $data['imageURL1'];
                            $result['product_image'][$key]['imageURL2'] =   $data['imageURL2'];
                            $result['product_image'][$key]['imageURL3'] =   $data['imageURL3'];
                            $result['product_image'][$key]['imageURL4'] =   $data['imageURL4'];
                            $result['product_image'][$key]['imageURL5'] =   $data['imageURL5'];
                            $result['product_description'][$data['supplier_sku']] =   $data['supplier_sku'].'-TP.txt';
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Get Count of source product table
     */
    function getCountProduct(){
        $result =   FALSE;
        $params_model   =   new Databases_Tables_Params();
        $data_source    =   $params_model->GetVal("product_info_table");
        $select         =   $this->db->select();
        $select->from('product_info_'.$data_source,'count(*) as count');
        $data_count     =   $this->db->fetchRow($select);
        if($data_count){
            if($data_count['count'] > 3000){
                $result =   TRUE;
            }
        }
        return $result;
    }
    
    function getProductsLowPrice(){
        $result =   FALSE;
        $select =   $this->db->select();
        $select->from('dd_products as d', array('d.product_code', 'd.cost as dd_price','d.stock','d.rrp','d.cc_supplier_sku','d.product_title'));
        $select->joinLeft('product_info_2 as p', 'p.product_id = d.product_id', array('p.supplier_sku','p.wholesale_cost','p.street_price', 'p.product_name'));
        $result =   $this->db->fetchAll($select);
        return $result;
    }
    function GetSkuPricesInfo()
    {
        $products_result    =   array();
        //get markup
        $params_model = new Databases_Tables_Params();
        $data_source = $params_model->GetVal("product_info_table");
        if($data_source && $this->order_skus_array) // 1 or 2
        {
            $product_select = $this->db->select();
            $skus           = implode("','", $this->order_skus_array);
            $product_select->from("product_info_".$data_source, array("product_id","product_name","supplier_sku", "street_price","supplier_price", "wholesale_cost", "estimated_shipping_cost", "estimated_handling_fee", "quantity_available","sc_class", "shipping_courier", "length", "height", "depth", "weight"));
            $product_select->where("supplier_sku in ('". $skus."')");
            $products = $this->db->fetchAll($product_select);
            if($products){
                foreach ($products as $product_info){
                    $products_result[$product_info['supplier_sku']] =   $product_info;
                }
            }
        }
        return $products_result;
    }
}