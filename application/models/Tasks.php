<?php

class Tasks
{
    var $request;
	var $keyword;
	var $lm; //limit
	var $page;
    
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
	function PushListData()
    {
        $select = $this->db->select();
        $select->from("tickets_users as u", array("u.id as uid", "u.ticket_id as utid","u.user_id as urid","u.user_type as utype","u.notes as unotes","u.status as ustatus","u.creator as ucreator","u.workbook as uworkbook","u.sequence as usequence",));
        $select->joinLeft("tickets as t", "u.ticket_id=t.id", array("t.project as tproject", "t.priority as tpriority", "t.title as ttitle", "t.status as tstatus", "t.update_who as tupdate_who", "t.update_when as tupdate_when", "t.dead_line as tdeadline"));
        $select->joinLeft("kpi_tickets as k", "k.tickets_users_id = u.id", array("suggestion_hour as khour", "used_time as kused"));
        $select->where("u.del is null or u.del='' or u.del='0'");
        
        //Step1: limit the project
		if($_SESSION['search_ticket_projects_current'])
		{
			$select->where("t.project = ?", $_SESSION['search_ticket_projects_current']);
		}
        
        //Step2: limit the users
		$request_string = new Users();
		$user_array = $request_string -> GetStaffInfo($_SESSION['search_ticket_users_current'], 2);
		
        if(!empty($user_array))
        {
            $select->where("u.user_id in (?)", $user_array);
        }
        
        //Step3: limit the status/keywords
		switch($this->request)
		{
			case 1: //pending
				$select->where("t.status = ?", 1);
				break;
			case 2: //processing
				$select->where("t.status = ?", 2);
				break;
			case 3: //closed
				$select->where("t.status = ?", 3);
				break;
			case 4: //canceled
				$select->where("t.status = ?", 4);
				break;
			case "search": //search
				if($this->keyword)
				{
					if("#" == substr($this->keyword,0,1))
					{
						$select->where("t.id = ?", trim(substr($this->keyword,1)));
					}elseif("%23" == substr($this->keyword,0,3))
					{
						$select->where("t.id = ?", trim(substr($this->keyword,3)));
					}else
					{
						$select->where("t.title like '%".trim($this->keyword)."%' ");
					}
				}else
				{
					$select->where("t.id = ?", 0); //post nothing
				}
				break;
			default://all
				break;
		}
        
        //Step4: order
		$select->order("u.sequence ASC");
        $select->order("u.id ASC");
        
        //Step5: limit and offset
		$this->lm = 20;
		$offset = ($this->page - 1) * $this->lm;
		
		$select->limit($this->lm, $offset);
		
		
		//Fetch
        $data = $this->db->fetchAll($select);
        
        $result = array();
        
        $tickets_model = new Tickets();
        $project_model = new Projects();
        $user_model = new Users();
        $kpi_model = new Kpi();
        
        foreach($data as $d_key => $d_val)
        {
            $temp = array();
            
            $temp['table_id'] = $d_val['uid'];
            $temp['staff'] = $user_model->GetRealName($d_val['urid']);
            $temp['status'] = $this->StatusLight($d_val['ustatus']);
            $temp['status_id'] = $d_val['ustatus'];
            $temp['ticket_id'] = $d_val['utid'];
            $temp['ticket_title'] = "<a href='/index/view/id/".$d_val['utid']."/type/".$this->request."'>".$d_val['ttitle'];
            $temp['notes'] = $d_val['unotes'];
            $temp['priority'] = $tickets_model->Priority($d_val['tpriority']);
            $temp['project'] = $project_model->GetVal($d_val['tproject']);
            $temp['deadline'] = substr($d_val['tdeadline'], 0, 10);
            $temp['ref_hour'] = $d_val['khour'];
            $temp['actual_hour'] = $d_val['kused'];
            
            $result[] = $temp;
        }
        
        return $result;
    }
    
    function StatusLight($status)
    {
        if($status)
        {
            $result = "<img src='/images/green.png' border='0' title='In progress' />";
        }else
        {
            $result = "<img src='/images/red.png' border='0' title='Pending' />";
        }
        
        return $result;
    }
}



