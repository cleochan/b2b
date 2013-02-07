<?php

class Kpi
{   
    var $page;
    var $lm;
    var $user;
    var $date_from;
    var $date_to;
    
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
	function PushListData($type) //1 = activate  2 = inactivated
    {
        $user_model = new Users();
        
        $related_user = $user_model->GetStaffInfo($_SESSION["Zend_Auth"]["storage"]->id, 2);
        
        $select = $this->db->select();
        $select->from("tickets as t", array("title as ttitle", "id as tid", "processing_date as tpdate", "closed_date as tcdate"));
        $select->joinLeft("tickets_users as u", "u.ticket_id=t.id", array("user_id as uuser", "notes as unotes", "workbook as uworkbook"));
        $select->joinLeft("kpi_tickets as k", "k.tickets_users_id = u.id", array("id as kid", "score as kscore", "efficiency as keff", "suggestion_hour as ksh", "used_time as kused"));
        $select->where("t.status = ?", 3); //ticket must be closed
        $select->where("u.creator IN (?)", $related_user);
        if(1 == $type) //1 = activate
        {
            $select->where("k.score is null");
        }elseif(2 == $type) //2 = inactivated
        {
            $select->where("k.score is not null");
        }
        if($this->user)
        {
            $select->where("u.user_id = ?", $this->user);
        }
        $select->order("k.id DESC");
        $this->lm = 20;
		$offset = ($this->page - 1) * $this->lm;

		$select->limit($this->lm, $offset);
		
		
		//Fetch
        $data = $this->db->fetchAll($select);
        
        $result = array();
        
        foreach($data as $d_key => $d_val)
        {
            $temp = array();
            
            $temp['id'] = $d_val['kid'];
            $temp['staff'] = $user_model->GetRealName($d_val['uuser']);
            $temp['ticket'] = $d_val['ttitle'];
            $temp['ticket_id'] = $d_val['tid'];
            $temp['notes'] = $d_val['unotes'];
            $temp['ref_hour'] = $d_val['ksh'];
            $temp['actual_hour'] = $d_val['kused'];
            $temp['efficiency'] = $this->EfficiencyArray($d_val['keff']);
            $temp['score'] = $d_val['kscore'];
            $temp['processing_date'] = $d_val['tpdate'];
            $temp['closed_date'] = $d_val['tcdate'];
            
            $result[] = $temp;
        }
        
        
        return $result;
    }
    
    function Efficiency($ref_hour, $actual_time) //1 poor  2 acceptable   3 good
    {
        
        if($actual_time && $ref_hour)
        {
            $a_hour = explode(":", $actual_time);
            $r_hour = explode(":", $ref_hour);
            
            if(intval($a_hour[0]) < intval($r_hour[0]))
            {
                $result = 3; //good
            }elseif(intval($a_hour[0]) == intval($r_hour[0]))
            {
                $result = 2; //acceptable
            }else
            {
                $result = 1; //poor
            }
            
        }else
        {
            $result = 3;
        }
        
        
        return $result;
    }
    
    function EfficiencyArray($val=NULL)
    {
        $eff = array(
            1 => "Poor",
            2 => "Acceptable",
            3 => "Good"
        );
        
        if($val)
        {
            $result = $eff[$val];
        }else
        {
            $result = $eff;
        }
        
        return $result;
    }
    
    function ScoreArray($val=NULL)
    {
        $score = array(
            1 => "Unacceptable",
            2 => "Poor",
            3 => "Acceptable",
            4 => "Good",
            5 => "Excellent"
        );
        
        if($val)
        {
            $result = $score[$val];
        }else
        {
            $result = $score;
        }
        
        return $result;
    }
    
    function GetStaffInfo($user_id)
    {
        $select = $this->db->select();
        $select->from("users as u", array("supervisor", "first_name", "last_name", "team_title"));
        $select->joinLeft("departments as d", "d.id=u.department", array("name as dname"));
        $select->joinLeft("teams as t", "t.id=u.team_id", array("tname"));
        $select->joinLeft("team_level as l", "l.id=u.team_level", array("l.level_name as llevel"));
        $select->where("u.id = ?", $user_id);
        $data = $this->db->fetchRow($select);
        
        $info = array();
        
        if(!empty($data))
        {
            $users = new Users();
            
            $info['first_name'] = $data['first_name'];
            $info['last_name'] = $data['last_name'];
            $info['team_title'] = $data['team_title'];
            $info['supervisor'] = $users->GetNameString($data['supervisor'], TRUE);
            $info['department'] = $data['dname'];
            $info['team_name'] = $data['tname'];
            $info['level_name'] = $data['llevel'];
        }
        
        return $info;
    }
    
    function IsValidViewKpi($current_id, $user_id)
    {
        $users = new Users();
        $staff_array = $users->GetStaffInfo($current_id, 2);
        
        if(in_array($user_id, $staff_array))
        {
            $result = 1;
        }else{
            $result = 0;
        }
        
        return $result;
    }
    
    function GetTaskInfo($user_id)
    {
        $result = array();
        
        //self result start
        
        $select = $this->db->select();
        $select->from("kpi_tickets as k", array("score as kscore", "efficiency as keff", "suggestion_hour as kref", "used_time as kused", "difficulty as kdiff"));
        $select->joinLeft("tickets_users as u", "u.id=k.tickets_users_id", array("ticket_id as utid"));
        $select->joinLeft("tickets as t", "t.id=u.ticket_id", array("title as ttitle"));
        $select->where("t.status = ?", 3); //closed
        $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
        $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
        $select->where("u.user_id = ?", $user_id);
        $select->where("u.del is NULL");
        $select->where("k.score > ?", 0);
        $select->order("t.closed_date ASC");
        $data = $this->db->fetchAll($select);
        
        if(!empty($data))
        {
            $list = array();
            $n = 0; // total
            $m = 0; // delay total
            
            foreach($data as $d)
            {
                $details['ticket_id'] = $d['utid'];
                $details['ticket'] = $d['ttitle'];
                $details['complexity'] = $d['kdiff'];
                $details['efficiency'] = $d['keff'];
                $details['score'] = $d['kscore'];
                
                $list[] = $details;
                $n += 1;
                $complexity_sum += $d['kdiff'];
                $efficiency_sum += $d['keff'];
                $score_sum += $d['kscore'];
                if(1 == $d['keff'])
                {
                    $m += 1;
                }
            }
            
            $result_self['details'] = $list;
            $result_self['ticket_qty'] = $n;
            $result_self['complexity'] = round($complexity_sum / $n, 2);
            $result_self['efficiency'] = round($efficiency_sum / $n, 2);
            $result_self['score'] = round($score_sum / $n, 2);
            $result_self['delayed_ticket'] = round($m/$n*100, 2);
            
            $result['self'] = $result_self;
        }
        
        //team result start
        
        $users = new Users();
        $user_array = $users->GetTeamArray($user_id);
        $user_count = count($user_array);
        
        $select = $this->db->select();
        $select->from("kpi_tickets as k", array("score as kscore", "efficiency as keff", "suggestion_hour as kref", "used_time as kused", "difficulty as kdiff"));
        $select->joinLeft("tickets_users as u", "u.id=k.tickets_users_id", array("ticket_id as utid"));
        $select->joinLeft("tickets as t", "t.id=u.ticket_id", array("title as ttitle"));
        $select->where("t.status = ?", 3); //closed
        $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
        $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
        $select->where("u.user_id IN (?)", $user_array);
        $select->where("u.del is NULL");
        $select->where("k.score > ?", 0);
        $select->order("t.closed_date ASC");
        $data = $this->db->fetchAll($select);
       
        if(!empty($data))
        {
            $list = array();
            $n = 0; // total
            $m = 0; // delay total
            $complexity_sum = 0;
            $efficiency_sum = 0;
            $score_sum = 0;
            
            foreach($data as $d)
            {
                $n += 1;
                $complexity_sum += $d['kdiff'];
                $efficiency_sum += $d['keff'];
                $score_sum += $d['kscore'];
                if(1 == $d['keff'])
                {
                    $m += 1;
                }
            }
            
            $result_team['ticket_qty'] = $n / $user_count;
            $result_team['complexity'] = round($complexity_sum / $n, 2);
            $result_team['efficiency'] = round($efficiency_sum / $n, 2);
            $result_team['score'] = round($score_sum / $n, 2);
            $result_team['delayed_ticket'] = round($m/$n*100, 2);
            
            $result['team'] = $result_team;
        }
        
        return $result;
    }
    
    function GetTrainingInfo($user_id)
    {
        $result = array();
        
        //self result start
        
        $select = $this->db->select();
        $select->from("training_trainee as e", array("score"));
        $select->joinLeft("training_arrangement as a", "a.id=e.arrangement_id", array("adate"));
        $select->joinLeft("training_library as b", "b.id=a.library_id", array("title as btitle"));
        $select->joinLeft("training_category as c", "c.id=b.category", array("cname as ccname"));
        $select->where("e.user_id = ?", $user_id);
        $select->where("e.score >= ?", 0);
        $select->where("a.adate >= ?", $this->date_from." 00:00:00");
        $select->where("a.adate <= ?", $this->date_to." 23:59:59");
        $select->order("a.adate ASC");
        $data = $this->db->fetchAll($select);
        
        if(!empty($data))
        {
            $list = array();
            $n = 0; // total
            $m = 0; // absence total
            
            foreach($data as $d)
            {
                $details['training_time'] = $d['adate'];
                $details['training_type'] = $d['ccname'];
                $details['training'] = $d['btitle'];
                $details['score'] = $d['score'];
                
                $list[] = $details;
                $n += 1;
                $score_sum += $d['score'];
                if('0' == strval($d['score']))
                {
                    $m += 1;
                }
            }
            
            $result_self['details'] = $list;
            $result_self['training_session'] = $n;
            $result_self['score'] = round($score_sum / $n, 2);
            $result_self['absence_rate'] = round($m/$n*100, 2);
            
            $result['self'] = $result_self;
        }
        
        //team result start
        
        $users = new Users();
        $user_array = $users->GetTeamArray($user_id);
        $user_count = count($user_array);
        
        $select = $this->db->select();
        $select->from("training_trainee as e", array("score"));
        $select->joinLeft("training_arrangement as a", "a.id=e.arrangement_id", array("adate"));
        $select->joinLeft("training_library as b", "b.id=a.library_id", array("title as btitle"));
        $select->joinLeft("training_category as c", "c.id=b.category", array("cname as ccname"));
        $select->where("e.user_id IN (?)", $user_array);
        $select->where("e.score >= ?", 0);
        $select->where("a.adate >= ?", $this->date_from." 00:00:00");
        $select->where("a.adate <= ?", $this->date_to." 23:59:59");
        $select->order("a.adate ASC");
        $data = $this->db->fetchAll($select);
        
        if(!empty($data))
        {
            $list = array();
            $n = 0; // total
            $m = 0; // absence total
            $score_sum = 0;
            
            foreach($data as $d)
            {
                $n += 1;
                $score_sum += $d['score'];
                if('0' == strval($d['score']))
                {
                    $m += 1;
                }
            }
            
            $result_team['training_session'] = round($n / $user_count, 2);
            $result_team['score'] = round($score_sum / $n, 2);
            $result_team['absence_rate'] = round($m/$n*100, 2);
            
            $result['team'] = $result_team;
        }
        
        return $result;
    }
    
    function KpiCalculationTaskByHour()
    {
        //$this->user
        //$this->date_from
        //$this->date_to
        
        //$result[1] User's Value
        //$result[2] Teams' Value
        
        if($this->user && $this->date_from && $this->date_to)
        {
            //staff
            $select = $this->db->select();
            $select->from("kpi_tickets_time as p", array("tickets_users_id", "event_time", "action_type"));
            $select->joinLeft("tickets_users as u", "u.id=p.tickets_users_id", array());
            $select->joinLeft("kpi_tickets as k", "u.id=k.tickets_users_id", array());
            $select->joinLeft("tickets as t", "t.id=u.ticket_id", array());
            $select->where("t.status = ?", 3); //closed
            $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
            $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
            $select->where("u.user_id = ?", $this->user);
            $select->where("u.del is NULL");
            $select->where("k.score > ?", 0);
            $select->order("t.closed_date ASC");
            $data = $this->db->fetchAll($select);
            
            $time_array_staff = $this->KpiCalculationTaskByHourLoop($data, 1);
            
            
            //team
            $users = new Users();
            $user_array = $users->GetTeamArray($this->user);
            $user_count = count($user_array);

            $select = $this->db->select();
            $select->from("kpi_tickets_time as p", array("tickets_users_id", "event_time", "action_type"));
            $select->joinLeft("tickets_users as u", "u.id=p.tickets_users_id", array());
            $select->joinLeft("kpi_tickets as k", "u.id=k.tickets_users_id", array());
            $select->joinLeft("tickets as t", "t.id=u.ticket_id", array());
            $select->where("t.status = ?", 3); //closed
            $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
            $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
            $select->where("u.user_id IN (?)", $user_array);
            $select->where("u.del is NULL");
            $select->where("k.score > ?", 0);
            $select->order("t.closed_date ASC");
            $data = $this->db->fetchAll($select);
            
            $time_array_team = $this->KpiCalculationTaskByHourLoop($data, $user_count);
            
            $result = array();
            $result[1] = $time_array_staff;
            $result[2] = $time_array_team;
            
            $result = $this->KpiCalculationTaskByHourArrayOptimization($result);
            
        }
        
        return $result;
    }
    
    function KpiCalculationTaskByHourLoop($data, $user_qty)
    {
        if(!empty($data))
        {
            $tickets_users_id_array = array();
              
            foreach($data as $d)
            {
                if($d['tickets_users_id'])
                {
                    $tickets_users_id_array[$d['tickets_users_id']][] = array(0=>$d['action_type'], 1=>$d['event_time']);
                }
            }
            
            $time_array = array();
                
            foreach($tickets_users_id_array as $divide_by_id)
            {
                foreach($divide_by_id as $group_one_id)
                {
                    if(1 == $group_one_id[0] && !$start_timestamp)
                    {
                        $start_timestamp = $group_one_id[1];
                    }elseif(0 == $group_one_id[0] && !$end_timestamp && $start_timestamp)
                    {
                        $end_timestamp = $group_one_id[1];
                    }

                    if($start_timestamp && $end_timestamp)
                    {
                        $start_hour = date("H", $start_timestamp);
                        $end_hour = date("H", $end_timestamp);
                        $hours = $end_hour - $start_hour;
                            
                        if(0 <= $hours)
                        {
                            for($n=0;$n<=$hours;$n++)
                            {
                                $hour_value = $start_hour + $n;
                                $time_array[$hour_value] += 1;
                            }
                        }

                        unset($start_timestamp);
                        unset($end_timestamp);
                    }
                }
            }
            
            foreach($time_array as $ta_key => $ta_val)
            {
                $time_array[$ta_key] = round($ta_val / $user_qty, 0);
            }
        }
        
        return $time_array;
    }
    
    function KpiCalculationTaskByHourArrayOptimization($data)
    {
        if(!empty($data))
        {
            $user_array = array();
            $team_array = array();
            $result[1] = array();
            $result[2] = array();
            
            for($n=0;$n<24;$n++)
            {
                $user_array[$n] = $data[1][$n];
                if(!$user_array[$n])
                {
                    $user_array[$n] = 0;
                }
                $team_array[$n] = $data[2][$n];
                if(!$team_array[$n])
                {
                    $team_array[$n] = 0;
                }
            }
            
            $result[1] = $user_array;
            $result[2] = $team_array;
        }
        
        return $result;
    }
    
    function KpiCalculationUsageUser()
    {
        //$this->user
        //$this->date_from
        //$this->date_to
        
        if($this->user && $this->date_from && $this->date_to)
        {
            $time_start = $this->GetTimeStamp($this->date_from, 1);
            $time_end = $this->GetTimeStamp($this->date_to, 2);
            
            $total_time = 0;
            $total_day = 0;
            
            $time_start += 1; //add a second, make it "00:00:01"
                
            while($time_end > $time_start)
            {
                if(!in_array(date("w", $time_start), array(0,6))) //not in the weekend
                {
                    $total_day += 1;
                }
                
                $time_start += 3600 * 24; //next day
            }
            
            $total_time = $total_day * 24 * 3600;
            
            if(0 > $total_time)
            {
                $total_time = 0;
            }

            //staff
            $select = $this->db->select();
            $select->from("kpi_tickets as k", array("used_time as ttime"));
            $select->joinLeft("tickets_users as u", "u.id=k.tickets_users_id", array());
            $select->joinLeft("tickets as t", "t.id=u.ticket_id", array());
            $select->where("t.status = ?", 3); //closed
            $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
            $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
            $select->where("u.user_id = ?", $this->user);
            $select->where("u.del is NULL");
            $select->where("k.score > ?", 0);
            $select->order("t.closed_date ASC");
            $data = $this->db->fetchAll($select);
            
            $time_usage = $this->KpiCalculationUsageSeconds($data);
            
            $diff = $total_time - $time_usage;
            if(0 > $diff)
            {
                $diff = 0;
            }
            
            $result['usage'] = $time_usage;
            $result['diff'] = $diff;
        }
        
        return $result;
    }
    
    function KpiCalculationUsageAvg()
    {
        //$this->user
        //$this->date_from
        //$this->date_to
        
        if($this->user && $this->date_from && $this->date_to)
        {
            //team
            $users = new Users();
            $user_array = $users->GetTeamArray($this->user);
            $user_count = count($user_array);
            
            $time_start = $this->GetTimeStamp($this->date_from, 1);
            $time_end = $this->GetTimeStamp($this->date_to, 2);
            
            $total_time = 0;
            $total_day = 0;
            
            $time_start += 1; //add a second, make it "00:00:01"
                
            while($time_end > $time_start)
            {
                if(!in_array(date("w", $time_start), array(0,6))) //not in the weekend
                {
                    $total_day += 1;
                }
                
                $time_start += 3600 * 24; //next day
            }
            
            $total_time = $total_day * 24 * 3600;
            
            if(0 > $total_time)
            {
                $total_time = 0;
            }

            //team
            $select = $this->db->select();
            $select->from("kpi_tickets as k", array("used_time as ttime"));
            $select->joinLeft("tickets_users as u", "u.id=k.tickets_users_id", array());
            $select->joinLeft("tickets as t", "t.id=u.ticket_id", array());
            $select->where("t.status = ?", 3); //closed
            $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
            $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
            $select->where("u.user_id in (?)", $user_array);
            $select->where("u.del is NULL");
            $select->where("k.score > ?", 0);
            $select->order("t.closed_date ASC");
            $data = $this->db->fetchAll($select);
            
            $time_usage = $this->KpiCalculationUsageSeconds($data);
            
            $diff = $total_time - floor($time_usage / $user_count);
            if(0 > $diff)
            {
                $diff = 0;
            }
            
            $result['usage'] = floor($time_usage / $user_count);
            $result['diff'] = $diff;
        }
        
        return $result;
    }
    
    function KpiCalculationUsageLeader()
    {
        //$this->user
        //$this->date_from
        //$this->date_to
        
        if($this->user && $this->date_from && $this->date_to)
        {
            $time_start = $this->GetTimeStamp($this->date_from, 1);
            $time_end = $this->GetTimeStamp($this->date_to, 2);
            
            $total_time = 0;
            $total_day = 0;
            
            $time_start += 1; //add a second, make it "00:00:01"
                
            while($time_end > $time_start)
            {
                if(!in_array(date("w", $time_start), array(0,6))) //not in the weekend
                {
                    $total_day += 1;
                }
                
                $time_start += 3600 * 24; //next day
            }
            
            $total_time = $total_day * 24 * 3600;
            
            if(0 > $total_time)
            {
                $total_time = 0;
            }

            //staff
            $select = $this->db->select();
            $select->from("kpi_tickets as k", array("suggestion_hour as ttime"));
            $select->joinLeft("tickets_users as u", "u.id=k.tickets_users_id", array());
            $select->joinLeft("tickets as t", "t.id=u.ticket_id", array());
            $select->where("t.status = ?", 3); //closed
            $select->where("t.closed_date >= ?", $this->date_from." 00:00:00");
            $select->where("t.closed_date <= ?", $this->date_to." 23:59:59");
            $select->where("u.user_id = ?", $this->user);
            $select->where("u.del is NULL");
            $select->where("k.score > ?", 0);
            $select->order("t.closed_date ASC");
            $data = $this->db->fetchAll($select);
            
            $time_usage = $this->KpiCalculationUsageSeconds($data);
            
            $diff = $total_time - $time_usage;
            if(0 > $diff)
            {
                $diff = 0;
            }
            
            $result['usage'] = $time_usage;
            $result['diff'] = $diff;
        }
        
        return $result;
    }
    
    function KpiCalculationUsageSeconds($data)
    {
        $result = 0;
        
        if(!empty($data))
        {
            foreach($data as $d)
            {
                $parts = explode(":", $d['ttime']);
                $sec = $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
                
                $result += $sec;
            }
        }
        
        return $result;
    }
    
    function GetTimeStamp($date, $type) //$type=1 Beginning $type=2 Ending
    {
        if(1 == $type)
        {
            $ext = " 00:00:00";
        }elseif(2 == $type)
        {
            $ext = " 23:59:59";
        }
        
        $a = $date.$ext;
        
        $result = mktime(substr($a, 11, 2), substr($a, 14, 2), substr($a, 17, 2), substr($a, 5, 2), substr($a, 8, 2), substr($a, 0, 4));
        
        return $result;
    }
}