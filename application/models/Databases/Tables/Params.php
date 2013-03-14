<?php

class Databases_Tables_Params extends Zend_Db_Table
{
    protected $_name = 'params';
    
    function GetVal($key)
    {
        $result = $this->fetchRow("ckey='".$key."'");
        return $result->cval;
    }
    
    function UpdateVal($key, $val)
    {
        $result = $this->fetchRow("ckey='".$key."'");
        $result->cval = $val;
        if($result->save())
        {
            $result2 = TRUE;
        }else{
            $result2 = FALSE;
        }
        return $result2;
    }
    
    function SwitchProductTableTo($new_table_num) // Make sure the target table has been refreshed succesfully! Important!
    {
        //update table name
        $row = $this->fetchRow("ckey='product_info_table'");
        $row->cval = $new_table_num;
        $row->save();
        
        //update refreshing time
        $row2 = $this->fetchRow("ckey='product_info_table_refresh_time'");
        $row2->cval = date("Y-m-d H:i:s");
        $row2->save();
        
        //erase the page amount
        $row3 = $this->fetchRow("ckey='product_request_page_amount'");
        $row3->cval = NULL;
        $row3->save();
        
        //erase the current page
        $row4 = $this->fetchRow("ckey='product_request_current_page'");
        $row4->cval = NULL;
        $row4->save();
        
        return TRUE;
    }
    
    function GetSecondaryProductTableName()
    {
        $row = $this->fetchRow("ckey='product_info_table'");
        
        $result = array();
        
        if(1 == $row['cval'])
        {
            $result['table_num'] = 2;
            $result['table_name'] = "product_info_2";
        }elseif(2 == $row['cval'])
        {
            $result['table_num'] = 1;
            $result['table_name'] = "product_info_1";
        }
        
        return $result;
    }
}