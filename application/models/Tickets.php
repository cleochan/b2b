<?php

class Tickets extends Zend_Db_Table
{
	protected $_name = 'tickets';

	var $request;
	var $keyword;
	var $lm; //limit
	var $page;
	var $st; //sort
	
	function PushListData()
	{
		$find = $this->select();
		
		//Step1: limit the project
		if($_SESSION['search_ticket_projects_current'])
		{
			$find->where("project = ?", $_SESSION['search_ticket_projects_current']);
		}
		
		//Step2: limit the users
		$request_string = new Users();
		$staffs = $request_string -> GetStaffInfo($_SESSION['search_ticket_users_current']);
		
		foreach($staffs as $string)
		{
			$condition[] = "composer like '%".$string."%' or participants like '%".$string."%' or user_related like '%".$string."%' ";
		}
		
		if(!empty($condition))
		{
			$condition_sql = implode(' or ', $condition);
		}
		
		$find->where($condition_sql);
		
		//Step3: limit the status/keywords
		switch($this->request)
		{
			case 1: //pending
				$find->where("status = ?", 1);
				break;
			case 2: //processing
				$find->where("status = ?", 2);
				break;
			case 3: //closed
				$find->where("status = ?", 3);
				break;
			case 4: //canceled
				$find->where("status = ?", 4);
				break;
			case "search": //search
				if($this->keyword)
				{
					if("#" == substr($this->keyword,0,1))
					{
						$find->where("id = ?", trim(substr($this->keyword,1)));
					}elseif("%23" == substr($this->keyword,0,3))
					{
						$find->where("id = ?", trim(substr($this->keyword,3)));
					}else
					{
						$find->where("title like '%".trim($this->keyword)."%' ");
					}
				}else
				{
					$find->where("id = ?", 0); //post nothing
				}
				break;
			default://all
				break;
		}
		
		//Step4: order
		$find->order("update_when DESC");
		
		
		//Step5: limit and offset
		$this->lm = 20;
		$offset = ($this->page - 1) * $this->lm;
		
		$find->limit($this->lm, $offset);
		
		
		//Fetch
		$result = $this->fetchAll($find);
               
		//Create More Info
		$users = new Users();
		$idstr = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;

		if(!empty($result))
		{
            foreach($result as $key => $val)
			{
				$data['id'] = $val->id;
				$data['title'] = $val->title;
				$data['update_when'] = $val->update_when;
				$data['dead_line'] = $val->dead_line;
				$data['pri_str'] = $this->Priority($val->priority);
				$data['status_str'] = $this->GetStatusStr($val->status);
				$data['update_who_realname'] = $users -> GetRealName($val->update_who);
				$data['composer'] = $users -> GetRealName($val->composer);
                $data['act'] = $users -> MyRole($idstr, $val->participants, $val->user_related);

				// will be redo
                //$data['act'] = $act_str;
				
				//is_read
                if(strpos($val->make_read, $idstr) || "0" == strval(strpos($val->make_read, $idstr)))
				{
					$data['is_read'] = 1;
				}else 
				{
					$data['is_read'] = 0;
				}
				
				//is_focus
				if(strpos($val->make_focus, $idstr) || "0" == strval(strpos($val->make_focus, $idstr)))
				{
					$data['is_focus'] = 1;
				}else
				{
					$data['is_focus'] = 0;
				}
				
				//dead line warning
				if(in_array($val->status, array(1,2,3))) //created, processing, testing
				{
					if($data['dead_line'])
					{
						$data['dead_line'] = "<font color='".$this->DeadLineWarning($data['dead_line'])."'>".substr($data['dead_line'],0,10)."</font>";
					}else
					{
						$data['dead_line'] = "";
					}
				}
				
				$data_group[] = $data;
			}
		}
		
		return $data_group;
	}
	
	function DeadLineWarning($dl)
	{
		if($dl)
		{
			$now = time();
			$dead_line = mktime(substr($dl,11,2), substr($dl,14,2), substr($dl,17,2), substr($dl,5,2), substr($dl,8,2), substr($dl,0,4));
			$dis = $dead_line - $now;
			$warn_time = 2 * 24 * 60 * 60; //2 days advanced
		
			if(0 >= $dis)
			{
				$result = "#FF0000";
			}elseif(0 < $dis && $warn_time >= $dis)
			{
				$result = "#E37819";
			}else
			{
				$result = "#000";
			}
		}else
		{
			$result = "";
		}
		
		return $result;
	}
	
	function Priority($num)
	{
		switch($num)
		{
			case 2:
				$str = "<font color='#ff0000'>urgent</font>";
				break;
			default:
				$str = "<font color='#666'>normal</font>";
				break;
		}
		
		return $str;
	}
	
	function GetStatusStr($status_key)
	{
		switch($status_key)
		{
			case 1:
				$str = "Pending";
				break;
			case 2:
				$str = "Processing";
				break;
			case 3:
				$str = "Closed";
				break;
			case 4:
				$str = "Canceled";
				break;
			default:
				$str = "";
				break;
		}
		
		return $str;
	}
	
	function StatusArray()
	{
		$status[1] = "Pending";
		$status[2] = "Processing";
		$status[3] = "Closed";
		$status[4] = "Canceled";
		
		return $status;
	}
	
	function MakeRead($type, $ticket_id, $who=NULL) //$who = id@username  type1=insert read, type2=clean all
	{
		$origine = $this->select();
		$origine -> where("id = ?", $ticket_id);
		$data = $this->fetchRow($origine);
		
		if(1 == $type && $who)
		{
			if($data->make_read)
			{
				$read = explode("|", $data->make_read);
				if(!in_array($who, $read))
				{
					$read[] = $who;
				}
				$result = implode("|", $read);
			}else
			{
				$result = $who;
			}

			$data->make_read = $result;
			$data->save();
		}elseif(2 == $type)
		{
			$data->make_read = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
			$data->save();
		}
	}
	
	function UserRelated($action, $ticket_id, $who) //$who = id@username  action1=insert, action2=delete
	{
		$origine = $this->select();
		$origine -> where("id = ?", $ticket_id);
		$data = $this->fetchRow($origine);
		
		if(1 == $action)
		{
			if($data->user_related)
			{
				$read = explode("|", $data->user_related);
				if(!in_array($who, $read))
				{
					$read[] = $who;
				}
				$result = implode("|", $read);
			}else
			{
				$result = $who;
			}

			$data->user_related = $result;
			$data->save();
		}elseif(2 == $action) //delete
		{
			if($data->user_related)
			{
				$read = explode("|", $data->user_related);
				if(in_array($who, $read))
				{
					foreach($read as $r1 => $r2)
                    {
                        if($r2 == $who)
                        {
                            unset($read[$r1]);
                        }
                    }
				}
				if(!empty($read))
                {
                    $result = implode("|", $read);
                }else
                {
                    $result = '';
                }
			}else
			{
				$result = '';
			}

			$data->user_related = $result;
			$data->save();
		}
	}
	
	function MakeFocus($ticket_id)
	{
		$current_id = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
		
		$ticket = $this -> fetchRow('id = "'.$ticket_id.'"');
		
		if(strpos($ticket->make_focus, $current_id) || "0" == strval(strpos($ticket->make_focus, $current_id))) //remove
		{
			$result = 0;
			$focus = explode("|", $ticket->make_focus);
			foreach($focus as $key => $val)
			{
				if($val == $current_id)
				{
					unset($focus[$key]);
				}
			}
			$focus_str = implode("|", $focus);
		}else //insert
		{
			$result = 1;
			if($ticket->make_focus)
			{
				$str = explode("|", $ticket->make_focus);
				{
					$str[] = $current_id;
					$focus_str = implode("|", $str);
				}
			}else
			{
				$focus_str = $current_id;
			}
		}
		
		$ticket -> make_focus = $focus_str;
		$ticket -> save();
				
		return $result;
	}
	
	function UpdateTime($type, $ticket_id=NULL)
	{
		$now = date("Y-m-d H:i:s");
		
		switch($type)
		{
			case 2:
				$result[0] = "processing_date";
				break;
			case 3:
				$result[0] = "closed_date";
				break;
			case 4:
				$result[0] = "canceled_date";
				break;
			default:
				$result[0] = "";
				break;
		}
		
		if($ticket_id)
		{
			switch($type)
			{
				case 2:
					$data = $this->fetchRow('id = "'.$ticket_id.'"');
					if($data->processing_date)
					{
						$result[1] = $data->processing_date;
					}else
					{
						$result[1] = $now;
					}
					break;
				case 3:
					$result[1] = $now;
					break;
				case 4:
					$result[1] = $now;
					break;
				default:
					$result[1] = "";
					break;
			}
		}else //new ticket
		{
			$result[1] = $now;
		}
		
		return $result;
	}
	
	function CountData()
	{
		$find = $this->select();
		$find->from("tickets",array("status","count(id) as CT"));
		
		//Step1: limit the project
		if($_SESSION['search_ticket_projects_current'])
		{
			$find->where("project = ?", $_SESSION['search_ticket_projects_current']);
		}
		
		//Step2: limit the users
		if(1 == $_SESSION["Zend_Auth"]["storage"]->level_view_tickets) //view self
		{
			$string = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
			$find->where("composer like '%".$string."%' or participants like '%".$string."%' or user_related like '%".$string."%' ");
		}else //view dept
		{
			$request_string = new Users();
			$staffs = $request_string -> GetStaffInfo($_SESSION["Zend_Auth"]["storage"]->id);
			
			foreach($staffs as $string)
			{
				$condition[] = "composer like '%".$string."%' or participants like '%".$string."%' or user_related like '%".$string."%' ";
			}
			
			if(!empty($condition))
			{
				$condition_sql = implode(' or ', $condition);
			}
			
			$find->where($condition_sql);
		}
		
		//Step3: unread
		$string2 = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
		$find->where("make_read NOT like ?", "%".$string2."%");
		
		//Step4: order
		$find->order("update_when DESC");
		
		//Step5: group
		$find->group("status");
		
		
		//Fetch
		$result = $this->fetchAll($find);

		//Make array
		$status['pending'] = 0;
		$status['processing'] = 0;
		$status['closed'] = 0;
		$status['canceled'] = 0;
		$status['all'] = 0;
		
		foreach($result as $result_val)
		{
			if(1 == $result_val['status'])
			{
				$status['pending'] += $result_val['CT'];
				$status['all'] += $result_val['CT'];
			}elseif(2 == $result_val['status'])
			{
				$status['processing'] += $result_val['CT'];
				$status['all'] += $result_val['CT'];
			}elseif(3 == $result_val['status'])
			{
				$status['closed'] += $result_val['CT'];
				$status['all'] += $result_val['CT'];
			}elseif(4 == $result_val['status'])
			{
				$status['canceled'] += $result_val['CT'];
				$status['all'] += $result_val['CT'];
			}
		}
		
		if($status['pending'])
		{
			$status['pending'] = "<font color='#2A62FF'>(".$status['pending'].")</font>";
		}else
		{
			$status['pending'] = "";
		}
		
		if($status['processing'])
		{
			$status['processing'] = "<font color='#2A62FF'>(".$status['processing'].")</font>";
		}else
		{
			$status['processing'] = "";
		}
        
		if($status['closed'])
		{
			$status['closed'] = "<font color='#2A62FF'>(".$status['closed'].")</font>";
		}else
		{
			$status['closed'] = "";
		}
		
		if($status['canceled'])
		{
			$status['canceled'] = "<font color='#2A62FF'>(".$status['canceled'].")</font>";
		}else
		{
			$status['canceled'] = "";
		}
		
		if($status['all'])
		{
			$status['all'] = "<font color='#2A62FF'>(".$status['all'].")</font>";
		}else
		{
			$status['all'] = "";
		}
				
		return $status;
	}
	
	function GetLastOne()
	{
		$find = $this->select();
		
		//Step1: Status
		$find->where("status = ?", 2);
		
		//Step2: limit the users
		if(1 == $_SESSION["Zend_Auth"]["storage"]->level_view_tickets) //view self
		{
			$string = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
			$find->where("composer like '%".$string."%' or participants like '%".$string."%' or user_related like '%".$string."%' ");
		}else //view dept
		{
			$request_string = new Users();
			$staffs = $request_string -> GetStaffInfo($_SESSION["Zend_Auth"]["storage"]->id);
			
			foreach($staffs as $string)
			{
				$condition[] = "composer like '%".$string."%' or participants like '%".$string."%' or user_related like '%".$string."%' ";
			}
			
			if(!empty($condition))
			{
				$condition_sql = implode(' or ', $condition);
			}
			
			$find->where($condition_sql);
		}
		
		//Step3: unread
		$string2 = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
		$find->where("make_read NOT like ?", "%".$string2."%");
		
		//Step4: order
		$find->order("update_when DESC");
		
		//Fetch
		$result = $this->fetchRow($find);
		
		return $result["title"];
	}
	
}



