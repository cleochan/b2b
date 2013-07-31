<?php

class Databases_Tables_Users extends Zend_Db_Table
{
    protected $_name = 'users';
    var $user_id;
    var $email;
    var $password;
    var $user_type;
    var $user_status;
    var $company;
    var $contact_name;
    var $contact_phone;
    var $credit;
    var $discount;
    var $address;
    var $post_code;
    var $suburb;
    var $state;
    var $flat_rate_shipping;
    
	function SetUser($obj)
	{
		$variables = array('user_id', 
			'email',
			'password', 
			'user_type', 
			'user_status', 
			'company', 
			'contact_name', 
			'contact_phone', 
			'credit', 
			'discount', 
			'address', 
			'post_code', 
			'suburb', 
			'state', 
			'flat_rate_shipping'
		);

		foreach($variables as $key)
		{
			if(is_object($obj))
			{
				if(isset($this->$key) && isset($obj->$key))
				{
					$this->$key = $obj->$key;
				}
			}
			else if(is_array($obj))
			{
				if(isset($this->$key) && isset($obj[$key]))
				{
					$this->$key = $obj[$key];
				}
			}
		}
	}

    function AddUser()
    {
        if($this->email && $this->password && $this->user_type)
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
        
        if($user_id)
        {
            //Extension Info
            $user_ext = new Databases_Tables_UsersExtension();
            $user_ext->user_id = $user_id;
            $user_ext->company = $this->company;
            $user_ext->contact_name = $this->contact_name;
            $user_ext->contact_phone = $this->contact_phone;
            $user_ext->credit = $this->credit;
            $user_ext->discount = $this->discount;
            
            $user_ext->address      =   $this->address;
            $user_ext->post_code    =   $this->post_code;
            $user_ext->suburb       =   $this->suburb;
            $user_ext->state        =   $this->state; 
            $user_ext->flat_rate_shipping   =   $this->flat_rate_shipping;
            
             try{
                $user_ext ->AddUserExtension();
            }  catch (Zend_Exception $exp){
                var_dump($exp->getMessage());
            }
        }
        
        return $user_id;
    }
    
    function EditUser()
    {
        if($this->user_id)
        {
            $user = $this->fetchRow("user_id='".$this->user_id."'");
            
            $user->email = $this->email;
            if($this->password)
            {
                $user->password = $this->password;
            }
            $user->user_status = $this->user_status;
            
            $user->save();
            //update extension
            $user_ext = new Databases_Tables_UsersExtension();
            $user_ext->user_id = $this->user_id;
            $user_ext->company = $this->company;
            $user_ext->contact_name = $this->contact_name;
            $user_ext->contact_phone = $this->contact_phone;
            $user_ext->credit = $this->credit;
            $user_ext->discount = $this->discount;
            
            $user_ext->address      =   $this->address;
            $user_ext->post_code    =   $this->post_code;
            $user_ext->suburb       =   $this->suburb;
            $user_ext->state        =   $this->state;
            $user_ext->flat_rate_shipping   =   $this->flat_rate_shipping;
            
            $user_ext->EditUserExtension();
            
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
            if($this->user_id) //exculde the self
            {
                $result = $this->fetchRow("email='".$this->email."' and user_id != '".$this->user_id."'");
            }else{
                $result = $this->fetchRow("email='".$this->email."'");
            }
        }
        
        return $result['user_id'];
    }
}
