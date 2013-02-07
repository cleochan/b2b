<?php

class TrainingLanguage extends Zend_Db_Table
{
	protected $_name = 'training_language';
	
    function GetLanguage($val=NULL)
    {
        $data = $this->select();
        $data->order("lang ASC");
        $d = $this->fetchAll($data);
        
        $pre = array();
        
        if($d)
        {
            foreach($d as $dv)
            {
                $pre[$dv['id']] = $dv['lang'];
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



