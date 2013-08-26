<?php
/**
 * @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_Order {
    
   /**
    * find the string in '[]'
    * get params
    * strat loop
    * substr
    * finish loop
    * return string
    * 
    * @param string $column_value_adjustment
    * @return string
    */ 
    function ValueAdjustmentReader($column_value_adjustment)
    {
        $string =   trim($column_value_adjustment);
        $message_main_order_id =   '';
        if($string)
        {
            $length = strlen($string);

            while($length)
            {
                $from = strpos($string, "[");
                $to = strpos($string, "]");

                if(FALSE !== $from && FALSE !== $to && $from < $to)
                {
                    $message_main_order_id = substr($string, $from+1, $to-$from-1);

                    $string = substr($string, $to+1);

                    $length = strlen($string);
                }else{
                    $length = 0; // Exit
                }
            }
        }
        return $message_main_order_id;
    }
    
}

?>
