<?php

class TrainingTrainee extends Zend_Db_Table
{
	protected $_name = 'training_trainee';
	
    function GetTrainee($arrangement_id)
    {
        $data = $this->select();
        $data->where("arrangement_id = ?", $arrangement_id);
        $d = $this->fetchAll($data);
        
        $result = array();
        
        if($d)
        {
            foreach($d as $d_val)
            {
                $result[] = $d_val['user_id'];
            }
        }
        
        return $result;
    }
    
    function WithMe($arrangement_id, $user_id)
    {
        $data = $this->GetTrainee($arrangement_id);
        
        if(in_array($user_id, $data))
        {
            $result = 1;
        }else
        {
            $result = 0;
        }
        
        return $result;
    }
}



