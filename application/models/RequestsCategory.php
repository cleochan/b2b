<?php

class RequestsCategory extends Zend_Db_Table
{
	protected $_name = 'requests_category';
    
    function BuildTree($id=Null)
    {
        $id_array = array();
        
        if($id)
        {
            $id_array[] = $id;
        }else // from root
        {
            $top = $this->select();
            $top -> where('parent_id = "" or parent_id = 0 or parent_id is null');
            $top -> where('status = ?', 1);
            $top -> order('cname ASC');
            $top_data = $this->fetchAll($top);
            
            foreach($top_data as $td)
            {
                $id_array[] = $td['id'];
            }
        }
        
        foreach($id_array as $iay)
        {
            $current_one = $this->GetOne($iay);
            $tree[] = array($iay => $current_one['cname']);            

            //max 10 level loop

            $select = $this->select();
            $select -> where('parent_id = ?', $iay);
            $select -> where('status = ?', 1);
            $select -> order('cname ASC');
            $data = $this->fetchAll($select);   

            if(!empty($data))
            {
                foreach($data as $d)
                {
                    $tree[] = array($d['id'] => "- ".$d['cname']);

                    $select2 = $this->select();
                    $select2 -> where('parent_id = ?', $d['id']);
                    $select2 -> where('status = ?', 1);
                    $select2 -> order('cname ASC');
                    $data2 = $this->fetchAll($select2);   

                    if(!empty($data2))
                    {
                        foreach($data2 as $d2)
                        {
                            $tree[] = array($d2['id'] => "- - ".$d2['cname']);

                            $select3 = $this->select();
                            $select3 -> where('parent_id = ?', $d2['id']);
                            $select3 -> where('status = ?', 1);
                            $select3 -> order('cname ASC');
                            $data3 = $this->fetchAll($select3);   

                            if(!empty($data3))
                            {
                                foreach($data3 as $d3)
                                {
                                    $tree[] = array($d3['id'] => "- - - ".$d3['cname']);

                                    $select4 = $this->select();
                                    $select4 -> where('parent_id = ?', $d3['id']);
                                    $select4 -> where('status = ?', 1);
                                    $select4 -> order('cname ASC');
                                    $data4 = $this->fetchAll($select4);   

                                    if(!empty($data4))
                                    {
                                        foreach($data4 as $d4)
                                        {
                                            $tree[] = array($d4['id'] => "- - - - ".$d4['cname']);

                                            $select5 = $this->select();
                                            $select5 -> where('parent_id = ?', $d4['id']);
                                            $select5 -> where('status = ?', 1);
                                            $select5 -> order('cname ASC');
                                            $data5 = $this->fetchAll($select5);   

                                            if(!empty($data5))
                                            {
                                                foreach($data5 as $d5)
                                                {
                                                    $tree[] = array($d5['id'] => "- - - - - ".$d5['cname']);

                                                    $select6 = $this->select();
                                                    $select6 -> where('parent_id = ?', $d5['id']);
                                                    $select6 -> where('status = ?', 1);
                                                    $select6 -> order('cname ASC');
                                                    $data6 = $this->fetchAll($select6);   

                                                    if(!empty($data6))
                                                    {
                                                        foreach($data6 as $d6)
                                                        {
                                                            $tree[] = array($d6['id'] => "- - - - - - ".$d6['cname']);

                                                            $select7 = $this->select();
                                                            $select7 -> where('parent_id = ?', $d6['id']);
                                                            $select7 -> where('status = ?', 1);
                                                            $select7 -> order('cname ASC');
                                                            $data7 = $this->fetchAll($select7);   

                                                            if(!empty($data7))
                                                            {
                                                                foreach($data7 as $d7)
                                                                {
                                                                    $tree[] = array($d7['id'] => "- - - - - - - ".$d7['cname']);

                                                                    $select8 = $this->select();
                                                                    $select8 -> where('parent_id = ?', $d7['id']);
                                                                    $select8 -> where('status = ?', 1);
                                                                    $select8 -> order('cname ASC');
                                                                    $data8 = $this->fetchAll($select8);   

                                                                    if(!empty($data8))
                                                                    {
                                                                        foreach($data8 as $d8)
                                                                        {
                                                                            $tree[] = array($d8['id'] => "- - - - - - - - ".$d8['cname']);

                                                                            $select9 = $this->select();
                                                                            $select9 -> where('parent_id = ?', $d8['id']);
                                                                            $select9 -> where('status = ?', 1);
                                                                            $select9 -> order('cname ASC');
                                                                            $data9 = $this->fetchAll($select9);   

                                                                            if(!empty($data9))
                                                                            {
                                                                                foreach($data9 as $d9)
                                                                                {
                                                                                    $tree[] = array($d9['id'] => "- - - - - - - - - ".$d9['cname']);

                                                                                    $select10 = $this->select();
                                                                                    $select10 -> where('parent_id = ?', $d9['id']);
                                                                                    $select10 -> where('status = ?', 1);
                                                                                    $select10 -> order('cname ASC');
                                                                                    $data10 = $this->fetchAll($select10);   

                                                                                    if(!empty($data10))
                                                                                    {
                                                                                        foreach($data10 as $d10)
                                                                                        {
                                                                                            $tree[] = array($d10['id'] => "- - - - - - - - - - ".$d10['cname']);
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        

        
        return $tree;
            
    }
	
	function GetVal($id)
	{
		$name = $this->fetchRow('id = "'.$id.'"');
		
		return $name['cname'];
	}
    
    function GetOne($id)
    {
        $select = $this->select();
        $select -> where('id = ?', $id);
        $data = $this->fetchRow($select);
        
        if(empty($data))
        {
            $data['id'] = 0;
            $data['cname'] = "Request Category";
        }
        
        return $data;
    }
	
}











