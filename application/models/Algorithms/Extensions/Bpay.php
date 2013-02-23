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
    
    var $csv_title = array(
        0 => "Payment date",
        1 => "Biller code",
        2 => "Customer reference number",
        3 => "Receivable type",
        4 => "Payment method",
        5 => "BPAY type",
        6 => "Transaction reference",
        7 => "Settlement date",
        8 => "Amount"
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
        if($check_csv_format)
        {
            $get_all_customer_ref = new Databases_Joins_GetUserInfo();
            $all_customer_ref = $get_all_customer_ref->MerchantRefArray();
            $logs_financial = new Databases_Tables_LogsFinancial();
            $trans_id_temp = array();
            
            foreach($check_csv_format as $array_key => $array_val)
            {
                if("Y" == $array_val['result'] && $array_key != 0)
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
                    }elseif($array_val['transaction_ref'] && in_array($array_val['transaction_ref'], $trans_id_temp))
                    {
                        $check_csv_format[$array_key]['result'] = "N";
                        $check_csv_format[$array_key]['reason'] = "Transaction Ref Duplicated";
                    }elseif($array_val['transaction_ref'] && $logs_financial->CheckCustomerRefExist($array_val['transaction_ref']))
                    {
                        $check_csv_format[$array_key]['result'] = "N";
                        $check_csv_format[$array_key]['reason'] = "Transaction Ref is existed";
                    }
                }
                
                $trans_id_temp[] = $array_val['transaction_ref'];
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
        
        //Check Title
        for($n=0;$n<9;$n++)
        {
            if(strtolower($this->csv_title[$n]) != strtolower($csv_array[0][$n]))
            {
                $title_invalid = 1;
            }
        }
        
        if($title_invalid)
        {
            $result[] = array(
                    "row" => $row,
                    "customer_ref" => "-",
                    "transaction_ref" => "-",
                    "amount" => "-",
                    "result" => "N",
                    "reason" => "Title Invalid"
                );
        }else{
            $result[] = array(
                    "row" => $row,
                    "customer_ref" => "-",
                    "transaction_ref" => "Title Check",
                    "amount" => "-",
                    "result" => "Y",
                    "reason" => "Title Check"
                );
        }
        
        $row += 1;
        unset($csv_array[0]); //remove title from array
        
        foreach ($csv_array as $csv_val)
        {
            if(9 != count($csv_val))
            {
                $result[] = array(
                    "row" => $row,
                    "customer_ref" => $csv_val[2],
                    "transaction_ref" => $csv_val[6],
                    "amount" => $csv_val[8],
                    "result" => "N",
                    "reason" => "Format Invalid"
                );
            }else{
                $result[] = array(
                    "row" => $row,
                    "customer_ref" => $csv_val[2],
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