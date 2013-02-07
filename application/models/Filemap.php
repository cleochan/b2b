<?php

class Filemap extends Zend_Db_Table
{
	protected $_name = 'filemap';
    
    var $domain = "http://it-ticket.motionglobal.com/";
	
	function AddToDb($origine, $indb, $ftype)
	{
		$data = array(
						"origine" => $origine,			
						"indb" => $indb,
						"ftype" => $ftype
					);
		
		$this->insert($data);
	}
	
	function MakeDownloadLink($string, $type=NULL, $module=NULL, $handle=NULL) //type=1, can delete. module=1 training_library, handle=library_id
	{
		$result = "";
		
		$att_array = explode("|", $string);
			
		foreach($att_array as $att_val)
		{
			if(strpos($att_val, "/"))
			{
				$bs = "/";  //linux
				$att_val_array = explode($bs, $att_val);
				//$att_val_array[0] path without /
				//$att_val_array[1] file name indb
				$file_name = $this -> fetchRow('indb = "'.$att_val.'"');
			}else
			{
				//compatibility for windows slash
				$bs = "\\";  //windows
				$att_val_array = explode($bs, $att_val);
				//$att_val_array[0] path without /
				//$att_val_array[1] file name indb
				$special_string = ereg_replace('\\\\','%',$att_val);
				$file_name = $this -> fetchRow('indb like "'.$special_string.'"');
			}
			
			if($file_name['origine'])
			{
				if(1 == $type && 1 == $module && $handle)
                {
                    $piece[] = "<a href='".$this->domain."index/call-file/val/".$att_val_array[1]."' target='callFile'>".$file_name['origine']."</a>&nbsp;&nbsp;&nbsp;<a href='/training/delatt/file/".$att_val_array[1]."/lid/".$handle."'><img src='/images/del.png' boder='0' title='Delete' alt='Delete' /></a>";
                }else
                {
                    $piece[] = "<a href='".$this->domain."index/call-file/val/".$att_val_array[1]."' target='callFile'>".$file_name['origine']."</a>";
                }
                
			}
		}
		
		if(!empty($piece))
		{
			$result .= implode("<br />", $piece);
		}
		
		return $result;
	}
    
    function DelAttachment($key, $string)
    {
        if($key && $string)
        {
            $get_array = explode("|", $string);
            
            foreach($get_array as $array_key => $array_val)
            {
                $va = explode("/", $array_val);
                
                if($va[1] == $key)
                {
                    unset($get_array[$array_key]);
                }
            }
            
            $result = implode("|", $get_array);
        
            return $result;
        }
    }
}
