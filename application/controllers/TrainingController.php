<?php

class TrainingController extends Zend_Controller_Action
{
	
	function init(){
		$this->db = Zend_Registry::get("db");
		
	}
	
	function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		$users = new Users();
		if(!$auth->hasIdentity() || !$users->IsValid())
		{
			$this->_redirect('/login/logout');
		}
		
		//get system title
		$get_title = new Params();
		$this->view->system_title = $get_title -> GetVal("system_title");
		$this->view->system_version = $get_title -> GetVal("system_version");

		//make top menu
		$menu = new Menu();
		$this->view->top_menu = $menu -> GetTopMenu($this->getRequest()->getControllerName());
	}
	
    function indexAction()
    {
        $this->view->title = "Training Calendar";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
		
		$training = new Training();
		$arr_list = $training -> ArrangementList();
		
		$this->view->list = $arr_list;
        
    }
    
    function addAction()
    {
        $this->view->title = "Create Calendar";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //@@@form data preparation start
        
        //library list
        $library_model = new TrainingLibrary();
        $this->view->library_list = $library_model -> GetLibrary();
        
        //date and time
        $this_year = date("Y");
        for($a=$this_year;$a<($this_year+2);$a++)
        {
            $year_arrry[] = $a;
        }
        $this->view->year_array = $year_arrry;
        
        for($b=1;$b<13;$b++)
        {
            $month_arrry[] = $b;
        }
        $this->view->month_array = $month_arrry;
        
        for($c=1;$c<32;$c++)
        {
            $day_arrry[] = $c;
        }
        $this->view->day_array = $day_arrry;
        
        for($d=0;$d<24;$d++)
        {
            if($d < 10)
            {
                $d = "0".$d;
            }
            $hour_arrry[] = $d;
        }
        $this->view->hour_array = $hour_arrry;
        
        $this->view->min_array = array("00", "15", "30", "45");
        
        //place list
        $place_model = new TrainingPlace();
        $this->view->place_list = $place_model->GetPlace();
        
        //language list
        $lang_model = new TrainingLanguage();
        $this->view->lang_list = $lang_model->GetLanguage();
        
        //trainee list
        $training_model = new Training();
        $this->view->trainee_list = $training_model -> GenerateTrainee();

        //create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
        
        $this->view->initial_trainer = $users->GetNameString($_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);
        
        //@@@form data preparation finished
    }
    
    function addSubmitAction()
    {
        $params = $this->_request->getParams();
        
        if(!$params['ttitle'])
        {
            $error = 1;
        }elseif(!$params['tplace'])
        {
            $error = 2;
        }elseif(!$params['tlang'])
        {
            $error = 3;
        }elseif(!$params['ttrainer'])
        {
            $error = 4;
        }
        
        if($error)
        {
            $this->view->title = "Create Calendar";
            $params = $this->_request->getParams();

            $menu = new Menu();
            $this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());

            //@@@form data preparation start

            //library list
            $library_model = new TrainingLibrary();
            $this->view->library_list = $library_model -> GetLibrary();

            //date and time
            $this_year = date("Y");
            for($a=$this_year;$a<($this_year+2);$a++)
            {
                $year_arrry[] = $a;
            }
            $this->view->year_array = $year_arrry;

            for($b=1;$b<13;$b++)
            {
                $month_arrry[] = $b;
            }
            $this->view->month_array = $month_arrry;

            for($c=1;$c<32;$c++)
            {
                $day_arrry[] = $c;
            }
            $this->view->day_array = $day_arrry;

            for($d=0;$d<24;$d++)
            {
                if($d < 10)
                {
                    $d = "0".$d;
                }
                $hour_arrry[] = $d;
            }
            $this->view->hour_array = $hour_arrry;

            $this->view->min_array = array("00", "15", "30", "45");

            //place list
            $place_model = new TrainingPlace();
            $this->view->place_list = $place_model->GetPlace();

            //language list
            $lang_model = new TrainingLanguage();
            $this->view->lang_list = $lang_model->GetLanguage();

            //trainee list
            $training_model = new Training();
            $this->view->trainee_list = $training_model -> GenerateTrainee();

            //create user list
            $users = new Users();
            $this->view->users_array = $users -> GetRealNameString();

            $this->view->initial_trainer = $users->GetNameString($_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);

            //@@@form data preparation finished
            
            switch ($error)
            {
                case 1:
                    $this->view->notice = "Title is required.";
                    break;
                case 2:
                    $this->view->notice = "Training place is required.";
                    break;
                case 3:
                    $this->view->notice = "Language is required.";
                    break;
                case 4:
                    $this->view->notice = "Trainer is required.";
                    break;
                default:
                    $this->view->notice = "Error: CODE 005";
                    break;
            }
            
            $this->view->original_ttitle = $params['ttitle'];
            $this->view->original_tyear = $params['tyear'];
            $this->view->original_tmonth = $params['tmonth'];
            $this->view->original_tday = $params['tday'];
            $this->view->original_thour = $params['thour'];
            $this->view->original_tmin = $params['tmin'];
            $this->view->original_tplace = $params['tplace'];
            $this->view->original_tlang = $params['tlang'];
            $this->view->original_topen = $params['topen'];
            $this->view->original_ttip = $params['ttip'];
            $this->view->original_ttrainer = $params['ttrainer'];
            
            $original_trainee = array();
            if($params['ttrainee'])
            {
                foreach($params['ttrainee'] as $ttrainee_key => $ttrainee_val)
                {
                    $original_trainee[] = $ttrainee_key;
                }
            }
            $this->view->original_ttrainee = $original_trainee;
            
            $this->render("add");
        }else
        {
            //action
            
            //preparation start
            //trainer
            $users = new Users();
            $trainer_string = $users -> MakeString($params['ttrainer'], 1);
            $trainer_id_array = $users ->GetUserIdArray($trainer_string);
            //trainee
            $trainee_id_array = array();
            if($params['ttrainee'])
            {
                foreach($params['ttrainee'] as $ttrainee_key => $ttrainee_val)
                {
                    $trainee_id_array[] = $ttrainee_key;
                }
            }
            //preparation finished
            
            $training_arrangement = new TrainingArrangement();
            $arr_row = $training_arrangement -> createRow();
            $arr_row->library_id = $params['ttitle'];
            $arr_row->adate = date("Y-m-d H:i:s", mktime(intval($params['thour']), intval($params['tmin']), 0, intval($params['tmonth']), intval($params['tday']), intval($params['tyear'])));
            $arr_row->aplace = $params['tplace'];
            $arr_row->aopen = $params['topen'];
            $arr_row->status = 1;
            $arr_row->tips = $params['ttip'];
            $arr_row->language = $params['tlang'];
            $arr_id = $arr_row->save();
            
            $training_trainer = new TrainingTrainer();
            if(!empty($trainer_id_array))
            {
                foreach($trainer_id_array as $tia)
                {
                    $er_row = $training_trainer ->createRow();
                    $er_row->arrangement_id = $arr_id;
                    $er_row->user_id = $tia;
                    $er_row->save();
                }
            }
            
            $training_trainee = new TrainingTrainee();
            if(!empty($trainee_id_array))
            {
                foreach($trainee_id_array as $tie)
                {
                    $ee_row = $training_trainee ->createRow();
                    $ee_row->arrangement_id = $arr_id;
                    $ee_row->user_id = $tie;
                    $ee_row->save();
                }
            }
                        
            //send email
            $training_place = new TrainingPlace();
            $place_array = $training_place->GetPlace();
            $training_language = new TrainingLanguage();
            $lang_array = $training_language->GetLanguage();
            $training_library = new TrainingLibrary();
            $library_array = $training_library->GetLibrary();
            
            $mail = new Mail();
            $mail->url = "training/view/id/".$arr_id;
            $mail->training_topic = $library_array[$params['ttitle']];
            $mail->training_time = date("Y-m-d H:i:s (l)", mktime(intval($params['thour']), intval($params['tmin']), 0, intval($params['tmonth']), intval($params['tday']), intval($params['tyear'])));
            $mail->training_place = $place_array[$params['tplace']];
            $mail->training_lang = $lang_array[$params['tlang']];
            if($params['topen'])
            {
                $mail->training_open = "Yes";
            }else{
                $mail->training_open = "No";
            }
            
            $mail->training_tips = $params['ttip'];
            $trainer_array = $training_trainer->GetTrainer($arr_id);
            $mail->training_trainer = $users->GetNameString($users->MakeString($trainer_array), TRUE);
            $trainee_array = $training_trainee->GetTrainee($arr_id);
            $mail->training_trainee = $users->GetNameString($users->MakeString($trainee_array), TRUE);
            $mail->mail_contents = $mail->ContentsTemplate(9);
            $mail->mail_subject = "Training Created: ".$library_array[$params['ttitle']];
              
            if(!empty($trainee_array))
            {
                $mail->to = $users -> MakeMailUserList($trainee_array);
            }
            
            if(!empty($trainer_array))
            {
                $mail->cc = $users -> MakeMailUserList($trainer_array);
            }
                
            //send
            $mail->Send();
            
            $this->_redirect("training/index");
        }
    }
    
    function editAction()
    {
        $this->view->title = "Edit Calendar";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //@@@form data preparation start
        
        //library list
        $library_model = new TrainingLibrary();
        $this->view->library_list = $library_model -> GetLibrary();
        
        //date and time
        $this_year = date("Y");
        for($a=$this_year;$a<($this_year+2);$a++)
        {
            $year_arrry[] = $a;
        }
        $this->view->year_array = $year_arrry;
        
        for($b=1;$b<13;$b++)
        {
            $month_arrry[] = $b;
        }
        $this->view->month_array = $month_arrry;
        
        for($c=1;$c<32;$c++)
        {
            $day_arrry[] = $c;
        }
        $this->view->day_array = $day_arrry;
        
        for($d=0;$d<24;$d++)
        {
            if($d < 10)
            {
                $d = "0".$d;
            }
            $hour_arrry[] = $d;
        }
        $this->view->hour_array = $hour_arrry;
        
        $this->view->min_array = array("00", "15", "30", "45");
        
        //place list
        $place_model = new TrainingPlace();
        $this->view->place_list = $place_model->GetPlace();
        
        //language list
        $lang_model = new TrainingLanguage();
        $this->view->lang_list = $lang_model->GetLanguage();
        
        //trainee list
        $training_model = new Training();
        $this->view->trainee_list = $training_model -> GenerateTrainee();

        //create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
        
        $this->view->initial_trainer = $users->GetNameString($_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);
        
        //@@@form data preparation finished
        
        //dump original data
        if($params['id'])
        {
            $this->view->arr_id = $params['id'];
            
            $training_arrangement = new TrainingArrangement();
            $arr_data = $training_arrangement -> fetchRow("id = '".$params['id']."'");
            
            //get auth
            $training_trainer = new TrainingTrainer();
            if(!$training_model->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $users->MakeString($training_trainer->GetTrainer($params['id']))))
            {
                echo "Invalid Operation.";die;
            }
            
            $this->view->original_ttitle = $arr_data['library_id'];
            $this->view->original_tyear = substr($arr_data['adate'], 0, 4);
            $this->view->original_tmonth = intval(substr($arr_data['adate'], 5, 2));
            $this->view->original_tday = intval(substr($arr_data['adate'], 8, 2));
            $this->view->original_thour = substr($arr_data['adate'], 11, 2);
            $this->view->original_tmin = substr($arr_data['adate'], 14, 2);
            $this->view->original_tplace = $arr_data['aplace'];
            $this->view->original_tlang = $arr_data['language'];
            $this->view->original_topen = $arr_data['aopen'];
            $this->view->original_ttip = $arr_data['tips'];
            $this->view->original_tstatus = $arr_data['status'];
            
            
            $this->view->original_ttrainer = $users->GetNameString($users->MakeString($training_trainer->GetTrainer($arr_data['id'])));
            $training_trainee = new TrainingTrainee();
            $this->view->original_ttrainee = $training_trainee->GetTrainee($arr_data['id']);
            
        }

    }
    
    function viewAction()
    {
        $this->view->title = "View Calendar";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //dump original data
        if($params['id'])
        {
            $users = new Users();
            $training_place = new TrainingPlace();
            $place_array = $training_place->GetPlace();
            $training_language = new TrainingLanguage();
            $lang_array = $training_language->GetLanguage();
            $training_library = new TrainingLibrary();
            $library_array = $training_library->GetLibrary();
            
            
            $training_arrangement = new TrainingArrangement();
            $arr_data = $training_arrangement -> fetchRow("id = '".$params['id']."'");
            
            if($arr_data['aopen'])
            {
                $this->view->original_topen = "Yes";
            }else{
                $this->view->original_topen = "No";
            }
           
            $this->view->original_ttitle = $library_array[$arr_data['library_id']];
            $this->view->original_lib_id = $arr_data['library_id'];
            $this->view->original_ttime = date("Y-m-d H:i:s (l)", mktime(substr($arr_data['adate'], 11, 2), substr($arr_data['adate'], 14, 2), substr($arr_data['adate'], 17, 2), substr($arr_data['adate'], 5, 2), substr($arr_data['adate'], 8, 2), substr($arr_data['adate'], 0, 4)));
            $this->view->original_tplace = $place_array[$arr_data['aplace']];
            $this->view->original_tlang = $lang_array[$arr_data['language']];
            $this->view->original_ttip = $arr_data['tips'];
            if(1 == $arr_data['status'])
            {
                $this->view->original_tstatus = "Available";
            }elseif(2 == $arr_data['status'])
            {
                $this->view->original_tstatus = "Closed";
            }
            
            
            $training_trainer = new TrainingTrainer();
            $this->view->original_ttrainer = $users->GetNameString($users->MakeString($training_trainer->GetTrainer($arr_data['id'])), TRUE);
            $training_trainee = new TrainingTrainee();
            $this->view->original_ttrainee = $users->GetNameString($users->MakeString($training_trainee->GetTrainee($arr_data['id'])), TRUE);
            
        }

    }
    
    function editSubmitAction()
    {
        $params = $this->_request->getParams();
        
        if(!$params['ttitle'])
        {
            $error = 1;
        }elseif(!$params['tplace'])
        {
            $error = 2;
        }elseif(!$params['tlang'])
        {
            $error = 3;
        }elseif(!$params['ttrainer'])
        {
            $error = 4;
        }
        
        if($error)
        {
            $this->view->title = "Edit Calendar";
            $params = $this->_request->getParams();

            $menu = new Menu();
            $this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());

            //@@@form data preparation start

            //library list
            $library_model = new TrainingLibrary();
            $this->view->library_list = $library_model -> GetLibrary();

            //date and time
            $this_year = date("Y");
            for($a=$this_year;$a<($this_year+2);$a++)
            {
                $year_arrry[] = $a;
            }
            $this->view->year_array = $year_arrry;

            for($b=1;$b<13;$b++)
            {
                $month_arrry[] = $b;
            }
            $this->view->month_array = $month_arrry;

            for($c=1;$c<32;$c++)
            {
                $day_arrry[] = $c;
            }
            $this->view->day_array = $day_arrry;

            for($d=0;$d<24;$d++)
            {
                if($d < 10)
                {
                    $d = "0".$d;
                }
                $hour_arrry[] = $d;
            }
            $this->view->hour_array = $hour_arrry;

            $this->view->min_array = array("00", "15", "30", "45");

            //place list
            $place_model = new TrainingPlace();
            $this->view->place_list = $place_model->GetPlace();

            //language list
            $lang_model = new TrainingLanguage();
            $this->view->lang_list = $lang_model->GetLanguage();

            //trainee list
            $training_model = new Training();
            $this->view->trainee_list = $training_model -> GenerateTrainee();

            //create user list
            $users = new Users();
            $this->view->users_array = $users -> GetRealNameString();

            $this->view->initial_trainer = $users->GetNameString($_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);

            //@@@form data preparation finished
            
            switch ($error)
            {
                case 1:
                    $this->view->notice = "Title is required.";
                    break;
                case 2:
                    $this->view->notice = "Training place is required.";
                    break;
                case 3:
                    $this->view->notice = "Language is required.";
                    break;
                case 4:
                    $this->view->notice = "Trainer is required.";
                    break;
                default:
                    $this->view->notice = "Error: CODE 005";
                    break;
            }
            
            $this->view->original_ttitle = $params['ttitle'];
            $this->view->original_tyear = $params['tyear'];
            $this->view->original_tmonth = $params['tmonth'];
            $this->view->original_tday = $params['tday'];
            $this->view->original_thour = $params['thour'];
            $this->view->original_tmin = $params['tmin'];
            $this->view->original_tplace = $params['tplace'];
            $this->view->original_tlang = $params['tlang'];
            $this->view->original_topen = $params['topen'];
            $this->view->original_ttip = $params['ttip'];
            $this->view->original_ttrainer = $params['ttrainer'];
            $this->view->original_tstatus = $params['tstatus'];
            
            $original_trainee = array();
            if($params['ttrainee'])
            {
                foreach($params['ttrainee'] as $ttrainee_key => $ttrainee_val)
                {
                    $original_trainee[] = $ttrainee_key;
                }
            }
            $this->view->original_ttrainee = $original_trainee;
            
            $this->view->arr_id = $params['arr_id'];
            
            $this->render("edit");
        }else
        {
            //action
            
            //preparation start
            //trainer
            $users = new Users();
            $trainer_string = $users -> MakeString($params['ttrainer'], 1);
            $trainer_id_array = $users ->GetUserIdArray($trainer_string);
            //trainee
            $trainee_id_array = array();
            if($params['ttrainee'])
            {
                foreach($params['ttrainee'] as $ttrainee_key => $ttrainee_val)
                {
                    $trainee_id_array[] = $ttrainee_key;
                }
            }
            //preparation finished
            
            $training_arrangement = new TrainingArrangement();
            $arr_row = $training_arrangement -> fetchRow("id = '".$params['arr_id']."'");
            $arr_row->library_id = $params['ttitle'];
            $arr_row->adate = date("Y-m-d H:i:s", mktime(intval($params['thour']), intval($params['tmin']), 0, intval($params['tmonth']), intval($params['tday']), intval($params['tyear'])));
            $arr_row->aplace = $params['tplace'];
            $arr_row->aopen = $params['topen'];
            $arr_row->status = $params['tstatus'];
            $arr_row->tips = $params['ttip'];
            $arr_row->language = $params['tlang'];
            $arr_row->save();
            
            $training_trainer = new TrainingTrainer();
            
            $old_trainer_array = $training_trainer -> GetTrainer($params['arr_id']);
            // old array   $old_trainer_array
            // new array   $trainer_id_array
            
            //delete trainer
            $need_del = array_diff($old_trainer_array, $trainer_id_array);
            if(!empty($need_del))
            {
                foreach($need_del as $t1)
                {
                    $er_row = $training_trainer -> delete("arrangement_id='".$params['arr_id']."' and user_id='".$t1."'");
                }
            }
            
            //add trainer
            $need_add = array_diff($trainer_id_array, $old_trainer_array);
            if(!empty($need_add))
            {
                foreach($need_add as $t2)
                {
                    $er_row = $training_trainer ->createRow();
                    $er_row->arrangement_id = $params['arr_id'];
                    $er_row->user_id = $t2;
                    $er_row->save();
                }
            }
            
            $training_trainee = new TrainingTrainee();
            
            $old_trainee_array = $training_trainee -> GetTrainee($params['arr_id']);
            // old array   $old_trainee_array
            // new array   $trainee_id_array
            
            //delete trainer
            $need_del2 = array_diff($old_trainee_array, $trainee_id_array);
            if(!empty($need_del2))
            {
                foreach($need_del2 as $t3)
                {
                    $er_row = $training_trainee -> delete("arrangement_id='".$params['arr_id']."' and user_id='".$t3."'");
                }
            }
            
            //add trainer
            $need_add2 = array_diff($trainee_id_array, $old_trainee_array);
            if(!empty($need_add2))
            {
                foreach($need_add2 as $t4)
                {
                    $er_row = $training_trainee ->createRow();
                    $er_row->arrangement_id = $params['arr_id'];
                    $er_row->user_id = $t4;
                    $er_row->save();
                }
            }

            //send email
            $training_place = new TrainingPlace();
            $place_array = $training_place->GetPlace();
            $training_language = new TrainingLanguage();
            $lang_array = $training_language->GetLanguage();
            $training_library = new TrainingLibrary();
            $library_array = $training_library->GetLibrary();
            
            $mail = new Mail();
            $mail->url = "training/view/id/".$params['arr_id'];
            if(1 == $params['tstatus'])
            {
                $mail->training_status = "Available";
            }elseif(2 == $params['tstatus'])
            {
                $mail->training_status = "Closed";
            }
            
            $mail->training_topic = $library_array[$params['ttitle']];
            $mail->training_time = date("Y-m-d H:i:s (l)", mktime(intval($params['thour']), intval($params['tmin']), 0, intval($params['tmonth']), intval($params['tday']), intval($params['tyear'])));
            $mail->training_place = $place_array[$params['tplace']];
            $mail->training_lang = $lang_array[$params['tlang']];
            if($params['topen'])
            {
                $mail->training_open = "Yes";
            }else{
                $mail->training_open = "No";
            }
            
            $mail->training_tips = $params['ttip'];
            $trainer_array = $training_trainer->GetTrainer($params['arr_id']);
            $mail->training_trainer = $users->GetNameString($users->MakeString($trainer_array), TRUE);
            $trainee_array = $training_trainee->GetTrainee($params['arr_id']);
            $mail->training_trainee = $users->GetNameString($users->MakeString($trainee_array), TRUE);
            $mail->mail_contents = $mail->ContentsTemplate(10);
            $mail->mail_subject = "Training Updated: ".$library_array[$params['ttitle']];
              
            if(!empty($trainee_array))
            {
                $mail->to = $users -> MakeMailUserList($trainee_array);
            }
            
            if(!empty($trainer_array))
            {
                $mail->cc = $users -> MakeMailUserList($trainer_array);
            }
                
            //send
            $mail->Send();
            
            $this->_redirect("training/index");
        }
    }
    
    function scoreAction()
    {
        $this->view->title = "Add Score";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //dump original data
        if($params['id'])
        {
            $this->view->arr_id = $params['id'];
            
            //get auth
            $training_model = new Training();
            $users = new Users();
            $training_trainer = new TrainingTrainer();
            if(!$training_model->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $users->MakeString($training_trainer->GetTrainer($params['id']))))
            {
                echo "Invalid Operation.";die;
            }
            
            $training_trainee = new TrainingTrainee();
            $trainee_list = $training_trainee -> fetchAll("arrangement_id = '".$params['id']."'");
            $trainee_list = $trainee_list->toArray();
            
            if(!empty($trainee_list))
            {
                foreach ($trainee_list as $trainee_key => $trainee_val)
                {
                    $trainee_list[$trainee_key]['user_name'] = $users->GetRealName($trainee_val['user_id']);
                }
            }
            
            $this->view->list = $trainee_list;
            
            $t_name = $this->db->select();
            $t_name->from("training_arrangement as a", "adate");
            $t_name->joinLeft("training_library as l", "a.library_Id=l.id", "title as ltitle");
            $t_name->where("a.id = ?", $params['id']);
            $t_name_val = $this->db->fetchRow($t_name);
            $this->view->t_name = $t_name_val;
            
            $this->view->score_array = $training_model -> ScoreArray();
            
            
        }else{
            echo "Invalid Operation.";die;
        }

    }
    
    function scoreSubmitAction()
    {
        $params = $this->_request->getParams();
        
        if($params['arr_id'])
        {
            if(!empty($params['score']))
            {
                $training_trainee = new TrainingTrainee();
                
                foreach($params['score'] as $score_key => $score_val)
                {
                    if("-1" == $score_val)
                    {
                        $score_val = NULL;
                    }
                    
                    $row = $training_trainee -> fetchRow("id = '".$score_key."'");
                    $row -> score = $score_val;
                    $row -> save();
                }
            }
            
            $this->_redirect("/training/score/id/".$params['arr_id']);
        }else{
            echo "Invalid Operation.";die;
        }
    }
    
    function historyAction()
    {
        $this->view->title = "My Training History";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
		
		$training = new Training();
		$list = $training -> HistoryArray($_SESSION["Zend_Auth"]["storage"]->id);
		
		$this->view->list = $list;
    }
    
    function libraryAction()
    {
        $this->view->title = "Training Library";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
		
		$training = new Training();
		$library_list = $training -> LibraryList(1);
		
		$this->view->list = $library_list;
        
    }
    
    function libAddAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Add Topic";
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new TrainingLibraryForm();
        $form->author->setValue($users->GetNameString($_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username));
        $form->submitx->setLabel('Create Topic');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
            //rename attachment
			$random = mt_rand(1000,9999);
			$insert_file = new Filemap();
			
			for($n=1;$n<21;$n++)
			{
				if($form->{"attachment".$n}->getFileName())
				{
					${"attachment".$n} = $form->{"attachment".$n}->getFileName();
					$path = substr(${"attachment".$n},0,28);
					$short_path = substr(${"attachment".$n},21,7);
					${"attachment".$n."_origine"} = substr(${"attachment".$n},28); //remove folder path
					${"attachment".$n."_db"} = time().$random;
					$form->{"attachment".$n}->addFilter('Rename',$path.${"attachment".$n."_db"},1);
					$random += 1;
					
					//get file info
					$get_file_info = $form->{"attachment".$n}->getFileInfo();
					
					//insert to files table
					$insert_file->AddToDb(${"attachment".$n."_origine"}, $short_path.${"attachment".$n."_db"}, $get_file_info["attachment".$n]['type']);
					$att_pool[] = $short_path.${"attachment".$n."_db"};
				}
			}
            
            $formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start

				if(!$form->getValue('title'))
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}

				if(!$form->getValue('category'))
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}

				if(!$form->getValue('author'))
				{
					$this->view->notice="Author is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(trim($form->getValue('author')))
				{
					$users = new Users();
                    $check_user_result = $users -> MakeString($form->getValue('author'), 1);
					
					if(is_array($check_user_result)) //error
					{
						if('error1' == $check_user_result[0])
						{
							$this->view->notice="System can't find the author: ".$check_user_result[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result[0])
						{
							$this->view->notice="You didn't choose a valid author.";
							$form->populate($formData);
						}	
						$error = 1;
					}
				}
                
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
                    //insert to db
					$library = new TrainingLibrary();
                    
					$row = $library->createRow();
					
					$row->title = $form->getValue('title');
					$row->category = $form->getValue('category');
					if($form->getValue('description'))
                    {
                        $row->description = $form->getValue('description');
                    }
					$row->author = $check_user_result;
					$row->updated_date = date("Y-m-d H:i:s");
                    $row->status = 1; //live
                    
                    if(!empty($att_pool))
					{
						$row->attachment = implode("|", $att_pool);
                    }
				
					$row->save();
					$this->_redirect('training/library');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!$formData['title'])
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!$formData['category'])
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!$formData['author'])
				{
					$this->view->notice="Author is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
				
				if(!$error)
				{
					$this->view->notice="Some information are inValid";
					$form->populate($formData);
				}

			}
		}
		
    }
    
    function libEditAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Edit Topic";
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        //create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new TrainingLibraryForm();
		$form->submitx->setLabel('Update');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
            //rename attachment
			$random = mt_rand(1000,9999);
			$insert_file = new Filemap();
			
			for($n=1;$n<21;$n++)
			{
				if($form->{"attachment".$n}->getFileName())
				{
					${"attachment".$n} = $form->{"attachment".$n}->getFileName();
					$path = substr(${"attachment".$n},0,28);
					$short_path = substr(${"attachment".$n},21,7);
					${"attachment".$n."_origine"} = substr(${"attachment".$n},28); //remove folder path
					${"attachment".$n."_db"} = time().$random;
					$form->{"attachment".$n}->addFilter('Rename',$path.${"attachment".$n."_db"},1);
					$random += 1;
					
					//get file info
					$get_file_info = $form->{"attachment".$n}->getFileInfo();
					
					//insert to files table
					$insert_file->AddToDb(${"attachment".$n."_origine"}, $short_path.${"attachment".$n."_db"}, $get_file_info["attachment".$n]['type']);
					$att_pool[] = $short_path.${"attachment".$n."_db"};
				}
			}
            
            $formData = $this->_request->getPost();
            
            $library = new TrainingLibrary();
			$get_lib = $library->fetchRow('id="'.$formData['id'].'"');
            $this->view->topic = $get_lib;
            
			if($form->isValid($formData)){
				$form->getValues();
				
                $library = new TrainingLibrary();
				$get_lib = $library->fetchRow('id="'.$form->getValue('id').'"');
                $this->view->topic = $get_lib;

                ///////////////////////////////////////////////////////////
				//check valid start

				if(!trim($form->getValue('category')))
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!trim($form->getValue('title')))
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!trim($form->getValue('author')))
				{
					$this->view->notice="Author is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(trim($form->getValue('author')))
				{
					$users = new Users();
                    $check_user_result = $users -> MakeString($form->getValue('author'), 1);
					
					if(is_array($check_user_result)) //error
					{
						if('error1' == $check_user_result[0])
						{
							$this->view->notice="System can't find the author: ".$check_user_result[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result[0])
						{
							$this->view->notice="You didn't choose a valid author.";
							$form->populate($formData);
						}	
						$error = 1;
					}
				}
                
				//check valid end
				///////////////////////////////////////////////////////////
				
				if(!$error)
				{
					//insert to db
                    $library = new TrainingLibrary();
                    $row = $library->fetchRow('id = "'.$form->getValue('id').'"');

					$row->category = $form->getValue('category');
					$row->title = $form->getValue('title');
					$row->author = $check_user_result;
					if($form->getValue('description'))
                    {
                        $row->description = $form->getValue('description');
                    }
					$row->updated_date = date("Y-m-d H:i:s");
                    if(!empty($att_pool))
					{
						if($row->attachment)
                        {
                            $ad = "|";
                        }else{
                            $ad = "";
                        }
                        $row->attachment = $row->attachment.$ad.implode("|", $att_pool);
                    }
                    
					$row->save();
					//unset session
					$theid = $form->getValue('id');
                    unset($_SESSION['library_contents'][$theid]);					
                    //redirect
					$this->_redirect('training/library');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!trim($formData['category']))
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!trim($formData['title']))
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                if(!trim($formData['author']))
				{
					$this->view->notice="Author is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
				
				if(!$error)
				{
					$this->view->notice="Some information are inValid";
					$form->populate($formData);
				}

			}
			
			//push static data
			$theid = $form->getValue('id');
			if($_SESSION['library_contents'][$theid])
			{
				$this->view->data = $_SESSION['library_contents'][$theid];
			}
		}else
		{
            if($params['id'])
			{
                $library = new TrainingLibrary();
                $theid = $params['id'];
				$get_lib = $library->fetchRow('id="'.$params['id'].'"');
                
                //get auth
                $training = new Training();
                if(!$training->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $get_lib->author))
                {
                    echo "Invalid Operation.";die;
                }
                
                $get_lib_array = $get_lib->toArray();
                $get_lib_array['author'] = $users->GetNameString($get_lib['author']);
				$form->populate($get_lib_array);
                $this->view->topic = $get_lib;
				$_SESSION['library_contents'][$theid] = $get_lib_array;
                
                $att_string = new Filemap();
				$training = new Training();
                if($training->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $get_lib->author))
                {
                    $this->view->attachments = $att_string -> MakeDownloadLink($get_lib['attachment'], 1, 1, $theid);
                }else{
                    $this->view->attachments = $att_string -> MakeDownloadLink($get_lib['attachment']);
                }
                
				$_SESSION['library_contents'][$theid]['attachments'] = $this->view->attachments;
			}
		}
    }
    
    function libViewAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "View Topic";
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetTrainingMenu($this->getRequest()->getActionName());
        
        if($params['id'])
		{
            $library = new TrainingLibrary();
            $users = new Users();
            $theid = $params['id'];
			$get_lib = $library->fetchRow('id="'.$params['id'].'"');
            
            $get_lib_array = $get_lib->toArray();
            $get_lib_array['author'] = $users->GetNameString($get_lib['author'], TRUE);
            $training_category = new TrainingCategory();
            $get_lib_array['category'] = $training_category->GetCategory($get_lib_array['category']);
            $this->view->topic = $get_lib_array;
                
            $att_string = new Filemap();
			$this->view->attachments = $att_string -> MakeDownloadLink($get_lib['attachment']);
        }
    }
    
    function delattAction()
    {
        $params = $this->_request->getParams();
        
        if($params['lid'] && $params['file'])
        {
            $library = new TrainingLibrary();
			$get_lib = $library->fetchRow('id="'.$params['lid'].'"');
            
            //get auth
            $training = new Training();
            if($training->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $get_lib->author))
            {
                //action
                $filemap = new Filemap();
                $get_lib->attachment = $filemap->DelAttachment($params['file'], $get_lib->attachment);
                $get_lib->save();
                $this->_redirect('training/lib-edit/id/'.$params['lid']);
            }else{
                echo "Invalid Operation.";die;
            }
        }else{
            echo "Invalid Operation.";die;
        }
        
        die;
    }
    
    function dellibAction()
    {
        $params = $this->_request->getParams();
        
        if($params['id'])
        {
            $library = new TrainingLibrary();
			$get_lib = $library->fetchRow('id="'.$params['id'].'"');
            
            //get auth
            $training = new Training();
            if($training->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $get_lib->author))
            {
                //action
                $get_lib->status = 0;
                $get_lib->save();
                $this->_redirect('training/library');
            }else{
                echo "Invalid Operation.";die;
            }
        }else{
            echo "Invalid Operation.";die;
        }
        
        die;
    }
    
    function delarrAction()
    {
        $params = $this->_request->getParams();
        
        if($params['id'])
        {
            $arr = new TrainingArrangement();
			$get_arr = $arr->fetchRow('id="'.$params['id'].'"');
            
            //get auth
            $training_trainer = new TrainingTrainer();
            $users = new Users();
            $training = new Training();
            if($training->HighAuth($_SESSION["Zend_Auth"]["storage"]->id, $users->MakeString($training_trainer->GetTrainer($params['id']))))
            {
                //action
                $get_arr->status = 0;
                $get_arr->save();
                $this->_redirect('training/index');
            }else{
                echo "Invalid Operation.";die;
            }
        }else{
            echo "Invalid Operation.";die;
        }
        
        die;
    }

}
