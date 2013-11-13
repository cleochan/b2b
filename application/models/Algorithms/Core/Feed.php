<?php

class Algorithms_Core_Feed
{
    var $user_id_array; //array
    var $txt_file_array;//array
    /**
     * start loop
     * collect merchant feed info
     * get product list
     * string replace
     * export csv file
     * finish loop
     */
    function Publish()
    {
        if(count($this->user_id_array))
        {
            $product_filter_model = new Databases_Joins_ProductFilter();
            $dd_products_model      =   new Databases_Tables_DdProducts();
            $new_product_ids_array  =   array();
            foreach($this->user_id_array as $user_id)
            {
                $collect_feed_info = $this->CollectFeedInfo($user_id);
                $product_list = $product_filter_model->Push($collect_feed_info, $user_id);
                
                //set all the DD products' stock 0
                if($user_id ==  8 && $product_list){
                    $dd_products_model->setStockZero();
                }
                $all_product_array  =   array();
                if(!empty($collect_feed_info['users_feed_definition']) && !empty($product_list))
                {
                    //Initial String
                    $contents = "";
                    $contents_tmp_array = array();
                    
                    switch($collect_feed_info['users_feed']['feed_delimeter'])
                    {
                        case 1:
                            $delimeter = "	"; //Tab
                            break;
                        case 2:
                            $delimeter = ";";
                            break;
                        case 3:
                            $delimeter = ",";
                            break;
                        case 4:
                            $delimeter = " "; //Space
                            break;
                        case 5:
                            $delimeter = $collect_feed_info['users_feed']['special_delimeter']; //Custom
                            break;
                    }

                     switch($collect_feed_info['users_feed']['feed_qualifier'])
                    {
                        case 1:
                            $qualifier = ''; //None
                            break;
                        case 2:
                            $qualifier = '"'; // "
                            break;
                        case 3:
                            $qualifier = "'"; // '
                            break;
                    }
                    
                    $feed_dictionary = new Databases_Tables_FeedDictionary();
                    $array_for_replacement = $feed_dictionary->ArrayForReplacement();
                    
                    if(in_array($collect_feed_info['users_feed']['feed_extension'], array(1,2))) //csv or txt
                    {
                        //Make Title
                        foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                        {
                            $contents_tmp_array[] = $qualifier.$users_feed_definition['column_alias'].$qualifier;
                        }

                        $contents .= implode($delimeter, $contents_tmp_array)."\r\n";
                        //==== Make Title Finished ====//
                        
                        foreach($product_list as $pl)
                        {
                            if($user_id == 8){
                                $all_product_array['product_image'][]['imageURL0']    =   $pl['imageURL0'];
                                $all_product_array['product_image'][]['imageURL1']    =   $pl['imageURL1'];
                                $all_product_array['product_image'][]['imageURL2']    =   $pl['imageURL2'];
                                $all_product_array['product_image'][]['imageURL3']    =   $pl['imageURL3'];
                                $all_product_array['product_image'][]['imageURL4']    =   $pl['imageURL4'];
                                $all_product_array['product_image'][]['imageURL5']    =   $pl['imageURL5'];
                                $all_product_array['product_description'][$pl['supplier_sku']] =   $pl['supplier_sku'].'-TP.txt';
                            }
                            $contents_tmp_array = array();
                            
                            foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                            {
                                $string_replacement_result = $this->StringReplacement($pl, $users_feed_definition['column_value'], $array_for_replacement, $users_feed_definition['column_value_adjustment']);
                                
                                if('"' === $qualifier)
                                {
                                    $string_replacement_result = str_replace('"', '""', $string_replacement_result);
                                }elseif("'" === $qualifier)
                                {
                                    $string_replacement_result = str_replace("'", '"', $string_replacement_result);
                                }
                                
                                $contents_tmp_array[] = $qualifier.$string_replacement_result.$qualifier;
                            }
                            if($user_id == 8){
                                $dd_products_model->product_id      =   $pl['product_id'];
                                $dd_products_model->product_code    =   trim($contents_tmp_array[0],'"');
                                $dd_products_model->product_title   =   trim($contents_tmp_array[1],'"');
                                $dd_products_model->brand           =   trim($contents_tmp_array[2],'"');
                                $dd_products_model->category_1      =   trim($contents_tmp_array[3],'"');
                                $dd_products_model->category_2      =   trim($contents_tmp_array[4],'"');
                                $dd_products_model->description     =   trim($contents_tmp_array[5],'"');
                                $dd_products_model->rrp             =   trim($contents_tmp_array[6],'"');
                                $dd_products_model->sell            =   trim($contents_tmp_array[7],'"');
                                $dd_products_model->freight         =   trim($contents_tmp_array[8],'"');
                                $dd_products_model->cost            =   trim($contents_tmp_array[9],'"');
                                $dd_products_model->weight          =   trim($contents_tmp_array[10],'"');
                                $dd_products_model->available       =   trim($contents_tmp_array[11],'"');
                                $dd_products_model->stock           =   trim($contents_tmp_array[12],'"');
                                $dd_products_model->image_1         =   trim($contents_tmp_array[13],'"');
                                $dd_products_model->image_2         =   trim($contents_tmp_array[14],'"');
                                $dd_products_model->image_3         =   trim($contents_tmp_array[15],'"');
                                $dd_products_model->image_4         =   trim($contents_tmp_array[16],'"');
                                $dd_products_model->image_5         =   trim($contents_tmp_array[17],'"');
                                $dd_products_model->image_6         =   trim($contents_tmp_array[18],'"');
                                $dd_products_model->length          =   trim($contents_tmp_array[19],'"');
                                $dd_products_model->width           =   trim($contents_tmp_array[20],'"');
                                $dd_products_model->height          =   trim($contents_tmp_array[21],'"');
                                $dd_products_model->despatch_pcode  =   trim($contents_tmp_array[22],'"');
                                $dd_products_model->courier         =   trim($contents_tmp_array[23],'"');
                                $dd_products_model->cc_supplier_sku =   $pl['supplier_sku'];
                                $dd_products_model->cc_price        =   $pl['supplier_price'];
                                $product_id                         =   $dd_products_model->updateDdProduct();
                                if($product_id){
                                    $new_product_ids_array[]        =   $product_id;
                                }          
                            }
                            $contents .= implode($delimeter, $contents_tmp_array)."\r\n";
                        }
                        
                    }elseif(3 == $collect_feed_info['users_feed']['feed_extension']) //xml
                    {
                        $api_model = new Algorithms_Core_Api();
                        $xml_array = array();
                            
                        $key_header = 'Product';
                        $key_value = 1;
                        
                         foreach($product_list as $pl)
                        {
                             if($user_id == 8){
                                $all_product_array['product_image'][]['imageURL0']    =   $pl['imageURL0'];
                                $all_product_array['product_image'][]['imageURL1']    =   $pl['imageURL1'];
                                $all_product_array['product_image'][]['imageURL2']    =   $pl['imageURL2'];
                                $all_product_array['product_image'][]['imageURL3']    =   $pl['imageURL3'];
                                $all_product_array['product_image'][]['imageURL4']    =   $pl['imageURL4'];
                                $all_product_array['product_image'][]['imageURL5']    =   $pl['imageURL5'];
                                $all_product_array['product_description'][$pl['supplier_sku']] =   $pl['supplier_sku'].'-TP.txt';
                             }
                            foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                            {
                                $contents_tmp_array[($api_model->XmlKeyFilter($users_feed_definition['column_alias']))] = $api_model->XmlValueFilter($this->StringReplacement($pl, $users_feed_definition['column_value'], $array_for_replacement, $users_feed_definition['column_value_adjustment']));
                            }
                            
                            $xml_array[$key_header.$key_value] = $contents_tmp_array;
                            $key_value += 1;
                        }
                        
                        $contents = $api_model->Array2Xml($xml_array);
                    }
                    
                    if($user_id == 8){
                        $all_dd_products    =   $dd_products_model->getAllDdProducts();
                        if($all_dd_products){
                            $contents   =   '';
                            //Make Title
                            foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                            {
                                $contents_title_array[] = $qualifier.$users_feed_definition['column_alias'].$qualifier;
                            }
                            $contents .= implode($delimeter, $contents_title_array)."\r\n";
                            foreach ($all_dd_products as $dd_product){
                                $contents_tmp_array = array();
                                foreach ($dd_product as $key => $value){
                                    if('"' === $qualifier)
                                    {
                                        $value = str_replace('"', '""', $value);
                                    }elseif("'" === $qualifier)
                                    {
                                        $value = str_replace("'", '"', $value);
                                    }

                                    $contents_tmp_array[] = $qualifier.$value.$qualifier;
                                }
                                $contents .= implode($delimeter, $contents_tmp_array)."\r\n";
                            }
                        }
                    }
                    
                    $export_model = new Algorithms_Core_Export();
                    $plugin_model = new Algorithms_Extensions_Plugin();
                    $export_model->file_name = $plugin_model->GetFeedPath($collect_feed_info['users_feed']['feed_name'], $collect_feed_info['users_feed']['feed_extension'], 1);
                    $export_model->contents = $contents;
                    //Create Feed
                    $result = $export_model->Push();
                    if($user_id == 8 ){
                        if($collect_feed_info['users_feed']['feed_product_type'] == '2'){
                            if($new_product_ids_array){
                                $product_filter_model->dd_new_product_ids_array =   $new_product_ids_array;
                                $new_product_array    =   $product_filter_model->getNewProductInfo();
                                if($new_product_array){
                                    $product_array  =   $new_product_array;
                                }
                            }
                        }
                        //elseif($collect_feed_info['users_feed']['feed_product_type'] == '1'){
                        //    $product_array      =   $all_product_array;
                        //}
                        if($product_array){
                            $this->uploadFtpFile($product_array['product_image'], 'image');
                            $this->uploadFtpFile($product_array['product_description'], 'txt');
                        }
                        $this->uploadFtpFile(array($export_model->file_name), 'csv');
                    }
                }
            }
        }else{
            $result = "Error: Key parameter missed.";
        }
        
        return $result." ".date("Y-m-d H:i:s");
        
    }
    
    /**
     * collect merchant feed info
     * get merchant feed info
     * get merchant feed definition info
     * return merchant feed definition info
     * @param int $user_id
     * @return array
     */
    function CollectFeedInfo($user_id)
    {
        $users_feed_model = new Databases_Tables_UsersFeed();
        $users_feed = $users_feed_model->GetFeedInfo($user_id);
        
        if($users_feed['users_feed_id'])
        {
            $product_filter_model = new Databases_Joins_ProductFilter();
            $users_feed_definition = $product_filter_model->GetFeedDefinitionInfo($users_feed['users_feed_id']);
        }
        
        return array("users_feed" => $users_feed,
                            "users_feed_definition" => $users_feed_definition
                            );
    }
    
    /**
     * String Replacement
     * start loop 
     * get feed_column_value
     * finish loop
     * get value adjustment array
     * start loop
     * substr conditions
     * finish loop
     * return feed column value
     * 
     * @param array $product_row
     * @param string $feed_column_value
     * @param array $array_for_replacement
     * @param string $column_value_adjustment
     * @return string
     */
    function StringReplacement($product_row, $feed_column_value, $array_for_replacement, $column_value_adjustment)
    {
        // Format with the value in database
        
        foreach($array_for_replacement as $key => $val)
        {
            $feed_column_value = str_replace($key, $product_row[$val], $feed_column_value);
        }
        
        // Format if there is value adjustment
        $value_adjustment_array = $this->ValueAdjustmentReader($column_value_adjustment);
        $product_filter_model   =   new Databases_Joins_ProductFilter();
        //print_r($value_adjustment_array);
        if(!empty($value_adjustment_array))
        {
            foreach($value_adjustment_array as $adj)
            {
                $cond = explode("|", $adj);
                if(">" === substr($cond[0], 0, 1))
                {
                    if($feed_column_value > substr($cond[0], 1))
                    {
                        $feed_column_value = $cond[1];
                    }
                }elseif("<" === substr($cond[0], 0, 1))
                {
                    if($feed_column_value < substr($cond[0], 1))
                    {
                        $feed_column_value = $cond[1];
                    }
                }elseif("VAL" == $cond[0] && "VAL" == substr($cond[1], 0, 3))
                {
                    if("+" === substr($cond[1], 3, 1))
                    {
                        $feed_column_value += substr($cond[1], 4);
                    }elseif("-" === substr($cond[1], 3, 1))
                    {
                        $feed_column_value -= substr($cond[1], 4);
                    }elseif("*" === substr($cond[1], 3, 1))
                    {
                        $feed_column_value = $feed_column_value * substr($cond[1], 4);
                    }elseif("/" === substr($cond[1], 3, 1) && 0 !== substr($cond[1], 4))
                    {
                        $feed_column_value = $feed_column_value / substr($cond[1], 4);
                    }
                }elseif("ROUND" == $cond[0])
                {
                    $feed_column_value = round($feed_column_value);
                }elseif ("FILE" == $cond[0])
                {
                    $feed_column_value  =   $this->filterHtmlTags($feed_column_value,$product_row['supplier_sku'].'-TP');
                }elseif("REL" == $cond[0])
                {
                    $feed_column_value  =   implode(' | ',array_filter(explode(' | ', $feed_column_value)));
                }elseif('FILENAME' == $cond[0])
                {
                    $feed_column_value  =   $product_filter_model->getFileName($feed_column_value);
                }elseif('SUBSTR' == $cond[0])
                {
                    if(strlen($feed_column_value) > $cond[1])
                    {
                        $feed_column_value  =   substr($feed_column_value, 0, $cond[1]);
                    }
                }elseif ('EXPRESS'== $cond[0]) {
                    $express_info_array = explode('|', $feed_column_value);
                    if($express_info_array[0]>=105 || $express_info_array[1]>=105 || $express_info_array[2]>=105 || $express_info_array[3]>=32){
                        $feed_column_value  =   3;
                    }  else {
                        $feed_column_value  =   1;
                    }
                }else{
                    if($feed_column_value === $cond[0])
                    {
                        $feed_column_value = $cond[1];
                    }
                }
            }
        }
        
        $feed_column_value = str_replace("\n", "", $feed_column_value);
        $feed_column_value = str_replace("\r", "", $feed_column_value);
        $feed_column_value = str_replace("\r\n", "", $feed_column_value);

        return $feed_column_value;
    }
    
    /**
     * value adjustment reader
     * start loop
     * get start string and stop string
     * substr
     * finish loop
     * return result_array
     * @param string $column_value_adjustment
     * @return array
     */
    function ValueAdjustmentReader($column_value_adjustment)
    {
        $string = trim($column_value_adjustment);
        
        $result = array();
        
        if($string)
        {
            $length = strlen($string);
            
            while($length)
            {
                $from = strpos($string, "[");
                $to = strpos($string, "]");

                if(FALSE !== $from && FALSE !== $to && $from < $to)
                {
                    $result[] = substr($string, $from+1, $to-$from-1);

                    $string = substr($string, $to+1);

                    $length = strlen($string);
                }else{
                    $length = 0; // Exit
                }
            }
        }
        
        return $result;
    }
    
    /**
     * @param string $html
     * @return string
     */
    function filterHtmlTags($html,$file_name,$user_id){
        $system_params_model    =   new Databases_Tables_Params();
        $logs_path                  =   $system_params_model->GetVal('merchant_feed_txt_path');
        $encode =   'UTF-8';
        preg_match("/WARRANTY:([\s\S]*)/i",$html,$warranties);
        //$html   =   preg_replace("/WARRANTY:([\s\S]*)/i", "", $html);
        //$warranty       =   $warranties[0];
        preg_match("/SPECIFICATIONS:<table.*?>[\s\S]*?<\/table>/i",$html,$matches);
        $html   =   preg_replace("/SPECIFICATIONS:([\s\S]*)/i", "", $html);
        $specifications =   $matches[0];
        $specifications =   preg_replace("/<tr>/i", "\t *", $specifications);
        $specifications =   preg_replace("/<\/?tr>/i", "\r\n", $specifications);
        $specifications =   preg_replace("/<br \s*\/?\/>/i", "\r\n\t\t", $specifications);
        $specifications =   preg_replace("/<\/?li>/i", "\r\n\t\t", $specifications); 
        $specifications =   preg_replace("/<\/?p>/i", "\r\n\t\t", $specifications);  
        //$html   .=  $specifications.$warranty;
        $html   .=  $specifications;
        $html   =   preg_replace("/<br \s*\/?\/>/i", "\r\n", $html);  
        $html   =   preg_replace("/<\/?p>/i", "\r\n", $html);  
        $html   =   preg_replace("/<\/?div>/i", "\r\n", $html);
        $html   =   preg_replace("/<\/?td>/i", " ", $html); 
        $html   =   preg_replace("/<tr>/i", "\t *", $html);
        $html   =   preg_replace("/<\/?tr>/i", "\r\n", $html);
        $html   =   preg_replace("/<br \s*\/?\/>/i", "\r\n\t\t\t", $html);
        $html   =   preg_replace("/FEATURES:/", "\r\nFEATURES:\r\n\r\n", $html);
        $html   =   preg_replace("/SPECIFICATIONS:/", "\r\nSPECIFICATIONS:\r\n\r\n", $html);
        //$html   =   preg_replace("/WARRANTY:/", "\r\nWARRANTY:\r\n", $html);
        $html   =   preg_replace("/<\/?blockquote>/i", "\n", $html);
        $html   =   preg_replace("/<li>/i", "\t * ", $html);
        $html   =   preg_replace("/<\/?li>/i", "\r\n", $html);
        $html   =   preg_replace("/\&nbsp\;/i", " ", $html);  
        $html   =   preg_replace("/\&nbsp/i", " ", $html);  
        $html   =   preg_replace("/\&amp\;/i", "&", $html);  
        $html   =   preg_replace("/\&amp/i", "&", $html);    
        $html   =   preg_replace("/\&lt\;/i", "<", $html);  
        $html   =   preg_replace("/\&lt/i", "<", $html);  
        $html   =   preg_replace("/\&ldquo\;/i", '"', $html);  
        $html   =   preg_replace("/\&ldquo/i", '"', $html);  
        $html   =   preg_replace("/\&lsquo\;/i", "'", $html);  
        $html   =   preg_replace("/\&lsquo/i", "'", $html);  
        $html   =   preg_replace("/\&rsquo\;/i", "'", $html);  
        $html   =   preg_replace("/\&rsquo/i", "'", $html);  
        $html   =   preg_replace("/\&gt\;/i", ">", $html);   
        $html   =   preg_replace("/\&gt/i", ">", $html);   
        $html   =   preg_replace("/\&rdquo\;/i", '"', $html);   
        $html   =   preg_replace("/\&rdquo/i", '"', $html);
        $html   =   preg_replace("/\*\s\s\s\s/i", "*", $html);
        $html   =   strip_tags($html);  
        $html   =   html_entity_decode($html, ENT_QUOTES, $encode);  
        $html   =   preg_replace("/\&\#.*?\;/i", "", $html);
        preg_match("/([\s\S]*)Warranty/",$html,$matches);
        $file_name  =   $file_name.".txt";
        $f          =   fopen($logs_path."/".$file_name, "w+");
        if($matches[0]){
            @fwrite($f,$matches[0]);
        }else{
            @fwrite($f,$html);
        }
        @fclose($f);
        return $file_name;
        
    }
    
    /**
     * Uplaod images to merchant's FTP server
     * @param array $images_array
     */
    function uploadFtpFile($file_array = array(), $type = null){
        if($file_array){
            $product_filter_model   =   new Databases_Joins_ProductFilter();
            $merchant_ftp_array     =   array(
                'ftp_host'      =>  'interface.dealsdirect.com.au',
                'ftp_port'      =>  '21',
                'ftp_user'      =>  'tp_crazysales',
                'ftp_pass'      =>  '3Ws5maLm',
                'image_path'    =>  'outgoing/inventory/images/',
                'txt_path'      =>  'outgoing/inventory/descriptions/',
                'csv_path'      =>  'outgoing/inventory/datafeed/processing/',
                'shipping_path' =>  'outgoing/shipping/processing/',
            );
            $ftp    =   new Algorithms_Core_Ftp($merchant_ftp_array['ftp_host'], $merchant_ftp_array['ftp_port'], $merchant_ftp_array['ftp_user'], $merchant_ftp_array['ftp_pass']);
            switch ($type){
                case 'image':
                    foreach ($file_array as $files){
                        foreach ($files as $file){
                            if($file){
                                $file_name         =   $product_filter_model->getFileName($file);
                                $ftp_server_path    =   $merchant_ftp_array['image_path'].$file_name;
                                $ftp->up_file($ftp_server_path, $file);
                            }
                        }
                    }
                    $ftp->close();
                    break;
                case 'txt':
                    foreach ($file_array as $file){
                        if($file){
                            $ftp_server_path    =   $merchant_ftp_array['txt_path'].$file;
                            $ftp->up_file($ftp_server_path, 'feed_txt/'.$file);
                        }
                    }
                    $ftp->close();
                    break;
                case 'csv':
                    foreach ($file_array as $file){
                        if($file){
                            $ftp_server_path    =   $merchant_ftp_array['csv_path'].'crazysales_datafeed.csv';
                            $log    =   $ftp->up_file($ftp_server_path, 'feeds/'.$file);
                        }
                    }
                    $ftp->close();
                    break;
                case 'shipping':
                    foreach ($file_array as $file){
                    echo $file;
                        if($file){
                            $ftp_server_path    =   $merchant_ftp_array['shipping_path'].$file;
                            $log    =   $ftp->up_file($ftp_server_path, 'DD_orders_new/'.$file);
                        }
                    }
                    $ftp->close();
                    break;
            }
            
        }
    }
}
