<?php

class Databases_Tables_Users extends Zend_Db_Table
{
    protected $_name = 'users';
    var $user_id;
    var $email;
    var $password;
    var $user_type;
    var $user_status;
    
    function AddUser()
    {
        if($this->email && $this->password && $this->user_type && $this->user_status)
        {
            $data = array(
                'email' => $this->email,
                'password' => $this->password,
                'user_type' => $this->user_type,
                'user_status' => $this->user_status
            );
            
            try{
                $user_id = $this->insert($data);
            }  catch (Zend_Exception $exp){
                var_dump($exp->getMessage());
            }
        }else{
            $user_id = NULL;
        }
        
        return $user_id;
    }
    
    function UserInfo()
    {
        if($this->user_id)
        {
            $user = $this->user_id;
        }else{
            $user = $_SESSION["Zend_Auth"]["storage"]->user_id;
        }
        
        $data = $this->fetchRow("user_id='".$user."'");
        $result = $data->toArray();
        
        return $result;
    }
}