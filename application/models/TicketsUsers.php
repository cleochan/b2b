<?php

class TicketsUsers extends Zend_Db_Table
{
	protected $_name = 'tickets_users';
	
    function UpdateStatus($uid, $action)
    {
        $a = $this->fetchRow("id = '".$uid."'");
        $a->status = $action;
        $b = $a->save();
        
        $kpi_tickets = new KpiTickets();
        $kpi_tickets ->StatusTrigger($uid, $action);
        
        return $b;
    }
    
    function GetUserArray($ticket_id)
    {
        $a = $this->fetchAll("ticket_id = '".$ticket_id."' and (del='0' or del='' or del is NULL)");
        
        $c = array();
        
        if(count($a))
        {
            foreach($a as $b)
            {
                $c[] = $b['user_id'];
            }
        }
        
        $c = array_unique($c);
        
        return $c;
    }
    
    function MoveUp($id)
    {
        $original = $this->fetchRow("id = '".$id."'");
        
        if($original['user_id'] && $original['sequence'])
        {
            $next = $this->select();
            $next -> where("user_id = ?", $original['user_id']);
            $next -> where("sequence < ?", $original['sequence']);
            $next -> order("sequence DESC");
            $next -> limit(1);
            
            $next_value = $this->fetchRow($next);
            
            if($next_value['sequence'])
            {
                $new_sequence = $next_value['sequence'] - 1;
            }else{
                $new_sequence = $original['sequence'] - 1;
            }
            
            $original -> sequence = $new_sequence;
                
            if($original -> save())
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
    
    function MoveDown($id)
    {
        $original = $this->fetchRow("id = '".$id."'");
        
        if($original['user_id'] && $original['sequence'])
        {
            $next = $this->select();
            $next -> where("user_id = ?", $original['user_id']);
            $next -> where("sequence > ?", $original['sequence']);
            $next -> order("sequence DESC");
            $next -> limit(1);
            
            $next_value = $this->fetchRow($next);
            
            if($next_value['sequence'])
            {
                $new_sequence = $next_value['sequence'] + 1;
            }else{
                $new_sequence = $original['sequence'] + 1;
            }
            
            $original -> sequence = $new_sequence;
                
            if($original -> save())
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
}



