<?php

class Databases_Tables_UsersExtension extends Zend_Db_Table
{
    protected $_name = 'users_extension';
    var $user_id;
    var $company;
    var $contact_name;
    var $contact_phone;
    var $credit;
    
    function AddUserExtension()
    {
        if($this->user_id)
        {
            $data = array(
                'user_id' => $this->user_id,
                'company' => $this->company,
                'contact_name' => $this->contact_name,
                'contact_phone' => $this->contact_phone,
                'join_date' => date("Y-m-d"),
                'credit' => round($this->credit, 2)
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
}