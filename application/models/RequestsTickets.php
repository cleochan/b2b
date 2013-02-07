<?php

class RequestsTickets extends Zend_Db_Table
{
	protected $_name = 'requests_tickets';
    
    function RelatedTickets($request_id)
    {
        $select = $this->select();
        $select -> where('request_id = ?', $request_id);
        $data = $this->fetchAll($select);
        
        $str = "";
        
        if(count($data))
        {
            foreach($data as $d)
            {
                $str .= "<a href='/index/view/id/".$d['ticket_id']."'>#".$d['ticket_id']."</a>&nbsp;&nbsp;";
            }
            
            $str .= "<br />";
        }
        
        return $str;
    }
	
}











