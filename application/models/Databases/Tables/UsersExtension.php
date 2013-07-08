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
            
            $user->company = $this->company;
            $user->contact_name = $this->contact_name;
            $user->contact_phone = $this->contact_phone;
            $user->credit = $this->credit;
            $user->discount = $this->discount;
            $user->address  =  $this->address;
            $user->post_code     =  $this->post_code;
            $user->suburb        =  $this->suburb;
            $user->state         =  $this->state;
            $user->flat_rate_shipping   =   $this->flat_rate_shipping;
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
            
            return $row->toArray();
        }
    }
}