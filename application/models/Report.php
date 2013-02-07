<?php

class Report
{
    const STARTUP_YEAR = 2010;
    const STARTUP_WEEK = 38;
    const STARTUP_DATE = '2010-09-20 00:00:00';
    const STARTUP_TIMESTAMP = 1284912000;
    
    function PushWeeklyList()
    {
    	$result = array();

		/* 
		 *	计算当年周数（截止到今天）
		 */
    	$now_year = date("Y",time());  //获得当前年份
    	$startup_week = Report::STARTUP_WEEK; //获得开始日期在当年中是第几周，这里是第38周
		$now_week = strftime("%U", time()) ;		//获得当前日期在当年中是第几周
        if(preg_match("/^0\d/", $now_week))
        {
        $now_week = substr($now_week, 1);
        }
		if(2010==$now_year)
		{
			for($i=$now_week;$i>=$startup_week;$i--)
			{
				$result[$now_year][$i] = $this->DateStartEndCalculation($now_year.'-'.$i);
			}
		}
		else
		{
			for($i=$now_week;$i>=1;$i--)
			{
				$result[$now_year][$i] = $this->DateStartEndCalculation($now_year.'-'.$i);
			}
		}
		
		/* 
		 *	计算历年周数
		 */
    	for($j=$now_year-1;$j>=Report::STARTUP_YEAR;$j--)
    	{
			if(Report::STARTUP_YEAR < $j)
			{
				$total_week = $this->GetWeeks($j);
				for($i=$total_week;$i>=1;$i--)
				{
					$result[$j][$i] = $this->DateStartEndCalculation($j.'-'.$i);
				}
			}
			elseif(Report::STARTUP_YEAR == $j)
			{
				$startup_week = Report::STARTUP_WEEK;
				$total_week = $this->GetWeeks($j);
				for($i=$total_week;$i>=$startup_week;$i--)
				{
					$result[$j][$i] = $this->DateStartEndCalculation($j.'-'.$i);
				}
			}
    	}
    	return $result;
    }
    
    /* 
	 *	获得当年总周数，瑞年52周多2天，平年52周多1天，每累计7天，当年就多一周
	 */
    function GetWeeks($end_year)
    {
    	$start_year = Report::STARTUP_YEAR;
    	$total_days = 0;
    	for($i=$start_year;$i<=$end_year;$i++)
    	{
    		if(0==($i%4))
    		{
    			$total_days = $total_days + 2;
    		}
    		else
    		{
    			$total_days = $total_days + 1;
    		} 		
    	}
    	if(0==($total_days%7))
    	{
    		return 53;
    	}
    	else
    	{
    		return 52;
    	}
    }

	
	function GetData($val) //2010-38
	{
		if($val)
		{
			$gd = $this -> DateStartEndCalculation($val);

			$week_start = $gd['week_start'];
			$week_end = $gd['week_end'];
			
			$last_week_start = $gd['last_week_start'];
			$last_week_end = $gd['last_week_end'];

			$tickets = new Tickets();
			$users = new Users();
			$projects = new Projects();

			//Push Last Week
			$ticket1 = $tickets -> select();
			$ticket1 -> where('(finished_date >= "'.$last_week_start.'" and finished_date <= "'.$last_week_end.'") or (update_when >= "'.$last_week_start.'" and update_when <= "'.$last_week_end.'")' );
			$ticket1 -> where('status IN (?)', array(2,3,4));
			$ticket1 -> order("project ASC");
			$ticket1 -> order("master ASC");
			$ticket1 -> order('started_dealing_date ASC');
			$ticket1_array = $tickets -> fetchAll($ticket1);
			foreach($ticket1_array as $ticket1_val)
			{
				$ticket1_result['title'] = "[".$projects->GetVal($ticket1_val['project'])."] ".$ticket1_val['title'];
				$ticket1_result['started_dealing_date'] = $this -> FormatDate($ticket1_val['started_dealing_date']);
				$ticket1_result['finished_date'] = $this -> FormatDate($ticket1_val['finished_date']);
				$ticket1_result['dead_line'] = $this -> FormatDate($ticket1_val['dead_line']);
				
				if(trim($ticket1_val['operator']))
				{
					$ticket1_result['owner'] = $users -> GetNameString(implode("|", array($ticket1_val['master'], $ticket1_val['operator'])),1);
				}else
				{
					$ticket1_result['owner'] = $users -> GetNameString($ticket1_val['master'],1);
				}
				$ticket1_result['progress'] = $ticket1_val['progress'];
				$ticket1_result['status'] = $tickets -> GetStatusStr($ticket1_val['status']);
				
				$ticket1_pool[] = $ticket1_result;
			}
			
			
			//Push This Week
			$ticket2 = $tickets -> select();
			$ticket2 -> where('status in (?)', array(2,3)); //processin/testing
			$ticket2 -> order("project ASC");
			$ticket2 -> order("master ASC");
			$ticket2 -> order('started_dealing_date ASC');
			$ticket2_array = $tickets -> fetchAll($ticket2);
			foreach($ticket2_array as $ticket2_val)
			{
				$ticket2_result['title'] = "[".$projects->GetVal($ticket2_val['project'])."] ".$ticket2_val['title'];
				$ticket2_result['started_dealing_date'] = $this -> FormatDate($ticket2_val['started_dealing_date']);
				$ticket2_result['finished_date'] = $this -> FormatDate($ticket2_val['finished_date']);
				$ticket2_result['dead_line'] = $this -> FormatDate($ticket2_val['dead_line']);
				
				if(trim($ticket2_val['operator']))
				{
					$ticket2_result['owner'] = $users -> GetNameString(implode("|", array($ticket2_val['master'], $ticket2_val['operator'])),1);
				}else
				{
					$ticket2_result['owner'] = $users -> GetNameString($ticket2_val['master'],1);
				}
				$ticket2_result['progress'] = $ticket2_val['progress'];
				$ticket2_result['status'] = $tickets -> GetStatusStr($ticket2_val['status']);
				
				$ticket2_pool[] = $ticket2_result;
			}
			
			//Push Future Task
			$ticket3 = $tickets -> select();
			$ticket3 -> where('status = ?', 1); //created
			$ticket3 -> order("project ASC");
			$ticket3 -> order("master ASC");
			$ticket3 -> order('started_dealing_date ASC');
			$ticket3_array = $tickets -> fetchAll($ticket3);
			foreach($ticket3_array as $ticket3_val)
			{
				$ticket3_result['title'] = "[".$projects->GetVal($ticket3_val['project'])."] ".$ticket3_val['title'];
				$ticket3_result['started_dealing_date'] = $this -> FormatDate($ticket3_val['started_dealing_date']);
				$ticket3_result['finished_date'] = $this -> FormatDate($ticket3_val['finished_date']);
				$ticket3_result['dead_line'] = $this -> FormatDate($ticket3_val['dead_line']);
				
				if(trim($ticket3_val['operator']))
				{
					$ticket3_result['owner'] = $users -> GetNameString(implode("|", array($ticket3_val['master'], $ticket3_val['operator'])),1);
				}else
				{
					$ticket3_result['owner'] = $users -> GetNameString($ticket3_val['master'],1);
				}
				$ticket3_result['progress'] = $ticket3_val['progress'];
				$ticket3_result['status'] = $tickets -> GetStatusStr($ticket3_val['status']);
				
				$ticket3_pool[] = $ticket3_result;
			}
			
			$result[0] = $ticket1_pool;
			$result[1] = $ticket2_pool;
			$result[2] = $ticket3_pool;
			
		}
		
		return $result;
	}
	
	function FormatDate($date) //YYYY-MM-DD HH:II:SS
	{
		if($date)
		{
			$yy = substr($date, 0, 4);
			$mm = substr($date, 5, 2);
			$dd = substr($date, 8, 2);
		
			$result = date("M j", mktime(0, 0, 0, $mm, $dd, $yy));
		}else
		{
			$result = "N/A";
		}

		return $result;
	}
	
	function DateStartEndCalculation($val)
	{
		$yw = explode("-", $val);
		$rest_day=strftime("%U", mktime(0, 0, 0, 1, 1, $yw[0]));
        if($rest_day == "01")
        {
                    if($yw[1] == "01")
                    {
                      $plus_days = 1;
                    }
                    else
                    {
                        $plus_days = ($yw[1]-1)*7+1;

                    }
       
        }
        else
        {
            $plus_days = $yw[1] * 7 - ($rest_day - 1);

        }
//			echo date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, $yw[0]));
//            die();
            $result['week_start'] = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, $plus_days, $yw[0]));
            $result['week_end'] = date("Y-m-d H:i:s", mktime(23, 59, 59, 1, $plus_days+6, $yw[0]));		
            $result['last_week_start'] = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, $plus_days-7, $yw[0]));
            $result['last_week_end'] = date("Y-m-d H:i:s", mktime(23, 59, 59, 1, $plus_days-1, $yw[0]));
		return $result;

	}
}











