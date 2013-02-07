<?php

class TrainingCategory extends Zend_Db_Table
{
	protected $_name = 'training_category';
	
    function GetCategory($val=NULL)
    {
        $data = $this->select();
        $data->order("cname ASC");
        $d = $this->fetchAll($data);
        
        $pre = array();
        
        if(!empty($d))
        {
            foreach($d as $dv)
            {
                $pre[$dv['id']] = $dv['cname'];
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



