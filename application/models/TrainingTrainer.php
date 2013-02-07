<?php

class TrainingTrainer extends Zend_Db_Table
{
	protected $_name = 'training_trainer';
	
    function GetTrainer($arrangement_id)
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
}



