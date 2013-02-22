<?php

class Databases_Tables_UsersExtension extends Zend_Db_Table
{
    protected $_name = 'users_extension';
    var $user_id;
    var $company;
    var $contact_name;
    var $contact_phone;
    var $credit;
    var $balance;
    
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
                'join_date' => date("Y-m-d"),
                'credit' => round($this->credit, 2),
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
        if($this->user_id && $this->balance)
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
}