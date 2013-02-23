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
    
    function CheckCSV($csv_array)
    {
        $check_csv_format = $this->CheckCsvFormat($csv_array);
        Algorithms_Extensions_Plugin::FormatArray($check_csv_format);die;
        if($check_csv_format)
        {
            $get_all_customer_ref = new Databases_Joins_GetUserInfo();
            $all_customer_ref = $get_all_customer_ref->MerchantRefArray();
            $logs_financial = new Databases_Tables_LogsFinancial();
            
            foreach($check_csv_format as $array_key => $array_val)
            {
                if("Y" == $array_val['result'])
                {
                    //Step 1: check customer ref
                    if(!in_array($array_val['customer_ref'], $all_customer_ref))
                    {
                        $check_csv_format[$array_key]['result'] = "N";
                        $check_csv_format[$array_key]['reason'] = "Invalid Customer Ref";
                    }elseif(0 >= $array_val['amount']) //Step 2: check amount
                    {
                        $check_csv_format[$array_key]['result'] = "N";
                        $check_csv_format[$array_key]['reason'] = "Invalid Amount";
                    }elseif($logs_financial->CheckCustomerRefExist($array_val['transaction_ref']))
                    {
                        $check_csv_format[$array_key]['result'] = "N";
                        $check_csv_format[$array_key]['reason'] = "Transaction Ref is existed";
                    }
                }
            }
        }else{
            $check_csv_format = array();
        }
        
        return $check_csv_format;
    }
    
    function CheckCsvFormat($csv_array)
    {
        $result = array();
        $row = 1;
        Algorithms_Extensions_Plugin::FormatArray($csv_array);die;
        foreach ($csv_array as $csv_val)
        {
            if(8 != count($csv_val))
            {
                $result[] = array(
                    "row" => $row,
                    "customer_ref" => $csv_val[1],
                    "transaction_ref" => $csv_val[6],
                    "amount" => $csv_val[8],
                    "result" => "N",
                    "reason" => "Format Invalid"
                );
            }else{
                $result[] = array(
                    "row" => $row,
                    "customer_ref" => $csv_val[1],
                    "transaction_ref" => $csv_val[6],
                    "amount" => $csv_val[8],
                    "result" => "Y",
                    "reason" => "Passed"
                );
            }
            
            $row += 1;
        }
        
        return $result;
    }
}