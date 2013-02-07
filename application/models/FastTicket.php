<?php

class FastTicket extends Zend_Db_Table
{
	protected $_name = 'fast_ticket';
	
    function GetTree($user_id)
    {
        $select = $this->select();
        $select->where("creator = ?", $user_id);
        $select->order("tname ASC");
        
        $data = $this->fetchAll($select);
        
        $result = array();
        
        if(count($data))
        {
            foreach($data as $d)
            {
                $result[$d['id']] = $d['tname'];
            }
        }
        
        return $result;
    }
}



