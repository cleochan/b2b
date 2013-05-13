<?php
   $params =   $this->_request->getParams();
         // read the post from PayPal system and add 'cmd'   
        $req = 'cmd=_notify-validate';   
        
        foreach ($params as $key => $value) {   
        $value = urlencode(stripslashes($value));   
        $req .= "&$key=$value";   
        }   
        // post back to PayPal system to validate   
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";   
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";   
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";   
           
        $fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30); // 沙盒用   
        //$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30); // 正式用   

        // assign posted variables to local variables   
        $item_name = $params['item_name'];   
        $item_number = $params['item_number'];   
        $payment_status = $params['payment_status'];   
        $payment_amount = $params['mc_gross'];   
        $payment_currency = $params['mc_gross'];   
        $txn_id = $params['txn_id'];   
        $receiver_email = $params['receiver_email'];   
        $payer_email = $params['payer_email'];   
        $mc_gross = $params['mc_gross ']; // 付款金额   
        $custom = $params['custom ']; // 得到订单号  
		 
	$logs_contents	=	'item_name:'.$item_name."\r\n".' item_number:'.$item_number."\r\n".'  payment_status:'.$payment_status."\r\n".'  payment_amount:'.$payment_amount."\r\n".'  txn_id:'.$txn_id."\r\n".'  receiver_email:'.$receiver_email."\r\n".'  payer_email'.$payer_email."\r\n".'   custom:'.$custom;
        print_R($logs_contents);
        $f  =   @fopen(date('YmdHis').".txt", "w+");
        @fwrite($f, $logs_contents);
        @fclose($f);
?>
