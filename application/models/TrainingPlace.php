<?php

class TrainingPlace extends Zend_Db_Table
{
	protected $_name = 'training_place';
	
    function GetPlace($val=NULL)
    {
        $data = $this->select();
        $data->order("pname ASC");
        $d = $this->fetchAll($data);
        
        $pre = array();
        
        if($d)
        {
            foreach($d as $dv)
            {
                $pre[$dv['id']] = $dv['pname'];
            }
        }
        
        if($val)
        {
            $result = $pre[$val];
        }else{
            $result = $pre;
        }
        
        return $result;
    }
}



