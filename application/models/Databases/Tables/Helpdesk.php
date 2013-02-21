<?php

class Databases_Tables_Helpdesk extends Zend_Db_Table
{
    protected $_name = 'helpdesk';
    var $helpdesk_id;
    var $category;
    var $h_subject;
    var $h_contents;
    var $h_status;
    
    function PushList()
    {
        $select = $this->select();
        if($this->category)
        {
            $select->where("category = ?", $this->category);
        }
        if($this->helpdesk_id)
        {
            $select->where("helpdesk_id = ?", $this->helpdesk_id);
        }
        if($this->h_status)
        {
            $select->where("h_status = ?", $this->h_status);
        }
        $select ->order(array("category ASC", "h_status DESC", "issue_time DESC"));
        $data = $this->fetchAll($select);
        $result = $data->toArray();
        
        return $result;
    }
    
    function AddHelp()
    {
        $data = array(
            "category" => $this->category,
            "h_subject" => $this->h_subject,
            "h_contents" => $this->h_contents,
            "h_status" => 1, // Active
            "issue_time" => date("Y-m-d H:i:s")
        );
        
        try{
                $this->insert($data);
            }  catch (Zend_Exception $exp){
                var_dump($exp->getMessage());
            }
    }
    
    function EditHelp()
    {
        if($this->helpdesk_id)
        {
            $data = $this->fetchRow("helpdesk_id='".$this->helpdesk_id."'");
            
            $data->category = $this->category;
            $data->h_subject = $this->h_subject;
            $data->h_contents = $this->h_contents;
            
            $data->save();
        }
    }
    
    function GetHelpInfo()
    {
        if($this->helpdesk_id)
        {
            $data = $this->fetchRow("helpdesk_id='".$this->helpdesk_id."'");
            $result = $data->toArray();
            
            return $result;
        }
    }
    
    function UpdateStatus()
    {
        if($this->helpdesk_id)
        {
            $data = $this->fetchRow("helpdesk_id='".$this->helpdesk_id."'");
            
            if($data['h_status'])
            {
                $data->h_status = 0;
            }else{
                $data->h_status = 1;
            }
            
            $data->save();
        }
    }
}