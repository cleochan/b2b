<?php

class KpiTickets extends Zend_Db_Table
{
	protected $_name = 'kpi_tickets';
    
    
    function StatusTrigger($uid, $action)
    {
        $tickets_time = new KpiTicketsTime();
        
        $tickets_time -> Trigger($uid, $action);
        
        $a = $this->fetchRow("tickets_users_id = '".$uid."'");
        $a->used_time = $tickets_time ->UsedTimeCalculation($uid);
                
        $b = $a->save();
        
        return $b;
    }
    
    function DifficultyArray($val)
    {
        $diff = array(
            1 => "Very Simple",
            2 => "Simple",
            3 => "Normal",
            4 => "Difficult",
            5 => "Very Difficult"
        );
        
        if($val)
        {
            $result = $diff[$val];
        }else{
            $result = $diff;
        }
        
        return $result;
    }
}