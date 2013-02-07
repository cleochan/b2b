<?php

class Training
{
    var $request;
	var $keyword;
	var $lm; //limit
	var $page=1;
    
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
	function ArrangementList()
    {
        $select = $this->db->select();
        $select->from("training_arrangement as a", array("id as aid", "adate", "aopen", "tips as atips", "status as astatus"));
        $select->joinLeft("training_language as l", "l.id=a.language", array("lang as llang"));
        $select->joinLeft("training_place as p", "p.id=a.aplace", array("pname"));
        $select->joinLeft("training_library as b", "b.id=a.library_id", array("title as btitle", "category as bcat"));
        $select->where("a.status in (?)", array(1,2));
        
        //Step4: order
		$select->order("a.adate DESC");
        
        //Step5: limit and offset
		$this->lm = 20;
		$offset = ($this->page - 1) * $this->lm;
		
		$select->limit($this->lm, $offset);
		
		//Fetch
        $data = $this->db->fetchAll($select);
        
        $training_category = new TrainingCategory();
        $category_array = $training_category->GetCategory();
        $training_trainer = new TrainingTrainer();
        $training_trainee = new TrainingTrainee();
        $users = new Users();
        
        $result = array();
                
        foreach($data as $d_key => $d_val)
        {
            $temp = array();
            $trainger_string = $users->MakeString($training_trainer->GetTrainer($d_val['aid']));
            $temp['agm_id'] = $d_val['aid'];
            $temp['agm_title'] = $d_val['btitle'];
            $temp['agm_time'] = date("Y-m-d H:i:s (l)", mktime(substr($d_val['adate'], 11, 2), substr($d_val['adate'], 14, 2), substr($d_val['adate'], 17, 2), substr($d_val['adate'], 5, 2), substr($d_val['adate'], 8, 2), substr($d_val['adate'], 0, 4)));
            $temp['agm_trainer'] = $users->GetNameString($trainger_string, TRUE);
            if($training_trainee->WithMe($d_val['aid'], $_SESSION["Zend_Auth"]["storage"]->id))
            {
                $temp['agm_me'] = "Y";
            }else{
                $temp['agm_me'] = "";
            }
            $temp['agm_place'] = $d_val['pname'];
            $temp['agm_category'] = $category_array[$d_val['bcat']];
            if($d_val['aopen'])
            {
                $temp['agm_open'] = "Y";
            }else{
                $temp['agm_open'] = "";
            }
            $temp['agm_lang'] = $d_val['llang'];
            $temp['high_auth'] = $this->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $trainger_string);
            
            if(mktime(substr($d_val['adate'], 11, 2),substr($d_val['adate'], 14, 2),substr($d_val['adate'], 17, 2),substr($d_val['adate'], 5, 2),substr($d_val['adate'], 8, 2),substr($d_val['adate'], 0, 4)) < time())
            {
                $temp['expired'] = "#ccc";
            }else
            {
                $temp['expired'] = "#000";
            }
            if(1 == $d_val['astatus'])
            {
                $temp['agm_status'] = "Available";
            }elseif(2 == $d_val['astatus'])
            {
                $temp['agm_status'] = "Closed";
            }
            
            
            $result[] = $temp;
        }
        
        return $result;
    }
    
	function LibraryList($status)
    {
        $select = $this->db->select();
        $select->from("training_library as l", array("id as lid", "title as ltitle", "author as lauthor", "updated_date as ludate"));
        $select->joinLeft("training_category as c", "c.id=l.category", "cname");
        $select->where("l.status = ?", $status);
        
        //Step4: order
		$select->order("c.cname ASC");
        $select->order("l.title ASC");
        
        //Step5: limit and offset
		$this->lm = 20;
		$offset = ($this->page - 1) * $this->lm;
		
		$select->limit($this->lm, $offset);
		
		//Fetch
        $data = $this->db->fetchAll($select);
        
        $training_category = new TrainingCategory();
        $training_language = new TrainingLanguage();
        $training_trainer = new TrainingTrainer();
        $training_trainee = new TrainingTrainee();
        $users = new Users();
        
        
        
        $result = array();
                
        foreach($data as $d_key => $d_val)
        {
            $temp = array();
            
            $temp['lib_id'] = $d_val['lid'];
            $temp['lib_category'] = $d_val['cname'];
            $temp['lib_title'] = $d_val['ltitle'];
            $temp['lib_author'] = $users->GetNameString($d_val['lauthor'], TRUE);
            $temp['lib_udate'] = $d_val['ludate'];
            $temp['high_auth'] = $this->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $d_val['lauthor']);
            
            $result[] = $temp;
        }
        
        return $result;
    }
    
    function HighAuth($user_id, $author)
    {
        $result = 0;
        
        if($user_id && $author)
        {
            $at = explode("|", $author);
            foreach($at as $at_val)
            {
                $name = explode("@", $at_val);
                if($user_id == $name[0])
                {
                    $result = 1;
                }
            }
        }
        
        return $result;
    }
    
    function GenerateTrainee()
    {
        $select = $this->db->select();
        $select->from("users as u", array("id as uid", "realname as uname"));
        $select->joinLeft("departments as d", "d.id=u.department", array("id as did", "name as dname"));
        $select->where("u.status = ?", 1);
        $select->order("d.name ASC");
        $select->order("u.realname ASC");
        $data = $this->db->fetchAll($select);
        
        $dept = array();
        
        foreach($data as $data_val)
        {
            if(!$dept[$data_val['did']])
            {
                $dept[$data_val['did']] = array("dept" => $data_val['dname'],
                                                "members" => array($data_val['uid'] => $data_val['uname'])
                                                );
            }else{
                $dept[$data_val['did']]['members'][$data_val['uid']] = $data_val['uname'];
            }
        }
        
        return $dept;
    }
    
    function ScoreArray()
    {
        $score = array(
            "-1" => "No Score - For the audience",
              0  => "0 - Absent from training",
              1  => 1,
              2  => 2,
              3  => 3,
              4  => 4,
              5  => 5,
              6  => 6,
              7  => 7,
              8  => 8,
              9  => 9,
             10  => "10 - Perfect!"
        );
        
        return $score;
    }
    
    function GetScore($val=NULL)
    {
        $score = array(
            "-1" => "No Score - For the audience",
              0  => "0 - Absent from training",
              1  => 1,
              2  => 2,
              3  => 3,
              4  => 4,
              5  => 5,
              6  => 6,
              7  => 7,
              8  => 8,
              9  => 9,
             10  => "10 - Perfect!"
        );
        
        if(strval($val))
        {
            $result = $score[$val];
        }else{
            $result = $score['-1'];
        }
        
        return $result;
    }
    
    function HistoryArray($user_id)
    {
        $data = $this->db->select();
        $data -> from("training_trainee as e", array("score as escore"));
        $data -> joinLeft("training_arrangement as a", "a.id=e.arrangement_id", array("adate"));
        $data -> joinLeft("training_library as l", "a.library_id=l.id", array("title as ltitle", "category as lcategory"));
        $data -> where("e.user_id = ?", $user_id);
        $data -> order("a.adate DESC");
        $data_array = $this->db->fetchAll($data);
        
        $training_category = new TrainingCategory();
        $category = $training_category -> GetCategory();
        
        if(!empty($data_array))
        {
            foreach($data_array as $d_key=>$d_val)
            {
                $data_array[$d_key]['escore'] = $this->GetScore($d_val['escore']);
                $data_array[$d_key]['adate'] = date("Y-m-d H:i:s (l)", mktime(substr($d_val['adate'], 11, 2), substr($d_val['adate'], 14, 2), substr($d_val['adate'], 17, 2), substr($d_val['adate'], 5, 2), substr($d_val['adate'], 8, 2), substr($d_val['adate'], 0, 4)));
                $data_array[$d_key]['lcategory'] = $category[$d_val['lcategory']];
            }
        }
        
        return $data_array;
        
    }
}



