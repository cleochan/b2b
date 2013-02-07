<?php

class Grp extends Zend_Db_Table
{
	protected $_name = 'grp';
	
	function MakeList($for_ticket=NULL)
	{
		$grp = $this->select();
		$grp -> order('gname ASC');
		$grp_array = $this->fetchAll($grp);
		
		$pool = array();
		
		$users = new Users();
		
		if(!empty($grp_array))
		{
			foreach($grp_array as $val)
			{
				$grp = array();
				
				$grp['id'] = $val['id'];
				$grp['gname'] = $val['gname'];
				if(!$for_ticket)
				{
					$grp['members'] = $users -> GetNameString($val['members'], TRUE);
				}else
				{
					$grp['members'] = $users -> GetNameString($val['members']);
				}
				
				$pool[] = $grp;
			}
		}

		return $pool;
	}
}