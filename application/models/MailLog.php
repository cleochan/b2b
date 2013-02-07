<?php

class MailLog extends Zend_Db_Table
{
	protected $_name = 'mail_log';
	
	function Add($log)
	{
		$a['ctime'] = date("Y-m-d H:i:s");
		$a['log'] = $log;
		
		try {
				$this->insert($a);
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		
	}
}













