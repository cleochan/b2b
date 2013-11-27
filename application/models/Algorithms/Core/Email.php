<?php
class Algorithms_Core_Email {
    
    var $smtp;
    var $email;
    var $user_email_address;
    var $admin_email_address;
    var $email_array;
    var $merchant_company;
    function __construct() {
        $smtp_info  =   array(
            'auth'      =>  'login',
            'username'  =>  'TimWu@crazysales.com.au',
            'password'  =>  'wuhaijieyong1987',
            'ssl'       =>  'ssl'
        );
        $this->smtp     =   new Zend_Mail_Transport_Smtp('smtp.gmail.com',$smtp_info);
    }
    
    function sentEmail(){
        $params_model   =   new Databases_Tables_Params();
        $invoice_emails  =   $params_model->GetVal('invoice_email');
        $admin_emails    =   $params_model->GetVal('admin_email');
        $invoice_email_array    =   explode(',', $invoice_emails);
        $admin_email_array      =   explode(',', $admin_emails);
        if($this->merchant_company){
            foreach ($this->merchant_company as $merchant_email_message){
                $mail   =   new Zend_Mail();
                $mail->addTo($merchant_email_message['email'], $merchant_email_message['company']);
                if($invoice_email_array){
                    foreach ($invoice_email_array as $invoice_email_message){
                        $invoice_email_info =   explode('|', $invoice_email_message);
                        $mail->addBcc($invoice_email_info[0], $invoice_email_info[1]);
                    }
                    
                }
                if($admin_email_array){
                    foreach ($admin_email_array as $admin_email_message){
                        $admin_email_info =   explode('|', $admin_email_message);
                        $mail->setFrom($admin_email_info[0], $admin_email_info[1]);
                    }
                    
                }
                $title  =   'Invoice of '.$merchant_email_message['company'].' is ready in the B2B System';
                $email_txt  =   "Hi,\r\n"
                                ."\r\n"
                                ."It's time to generate invoice for the merchant ".$merchant_email_message['company'].".\r\n"
                                ."\r\n"
                                ."Cheers\r\n"
                                ."IT Department";
                $mail->setSubject($title);
                $mail->setBodyText($email_txt);
                $mail->send($this->smtp);
            }
        }
    }
}

?>
