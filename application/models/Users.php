<?php

class Users extends Zend_Db_Table
{
	protected $_name = 'users';
		
	function MakeString($id_array, $type=NULL)
	{
		if(1 == $type) //make from realname
		{
			$pool = array();
			
			if($id_array)
			{
				$realname_array = explode(",", $id_array);
				
				foreach($realname_array as $realname_val)
				{
					$realname_val = trim($realname_val);
					
					if($realname_val)
					{
						//check repeat
						if(!in_array($realname_val, $pool))
						{
							$pool[] = $realname_val;
							//exact search
							$search = $this->select();
							$search -> where("realname like ?", $realname_val);
							$get_search = $this->fetchRow($search);
					
							if(!$get_search['id'])
							{
								//generally search
								$search = $this->select();
								$search -> where("realname like ?", "%".$realname_val."%");
								$get_search = $this->fetchAll($search);
						
								if(1 == count($get_search))
								{
									//make string
									foreach($get_search as $get_search_val)
									{
										$name_array[] = $get_search_val['id']."@".$get_search_val['username'];
									}
								}else
								{
									//error
									$error = TRUE;
									$error_name[] = $realname_val;
								}
							}else
							{
								//make string
								$name_array[] = $get_search['id']."@".$get_search['username'];
							}	
						}
					}
				}
				
				if($error)
				{
					$result = implode(", ", $error_name);
					
					$str[0] = "error1";
					$str[1] = $result;
				}elseif(!$error && !empty($name_array))
				{
					$str = implode("|", $name_array);
				}else
				{
					$str[0] = "error2";
					$str[1] = "";
				}
			}
		}else //make from id
		{
			$id_array = array_unique($id_array);
			
			foreach($id_array as $val)
			{
				$info = $this->fetchRow('id = "'.$val.'"');
				$str_array[] = $val."@".$info['username'];
			}
			
			$str = implode("|", $str_array);
		}
		
		return $str;
	}
	
	function GetNameString($string, $special=FALSE)
	{
		if($string)
		{
			$string_array = explode("|", $string);
			$pool = array();
			
			foreach($string_array as $val)
			{
				if(!in_array($val, $pool))
				{				
					$id_array = explode("@", $val); //$id_array[0]=id, $id_array[1]=username
					$info = $this->fetchRow('id = "'.$id_array[0].'"');
					if(!empty($info))
					{
						$names[] = $info['realname'];
					}
					$pool[] = $val;
				}
			}
		
			$result = implode(", ", $names);
		
			if(FALSE == $special)
			{
				$result .= ", ";
			}
		}else
		{
			$result = "";
		}
		
		return $result;
	}
	
	function GetStaffInfo($current_user_id, $type=NULL) //$type=2 with id only
	{
		$get1 = $this->select();
		$get1->where("id = ?", $current_user_id);
		$get1_array = $this->fetchRow($get1);
		$loop_key[] = $current_user_id."@".$get1_array['username'];
		$loop_temp = array();
		$try = TRUE;
		
		if(!empty($get1_array))
		{
			while(TRUE == $try)
			{
				$try = FALSE;
				
				foreach($loop_key as $lp)
				{
					if(!in_array($lp, $loop_temp))
					{
						$loop_temp[] = $lp;
						$get2 = $this->select();
						$get2->where("supervisor like ?", "%".$lp."%");
						$get2_array = $this->fetchAll($get2);
						
						if(!empty($get2_array))
						{
							foreach($get2_array as $get2_val)
							{
								$loop_key[] = $get2_val['id']."@".$get2_val['username'];
							}
							
							$try = TRUE;
						}
					}

				}
			}
		}
        
        if(2 == $type && !empty($loop_key))
        {
            $just_id = array();
            foreach($loop_key as $a)
            {
                $b = explode("@", $a);
                $just_id[] = $b[0];
            }
            
            $loop_key = $just_id;
        }
		
		return $loop_key;
	}

	function GetStaffInfoArray($current_user_id)
	{
		$get1 = $this->select();
		$get1->where("id = ?", $current_user_id);
		$get1_array = $this->fetchRow($get1);
		$loop_key[$current_user_id] = $get1_array['realname'];
		$loop_temp = array();
		$try = TRUE;

		if(!empty($get1_array))
		{
			while(TRUE == $try)
			{
				$try = FALSE;
				
				foreach($loop_key as $lk => $lp)
				{
					if(!in_array($lk, $loop_temp))
					{
						$loop_temp[] = $lk;
						$get2 = $this->select();
						$lp_str = $this->MakeString(array($lk));
						$get2->where("supervisor like ?", "%".$lp_str."%");
                        $get2->where("status !=?","0");
						$get2_array = $this->fetchAll($get2);
						
						if(!empty($get2_array))
						{
							foreach($get2_array as $get2_val)
							{
								$the_id = $get2_val['id'];
								$loop_key[$the_id] = $get2_val['realname'];
							}
							
							$try = TRUE;
						}
					}

				}
			}
		}
		
		//sort array
		asort($loop_key);
		
		return $loop_key;
	}
	
	function GetRealName($str)
	{
		$name = explode("|", $str);
		$name_db = $this->fetchRow('id = "'.$name[0].'"');
		
		return $name_db['realname'];
	}
	
	function GetRealNameString()
	{
		$name = $this->select();
		$name -> where('status = ?', 1);
		$name -> order('realname ASC');
		$name_array = $this->fetchAll($name);
		
		foreach($name_array as $val)
		{
			$push[] = "'".$val['realname']."'";
		}
		
		if(!empty($push))
		{
			$result = implode(", ", $push);
		}
		
		return $result;
	}
	
	function GetTicketAuth($ticket_id)
	{
		//auth of modifing ticket 0/1
		if(2 == $_SESSION["Zend_Auth"]["storage"]->level_view_tickets &&  1 == $_SESSION["Zend_Auth"]["storage"]->department)
		{
			$result = 1;
		}else
		{
			$result = 0;
		}
        
        if(!$result)
        {
            $tickets = new Tickets();
            $data = $tickets->fetchRow("id='".$ticket_id."'");
            $d = explode("@", $data['composer']);
            if($d[0] == $_SESSION["Zend_Auth"]["storage"]->id)
            {
                $result = 1;
            }
        }
		
		return $result;
	}
	
	function GetRequestAuth($request_id)
	{
		$user_str = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
		
		$tickets = new Requests();
		$ticket = $tickets -> fetchRow('id = "'.$request_id.'"');
			
		if($ticket->id)
		{
			if($ticket->composer == $user_str || 1 == $_SESSION["Zend_Auth"]["storage"]->department)
			{
				$result = 1;
			}else
			{
				$result = 0;
			}
		}else
		{
			$result = 0;
		}
		
		return $result;
	}
	
	function MakeList()
	{
		
		$users = $this->select();
		$users -> order('department ASC');
		$users -> order('realname ASC');
		$user_array = $this->fetchAll($users);
		
		$dept = new Departments();
        
        $pool_a = array();
        $pool_i = array();
		
		foreach($user_array as $val)
		{
			$user = array();
			
			if($val['status'])
			{
                $user['id'] = $val['id'];
                $user['username'] = $val['username'];
                $user['department'] = $dept -> GetDeptVal($val['department'], 1);
                $user['realname'] = $val['realname'];
                $user['supervisor'] = $this -> GetNameString($val['supervisor'], TRUE);
                $user['status'] = "<font color='green'>Active</font>";
                $user['level_view_tickets'] = $this -> LevelViewTicket($val['level_view_tickets']);
                $user['level_mgt'] = $this -> LevelMgt($val['level_mgt']);
                $user['email'] = $val['email'];			

                $pool_a[] = $user;
            }else
            {
                $user['id'] = $val['id'];
                $user['username'] = $val['username'];
                $user['department'] = $dept -> GetDeptVal($val['department'], 1);
                $user['realname'] = $val['realname'];
                $user['supervisor'] = $this -> GetNameString($val['supervisor'], TRUE);
                $user['status'] = "<font color='red'>Inactive</font>";
                $user['level_view_tickets'] = $this -> LevelViewTicket($val['level_view_tickets']);
                $user['level_mgt'] = $this -> LevelMgt($val['level_mgt']);
                $user['email'] = $val['email'];			

                $pool_i[] = $user;
            }

		}
        
        $pool = array_merge($pool_a, $pool_i);
		
		return $pool;
	}
	
	function MakeListForDropDown()
	{
		
		$users = $this->select();
		$users -> order('department ASC');
		$users -> order('realname ASC');
		$user_array = $this->fetchAll($users);
		
		$dept = new Departments();
        
        $pool_a = array();
        $pool_i = array();
		
		foreach($user_array as $val)
		{
			$user = array();
			
			if($val['status'])
			{
                $user['id'] = $val['id'];
                $user['department'] = $dept -> GetDeptVal($val['department'], 1);
                $user['realname'] = $val['realname'];	

                $pool_a[] = $user;
            }else
            {
                $user['id'] = $val['id'];
                $user['department'] = $dept -> GetDeptVal($val['department'], 1);
                $user['realname'] = $val['realname'];

                $pool_i[] = $user;
            }

		}
        
        $result = array();
        
        foreach($pool_a as $pool_a_val)
        {
            $result[] = array($pool_a_val['id'], $pool_a_val['department']." - ".$pool_a_val['realname']);
        }
        
        $result[] = array("", "------");
        
        foreach($pool_i as $pool_i_val)
        {
            $result[] = array($pool_i_val['id'], $pool_i_val['department']." - ".$pool_i_val['realname']);
        }
		
		return $result;
	}
	
	function LevelViewTicket($id)
	{
		switch($id)
		{
			case 1:
				$result = "SELF";
				break;
			case 2:
				$result = "MGT";
				break;
			default:
				break;
		}
		
		return $result;
	}
	
	function LevelViewTicketOptions()
	{
		$result = array(
							1 => "SELF",
							2 => "MGT"
						);
		
		return $result;
	}
	
	function LevelMgt($id)
	{
		switch($id)
		{
			case 1:
				$result = "REQUESTS / TICKETS";
				break;
			case 2:
				$result = "LEADER";
				break;
			case 3:
				$result = "ADMIN";
				break;
			case 4:
				$result = "IT STAFF";
				break;
			default:
				break;
		}
		
		return $result;
	}
	
	function LevelMgtOptions()
	{
		$result = array(
                            1 => "REQUESTS / TICKETS",
							2 => "LEADER",
							3 => "ADMIN",
							4 => "IT STAFF"
						);
		
		return $result;
	}
	
	function MakeOptions($my_id)
	{
		$users = $this->select();
		$users -> where("id != ?", $my_id);
		$users -> order("realname ASC");
		$users_array = $this->fetchAll($users);
		
		foreach($users_array as $val)
		{
			$id = $val['id'];
			$realname = $val['realname'];
		
			$result[$id] = $realname;
		}
		
		return $result;
	}
	
	function IsValid()
	{
		$data = $this->fetchRow('id = "'.$_SESSION["Zend_Auth"]["storage"]->id.'"');
        
        $_SESSION["Zend_Auth"]["storage"]->default_list = $data->default_list;
		
		return $data->status;
	}

	function MakeMailUserList($id_array)
	{
		if($id_array)
        {
            $name = $this->select();
            $name -> where('id in (?)', $id_array);
            $name_array = $this->fetchAll($name);

            foreach($name_array as $val)
            {
                $email = $val['email'];
                $result[$email] = $val['realname'];
            }
        }
		
		return $result;
	}
    
    function GetUserIdArray($string)
    {
        $d = array();
        
        if($string)
        {
            $a = explode("|", $string);
            
            foreach($a as $b)
            {
                $c = explode("@", $b);
                $d[] = $c[0];
            }
        }
        
        return $d;
    }
    
    function UserType($id)
    {
        $user_group = array(
                            0 => "Choose..",
                            1 => "Designer",
                            2 => "Programmer",
                            3 => "Tester",
                            4 => "DBA",
                            5 => "System Administrator",
                            6 => "Webmaster",
                            7 => "Assistant",
                            8 => "Other",
                            9 => "Leader",
                           10 => "PM"
                            );
        
        if($id)
        {
            $result = $user_group[$id];
        }else
        {
            $result = $user_group;
        }
        
        return $result;
    }
    
    function MyRole($my, $participants, $user_related)
    {
        if($participants)
        {
            $p = explode("|", $participants);
            
            if(in_array($my, $p))
            {
                $is_p = 1;
            }else
            {
                $is_p = 0;
            }
        }else
        {
            $is_p = 0;
        }
        
        if($user_related)
        {
            $u = explode("|", $user_related);
            
            if(in_array($my, $u))
            {
                $is_u = 1;
            }else
            {
                $is_u = 0;
            }
        }else
        {
            $is_u = 0;
        }
        
        if($is_p && $is_u)
        {
            $result = "Participant, Staff";
        }elseif($is_p && !$is_u)
        {
            $result = "Participant";
        }elseif(!$is_p && $is_u)
        {
            $result = "Staff";
        }else
        {
            $result = "/";
        }
        
        return $result;
    }
    
    function SwitchMode($new_mode=NULL)
    {
        if($new_mode)
        {
            $new_mode = 1;
        }else
        {
            $new_mode = '';
        }
        
        $data = $this->fetchRow("id = '".$_SESSION["Zend_Auth"]["storage"]->id."'");
        $data -> default_list = $new_mode;
        $data -> save();
    }
    
    function GetSkype($str)
    {
        $name = explode("|", $str);
		$name_db = $this->fetchRow('id = "'.$name[0].'"');
		
        $result = "";
        
        if($name_db['skype'])
        {
            $result = '<a href="skype:'.$name_db['skype'].'?chat"><img src="/images/skype.png" style="border: none;" alt="Skype me" /></a>';

        }
        
		return $result;
    }
    
    function GetTeamArray($user_id)
    {
        $data = $this->fetchRow("id = '".$user_id."'");
        $result = array();
        
        if($data['team_id'])
        {
            $list = $this->fetchAll("team_id = '".$data['team_id']."'");
            if(count($list))
            {
                foreach($list as $l)
                {
                    $result[] = $l['id'];
                }
            }
        }else{
            $result = array($user_id);
        }
        
        return $result;
    }
    
    function GetItTeamMembers($type) //type=1 ID only
    {
        if(1 == $type)
        {
            $data = $this->fetchAll("department=1 and status=1", "realname ASC");
            $result = array();
            if(!empty($data))
            {
                foreach($data as $d)
                {
                    $result[] = $d['id'];
                }
            }
        }
        
        return $result;
    }
}