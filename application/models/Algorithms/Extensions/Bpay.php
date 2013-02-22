<?php

class Algorithms_Extensions_Bpay
{
    var $biller_code = 696732;
    var $multiplier = array(
        0 => 2,
        1 => 1,
        2 => 2,
        3 => 1,
        4 => 2,
        5 => 1,
        6 => 2,
        7 => 1,
        8 => 2,
        9 => 1,
        10 => 2,
        11 => 1,
        12 => 2,
        13 => 1,
        14 => 2,
        15 => 1,
        16 => 2,
        17 => 1,
        18 => 2
    );
    
    function GetBillerCode()
    {
        return $this->biller_code;
    }
    
    function RefGenerator($original_ref)
    {
        $sep = array();
        
        for($n=0;$n<19;$n++)
        {
            $sep[$n] = substr($original_ref, $n, 1);
        }
        
        $total = 0;
        
        foreach($sep as $sep_key => $sep_val)
        {
            $sub = $this->multiplier[$sep_key] * $sep_val;
            
            if($sub > 9)
            {
                $sub_1 = substr($sub, 0, 1);
                $sub_2 = substr($sub, 1, 1);
                $sub_single = $sub_1 + $sub_2;
            }else{
                $sub_single = $sub;
            }
            
            $total += $sub_single;
        }
        
        $last_number = 10 - ($total % 10);
        
        $result = $original_ref . $last_number;
        
        return $result;
    }
}