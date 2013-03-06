<?php

class Algorithms_Core_Feed
{
    var $user_id_array; //array
    
    function Publish()
    {
        if(count($this->user_id_array))
        {
            $product_filter_model = new Databases_Joins_ProductFilter();
            
            foreach($this->user_id_array as $user_id)
            {
                $collect_feed_info = $this->CollectFeedInfo($user_id);
                
                $product_list = $product_filter_model->Push($collect_feed_info);
                
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
                    
                    //Create Title
                    foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                    {
                        $contents_tmp_array[] = $qualifier.$users_feed_definition['column_alias'].$qualifier;
                    }
                    
                    $contents .= implode($delimeter, $contents_tmp_array)."\n\r";
                    
                    //==== Make Title Finished ====//
                    
                    $feed_dictionary = new Databases_Tables_FeedDictionary();
                    $array_for_replacement = $feed_dictionary->ArrayForReplacement();
                    
                    foreach($product_list as $pl)
                    {
                        $contents_tmp_array = array();
                        
                        foreach($collect_feed_info['users_feed_definition'] as $users_feed_definition)
                        {
                            $contents_tmp_array[] = $qualifier.$this->StringReplacement($pl, $users_feed_definition['column_value'], $array_for_replacement).$qualifier;
                        }
                        
                        $contents .= implode($delimeter, $contents_tmp_array)."\n\r";
                    }
                    
                    $export_model = new Algorithms_Core_Export();
                    $plugin_model = new Algorithms_Extensions_Plugin();
                    $export_model->file_name = $plugin_model->GetFeedPath($collect_feed_info['users_feed']['feed_name'], $collect_feed_info['users_feed']['feed_extension'], 1);
                    $export_model->contents = $contents;
                    
                    //Create Feed
                    $result = $export_model->Push();
                }
            }
        }else{
            $result = "Error: Key parameter missed.";
        }
        
        return $result." ".date("Y-m-d H:i:s");
        
    }
    
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
    
    function StringReplacement($product_row, $feed_column_value, $array_for_replacement)
    {
        foreach($array_for_replacement as $key => $val)
        {
            $feed_column_value = str_replace($key, $product_row[$val], $feed_column_value);
        }
        
        return $feed_column_value;
    }
}