<?php

class Databases_Tables_UsersFeedDefinition extends Zend_Db_Table
{
    protected $_name = 'users_feed_definition';
    
    function ElementsForList($feed_dictionary, $users_feed_id)
    {
        if($users_feed_id)
        {
            $rows = $this->fetchAll("users_feed_id='".$users_feed_id."'", "sequence ASC");
            $data = $rows->toArray();
        }else{
            $data = array();
        }
        
        $result_2 = array();
        $n = 0;
        $format_dictionary_array = $this->FormatDictionaryArray($feed_dictionary);
        
        if(!empty($data))
        {
            foreach($data as $d)
            {
                $result_2[] = array(
                    "feed_dictionary_id" => $d['feed_dictionary_id'],
                    "checked" => 1,
                    "column_alias" => $d['column_alias'],
                    "column_value" => $d['column_value'],
                    "column_value_adjustment" => $d['column_value_adjustment']
                );
                
                unset($format_dictionary_array[$d['feed_dictionary_id']]);
                $n += 1;
            }
        }
        
        if(!empty($format_dictionary_array))
        {
            foreach($format_dictionary_array as $f_key => $f_val)
            {
                $result_2[] = array(
                    "feed_dictionary_id" => $f_key,
                    "checked" => 0,
                    "column_alias" => $f_val['column_name'],
                    "column_value" => $this->FormatColumnValue($f_val['column_param']),
                    "column_value_adjustment" => NULL
                );
                
                $n += 1;
            }
        }
        
        $result = array();
        
        $result[1] = $n;
        $result[2] = $result_2;
        
        return $result;
    }
    
    function FormatDictionaryArray($feed_dictionary)
    {
        $result = array();
        
        if(!empty($feed_dictionary))
        {
            foreach($feed_dictionary as $f)
            {
                $result[$f['feed_dictionary_id']] = array(
                    "column_name" => $f['column_name'],
                    "column_param" => $f['column_param']
                );
            }
        }
        
        return $result;
    }
    
    function FormatColumnValue($val)
    {
        $header = "<{";
        $ending = "}>";
        
        return $header.$val.$ending;
    }
}