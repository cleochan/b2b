<?php

class TrainingLibrary extends Zend_Db_Table
{
	protected $_name = 'training_library';
    
    function GetLibrary($val=NULL)
    {
        $training_category = new TrainingCategory();
        $category_list = $training_category ->GetCategory();
        
        $data = $this->select();
        $data->where("status in (?)", array(1,2));
        $data->order("category ASC");
        $data->order("title ASC");
        $d = $this->fetchAll($data);
        
        $pre = array();
        
        if($d)
        {
            foreach($d as $dv)
            {
                $pre[$dv['id']] = $category_list[$dv['category']]." - ".$dv['title'];
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



