<?php

class Databases_Tables_LogsFinancial extends Zend_Db_Table
{
    protected $_name = 'logs_financial';
    var $start_date;
    var $end_date;
    var $user_id;
    var $action_type;
    var $p_query_order = "issue_time|DESC";
    var $p_qty_per_page = 50; //50 data / page
    var $p_current_page = 1; // start from the first page
    
    
    function Pagination()
    {
        //Get amount page qty
        $select = $this->select();
        $select->from($this->_name, array("count(logs_financial_id) as ct"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->action_type)
        {
            $select->where("action_type = ?", $this->action_type);
            
            $cond[] = "action_type=".$this->action_type;
        }
        
        $result = $this->fetchRow($select);
        
        $total_page = ceil($result['ct'] / $this->p_qty_per_page);
        
        //Generate HTML
        if($total_page > 1)
        {
            if(1 == $this->p_current_page)
            {
                $cond[] = "p_current_page=".($this->p_current_page+1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-financial-report/user_id/".$this->user_id."?".$params."'>Next >></a>";
            }elseif($total_page == $this->p_current_page){
                $cond[] = "p_current_page=".($this->p_current_page-1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-financial-report/user_id/".$this->user_id."?".$params."'><< Previous</a>";
            }else{
                $cond[] = "p_current_page=".($this->p_current_page-1);
                $params = implode("&", $cond);
                $html = "<a href='/admin/admin-financial-report/user_id/".$this->user_id."?".$params."'><< Previous</a>";
                $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                $cond = array_pop($cond);
                $cond[] = "p_current_page=".($this->p_current_page+1);
                $params_next = implode("&", $cond);
                $html .= "<a href='/admin/admin-financial-report/user_id/".$this->user_id."?".$params_next."'>Next >></a>";
            }
        }else{
            $html = "";
        }
        
        return $html;
    }
    
    function PushList()
    {
        $select = $this->select();
        $cond = array();
        if($this->start_date)
        {
            $select->where("issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date=".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date=".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("user_id = ?", $this->user_id);
            $cond[] = "user_id=".$this->user_id;
        }
        if($this->action_type)
        {
            $select->where("action_type = ?", $this->action_type);
            
            $cond[] = "action_type=".$this->action_type;
        }
        if($this->p_query_order)
        {
            $qorder = explode("|", $this->p_query_order);
            $select->order($qorder[0]." ".$qorder[1]);
        }
        if($this->p_qty_per_page && $this->p_current_page)
        {
            $select->limit($this->p_qty_per_page, $this->p_qty_per_page*($this->p_current_page-1));
        }
        
        $data = $this->fetchAll($select);
        $result = $data->toArray();
        
        return $result;
    }
}