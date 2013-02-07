<?php

class KpiTicketsTime extends Zend_Db_Table
{
	protected $_name = 'kpi_tickets_time';
    
    function UsedTimeCalculation($id)
    {
        $data = $this->select();
        $data -> where("tickets_users_id = ?", $id);
        $data -> order("id ASC");
        $data_array = $this->fetchAll($data);
        
        $ct = count($data_array);
        
        $n = 0;
        $init = 0;
        $pool = 0;
        $now = time();
        
        foreach($data_array as $d)
        {
            $n += 1;
            
            if(1 == $d['action_type']) //start
            {
                $init = $d['event_time'];
                
                if($n == $ct && $init)
                {
                    $pool += ($now - $init);
                    $init = 0;
                }
                
            }else //stop
            {
                if($init)
                {
                    $pool += ($d['event_time'] - $init);
                    $init = 0;
                }
            }
        }
        
        return $this->FormatTime($pool, 1);
    }
    
    function FormatTime($data, $type)
    {
        switch($type)
        {
            default: //by second
                $hour = floor($data / 3600);
                $hour_mod = $data % 3600;
                
                $min = floor($hour_mod / 60);
                
                $sec = $hour_mod % 60;
                
                if($hour < 10)
                {
                    $hour_format = "0".$hour;
                }else
                {
                    $hour_format = $hour;
                }
                
                if($min < 10)
                {
                    $min_format = "0".$min;
                }else
                {
                    $min_format = $min;
                }
                
                if($sec < 10)
                {
                    $sec_format = "0".$sec;
                }else
                {
                    $sec_format = $sec;
                }
                
                $result = $hour_format.":".$min_format.":".$sec_format;
                
                break;
        }
        
        return $result;
    }
    
    function Trigger($uid, $action)
    {
        $a = $this->createRow();
        $a->tickets_users_id = $uid;
        $a->event_time = time();
        $a->action_type = $action;
        $b = $a->save();
        
        return $b;
    }
    
    function GetRecentTime($uid)
    {
        $data = $this->UsedTimeCalculation($uid);
        
        $result = 0;
        
        if($data)
        {
            $val = explode(":", $data);
            
            $result += intval($val[0]) * 3600;
            $result += intval($val[1]) * 60;
            $result += intval($val[2]);
        }
        
        return $result;
    }
	
}