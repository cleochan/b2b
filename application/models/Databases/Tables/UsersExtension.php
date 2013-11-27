<?php

class Databases_Tables_UsersExtension extends Zend_Db_Table
{
    protected $_name = 'users_extension';
    var $user_id;
    var $company;
    var $contact_name;
    var $contact_phone;
    var $credit;
    var $discount;
    var $balance;
    var $address;
    var $post_code;
    var $suburb;
    var $state;
    var $flat_rate_shipping;
    
    var $invoice_type;
    var $invoice_value;
    
    var $account_name;
    var $account_phone;
    var $account_email;
    
    function AddUserExtension()
    {
        if($this->user_id)
        {
            $bpay_generator = new Algorithms_Extensions_Bpay();
            
            $data = array(
                'user_id' => $this->user_id,
                'company' => $this->company,
                'contact_name' => $this->contact_name,
                'contact_phone' => $this->contact_phone,
                'address'       =>  $this->address,
                'post_code'     =>  $this->post_code,
                'suburb'        =>  $this->suburb,
                'state'         =>  $this->state,
                'flat_rate_shipping'    =>  $this->flat_rate_shipping,
                'invoice_type'          =>  $this->invoice_type,
                'invoice_value'         =>  $this->invoice_value,
                'account_name'          =>  $this->account_name,
                'account_phone'         =>  $this->account_phone,
                'account_email'         =>  $this->account_email,
                'join_date' => date("Y-m-d"),
                'credit' => round($this->credit, 2),
                'discount' => round($this->discount, 2),
                'bpay_ref' => $bpay_generator->RefGenerator(1000+$this->user_id)
            );
            
            try{
                $this->insert($data);
            }  catch (Zend_Exception $exp){
                var_dump($exp->getMessage());
            }
        }else{
            echo "User ID missed";die;
        }
    }
    
    function EditUserExtension()
    {
        if($this->user_id)
        {
            $user = $this->fetchRow("user_id='".$this->user_id."'");
            if($this->company){
                $user->company = $this->company;
            }
            if($this->contact_name){
                $user->contact_name = $this->contact_name;
            }
            if($this->contact_phone){
                $user->contact_phone = $this->contact_phone;
            }
            if($this->credit){
                $user->credit = $this->credit;
            }
            if($this->discount){
                $user->discount = $this->discount;
            }
            if($this->address){
                $user->address  =  $this->address;
            }
            if($this->post_code){
                $user->post_code     =  $this->post_code;
            }
            if($this->suburb){
                $user->suburb        =  $this->suburb;
            }
            if($this->state){
                $user->state         =  $this->state;
            }
            if($this->flat_rate_shipping){
                $user->flat_rate_shipping   =   $this->flat_rate_shipping;
            }
            if($this->invoice_type){
                $user->invoice_type   =   $this->invoice_type;
            }
            if($this->invoice_value){
                $user->invoice_value   =   $this->invoice_value;
            }
            if($this->account_name){
                $user->account_name   =   $this->account_name;
            }
            if($this->account_email){
                $user->account_email   =   $this->account_email;
            }
            if($this->account_phone){
                $user->account_phone   =   $this->account_phone;
            }
            $user->save();
        }
    }
    
    function UserInfo()
    {
        if($this->user_id)
        {
            $user = $this->user_id;
        }else{
            $user = $_SESSION["Zend_Auth"]["storage"]->user_id;
        }
        
        $result = $this->fetchRow("user_id='".$user."'");
        
        return $result;
    }
    
    function IsUserExist()
    {
        if($this->email)
        {
            $result = $this->fetchRow("email='".$this->email."'");
        }
        
        return $result['user_id'];
    }
    
    //This function is called in LogsFinancial::AddLog() already, don't call it in any other way!!!!!!
    function UpdateBalance()
    {
        if($this->user_id && NULL !== $this->balance)
        {
            $row = $this->fetchRow("user_id='".$this->user_id."'");
            $row->balance = $this->balance;
            if($row->save())
            {
                $result = TRUE;
            }else{
                $result = FALSE;
            }
        }else{
            $result = FALSE;
        }
        
        return $result;
    }
    
    function GetUserId($type, $value) //type 1 = from customer_ref
    {
        if(1 == $type)
        {
            $row = $this->fetchRow("bpay_ref = '".$value."'");
        }
        
        return $row['user_id'];
    }
    
    function CheckCompanyInCsv()
    {
        if($this->company)
        {
            $row = $this->fetchRow("company = '".trim($this->company)."'");
            
            if(empty($row))
            {
                $result = array(); 
            }else{
                $result = $row->toArray();
            }
            
            return $result;
        }
    }
}