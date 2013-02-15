<?php

class Databases_Tables_Logs extends Zend_Db_Table
{
    protected $_name = 'logs';
    
    function AddLog($contents)
    {
        if($contents)
        {
            $data = array(
                'contents' => $contents
            );
            
            try{
                $log_id = $this->insert($data);
            }  catch (Zend_Exception $exp){
                var_dump($exp->getMessage());
            }
        }
        
        return $log_id;
    }
}