<?php

class Tables_Users extends Zend_Db_Table
{
	protected $_name = 'users';

	function DumpData()
        {
            $data = $this->fetchAll("user_id > 0");
            print_r($data);
            die;
        }
}



