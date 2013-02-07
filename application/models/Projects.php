<?php

class Projects extends Zend_Db_Table
{
	protected $_name = 'projects';
	
	function MakeList()
	{
		$projects = $this->select();
        $projects -> where('status = ?', 1);
		$projects -> order('create_time DESC');
		$projects_array = $this->fetchAll($projects);
        
        $users = new Users();
        
        $pool_a = array();
        $pool_i = array();
		
        if($projects_array)
        {
            foreach($projects_array as $val)
            {
                $project = array();

                $project['id'] = $val['id'];
                $project['project_name'] = $val['project_name'];
                $project['status'] = "<font color='green'>Active</font>";
                $project['creator'] = $users->GetRealName($val['creator']);
                $project['create_time'] = $val['create_time'];

                $pool_a[] = $project;
            }
        }
        
        $three_month_ago = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m")-3, date("d"),   date("Y")));

        $projects = $this->select();
        $projects -> where('status = ?', 0);
        $projects -> where('create_time > ?', $three_month_ago);
		$projects -> order('create_time DESC');
		$projects_array = $this->fetchAll($projects);
		
        if($projects_array)
        {
            foreach($projects_array as $val)
            {
                $project = array();

                $project['id'] = $val['id'];
                $project['project_name'] = $val['project_name'];
                $project['status'] = "<font color='red'>Inactive</font>";
                $project['creator'] = $users->GetRealName($val['creator']);
                $project['create_time'] = $val['create_time'];

                $pool_i[] = $project;
            }
        }
        
        $pool = array_merge($pool_a, $pool_i);
		
		return $pool;
	}
    
    function GetArray()
	{
		$pj = $this->select();
        $pj -> where('status = ?', 1);
		$pj -> order('project_name ASC');
		$pj_array = $this -> fetchAll($pj);
		
		foreach($pj_array as $val)
		{
			$id = $val['id'];
			$result[$id] = $val['project_name'];
		}

		return $result;
	}
	
	function GetVal($id)
	{
		$name = $this->fetchRow('id = "'.$id.'"');
		
		return $name['project_name'];
	}
	
}











