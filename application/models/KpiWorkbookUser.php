<?php

class KpiWorkbookUser extends Zend_Db_Table
{
	protected $_name = 'kpi_workbook_user';
    
    function GetUser($work_id, $with_name=NULL)
    {
        if($work_id)
        {
            $select = $this->select();
            $select -> where("workbook_id = ?", $work_id);
            $data = $this->fetchAll($select);
        }
        
        if(!empty($data) && $with_name)
        {
            $user_id_array = array();
            
            foreach($data as $d)
            {
                $user_id_array[] = $d['user_id'];
            }
            
            $users = new Users();
            $result = $users->GetNameString($users->MakeString($user_id_array), TRUE);
        }else{
            $result = $data;
        }

        return $result;
    }
    
    function GetWork($user_id)
    {
        if($user_id)
        {
            $select = $this->select();
            $select -> where("user_id = ?", $user_id);
            $data = $this->fetchAll($select);
            
            $user_array = array();
            
            if(!empty($data))
            {
                foreach($data as $d)
                {
                    $user_array[] = $d['workbook_id'];
                }
            }
        }

        return $user_array;
    }
}