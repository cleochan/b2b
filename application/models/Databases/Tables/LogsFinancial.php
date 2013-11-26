<?php

class Databases_Tables_LogsFinancial extends Zend_Db_Table
{
    protected $_name = 'logs_financial';
    var $start_date;
    var $end_date;
    var $user_id;
    var $p_query_order = "issue_time|DESC";
    var $p_qty_per_page = 50; //50 data / page
    var $p_current_page = 1; // start from the first page
    var $action_type;
    var $action_affect;
    var $trans_id;
    var $action_value;
    var $instant_balance;
    var $invoice_end_date;
    
    function Pagination()
    {
        //Get amount page qty
        $select = $this->select();
        $select->from($this->_name, array("count(logs_financial_id) as ct"));
        $cond = array();
        if($this->start_date)
        {
            $select->where("issue_time >= ?", $this->start_date." 00:00:00");
            $cond[] = "start_date/".$this->start_date;
        }
        if($this->end_date)
        {
            $select->where("issue_time <= ?", $this->end_date." 23:59:59");
            $cond[] = "end_date/".$this->end_date;
        }
        if($this->user_id)
        {
            $select->where("user_id = ?", $this->user_id);
            $cond[] = "user_id/".$this->user_id;
        }
        if($this->action_type)
        {
            $select->where("action_type = ?", $this->action_type);
            
            $cond[] = "action_type/".$this->action_type;
        }
        
        $result = $this->fetchRow($select);
        
        $total_page = ceil($result['ct'] / $this->p_qty_per_page);
        
        //Generate HTML
        if($total_page > 1)
        {
            if(1 == $this->p_current_page)
            {
                $cond[] = "p_current_page/".($this->p_current_page+1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/admin-financial-report/".$params."'>Next >></a>";
            }elseif($total_page == $this->p_current_page){
                $cond[] = "p_current_page/".($this->p_current_page-1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/admin-financial-report/".$params."'><< Previous</a>";
            }else{
                $cond[] = "p_current_page/".($this->p_current_page-1);
                $params = implode("/", $cond);
                $html = "<a href='/admin/admin-financial-report/".$params."'><< Previous</a>";
                $html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                array_pop($cond);
                $cond[] = "p_current_page/".($this->p_current_page+1);
                $params_next = implode("/", $cond);
                $html .= "<a href='/admin/admin-financial-report/".$params_next."'>Next >></a>";
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
        if($this->invoice_end_date){
            $select->where("issue_time <= ?", $this->invoice_end_date." 00:00:00");
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
        if($this->action_affect){
            $select->where("action_affect = ?", $this->action_affect);
            $cond[] = "action_affect=".$this->action_affect;
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
        $select->order('issue_time DESC');
        $data = $this->fetchAll($select);
        $result = $data->toArray();
        
        return $result;
    }
    
    function AddLog()
    {
        if($this->user_id && $this->action_type && $this->action_affect && $this->action_value)
        {
            //Get current balance
            $user_model = new Databases_Joins_GetUserInfo();
            $user_info = $user_model->GetUserInfo($this->user_id);
            
            if(1 == $this->action_affect) //recharge
            {
                $new_balance = $user_info['balance'] + $this->action_value;
            }elseif(2 == $this->action_affect) //deduct
            {
                $new_balance = $user_info['balance'] - $this->action_value;
            }
            
            
            //Add Log
            $data = array(
                "user_id" => $this->user_id,
                "action_type" => $this->action_type,
                "action_affect" => $this->action_affect,
                "trans_id" => $this->trans_id,
                "action_value" => $this->action_value,
                "instant_balance" => $new_balance,
                "issue_time" => date("Y-m-d H:i:s"),
                "operator_id" => $_SESSION["Zend_Auth"]["storage"]->user_id,
                "ip" => $_SERVER['REMOTE_ADDR']
            );
            
            if($this->insert($data))
            {
                $user_extension = new Databases_Tables_UsersExtension();
                $user_extension->user_id = $this->user_id;
                $user_extension->balance = $new_balance;
                if($user_extension->UpdateBalance())
                {
                    $result = TRUE;
                }else{
                    $result = FALSE;
                }
            }else{
                $result = FALSE;
            }
        }
        
        return $result;
    }
    
    function CheckCustomerRefExist($ref)
    {
        $row = $this->fetchRow("trans_id='".$ref."'");
        
        return $row['logs_financial_id'];
    }
}