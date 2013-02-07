<?php

class Departments extends Zend_Db_Table
{
	protected $_name = 'departments';
	
	function GetArray()
	{
		$dept = $this->select();
		$dept -> order('name ASC');
		$dept_array = $this -> fetchAll($dept);
		
		foreach($dept_array as $val)
		{
			$id = $val['id'];
			$result[$id] = $val['name'];
		}

		return $result;
	}
	
	function GetDeptVal($val, $type) //$type 1 get name, 2 get id
	{
		if(1 == $type)
		{
			$dept = $this->fetchRow('id = "'.$val.'"');
			$result = $dept['name'];
		}elseif(2 == $type)
		{
			$dept = $this->fetchRow('name = "'.$val.'"');
			$result = $dept['id'];
		}
		
		return $result;
	}
	
}











