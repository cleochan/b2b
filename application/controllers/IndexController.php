<?php

class IndexController extends Zend_Controller_Action
{
  
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
            if('call-file' != $this->getRequest()->getActionName())
            {
                $auth = Zend_Auth::getInstance();
                $users = new Users();
                if(!$auth->hasIdentity() || !$users->IsValid())
                { 
                    $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
                }
            }
            //get system title
            $get_title = new Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");

            //make top menu
            $menu = new Menu();
            $this->view->top_menu = $menu -> GetTopMenu($this->getRequest()->getControllerName());

            //echo "<pre>";
            //print_r($_SESSION);
            //echo "<pre>";
            //die;
    }
	
    function indexAction()
    {
        $this->view->title = "Tickets";
        $params = $this->_request->getParams();
        
        //check list mode start
        if($_SESSION["Zend_Auth"]["storage"]->default_list)
        {
            $this->_redirect('tasks/index/type/'.$params['type']);
        }
        //check list mode finished
        
        $this->view->type = $params['type'];
        $menu = new Menu();
        $this->view->menu = $menu -> GetTicketMenu($params['type']); 
        //create user list

        $users = new Users();
        $this->view->get_user_list = $users -> MakeListForDropDown();

        $projects = new Projects();
        $this->view->get_project_list = $projects -> GetArray();
        
 
        //$_SESSION['search_ticket_users_current']
        if(!$_SESSION['search_ticket_users_current'])
        {
                $_SESSION['search_ticket_users_current'] = $_SESSION["Zend_Auth"]["storage"]->id;
        }

        $tickets = new Tickets();
        $this->view->p_status = $tickets -> StatusArray();
        $tickets -> request = $params['type'];

        if($params['keyword'])
        {
                $_SESSION['search_ticket_keyword'] = $params['keyword'];
        }

        $tickets -> keyword = $_SESSION['search_ticket_keyword'];

        $tickets -> st = $_SESSION['search_ticket_sort'];
        $this->view->sort = $_SESSION['search_ticket_sort'];
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $tickets -> page = $current_page;

        $list = $tickets -> PushListData();
        $this->view->list = $list;

        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;

        $tickets -> page = $this->view->older;
        $list = $tickets -> PushListData();
        //print_r($list);die;
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $tickets -> page = $this->view->newer;
                $list = $tickets -> PushListData();
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }

        $this->view->p_type = $params['type'];
        $this->view->p_page = $params['page'];

    }
	
    function indexTasksAction()
    {
        $this->view->title = "Tickets - Task View";
        $params = $this->_request->getParams();
        $this->view->type = $params['type'];
        $menu = new Menu();
        $this->view->menu = $menu -> GetTicketMenu($params['type']); 
        //create user list

        $users = new Users();
        $_SESSION['search_ticket_users'] = $users -> GetStaffInfoArray($_SESSION["Zend_Auth"]["storage"]->id);
 
        //$_SESSION['search_ticket_users_current']
        if(!$_SESSION['search_ticket_users_current'])
        {
                $_SESSION['search_ticket_users_current'] = $_SESSION["Zend_Auth"]["storage"]->id;
        }

        $tickets = new Tickets();
        $this->view->p_status = $tickets -> StatusArray();
        $tickets -> request = $params['type'];

        if($params['keyword'])
        {
                $_SESSION['search_ticket_keyword'] = $params['keyword'];
        }

        $tickets -> keyword = $_SESSION['search_ticket_keyword'];

        if($params['sort'])
        {
                $_SESSION['search_ticket_sort'] = $params['sort'];
        }else
        {
                $_SESSION['search_ticket_sort'] = 30; //update time desc
        }

        $tickets -> st = $_SESSION['search_ticket_sort'];
        $this->view->sort = $_SESSION['search_ticket_sort'];
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $tickets -> page = $current_page;

        $list = $tickets -> PushListData();
        $this->view->list = $list;

        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;

        $tickets -> page = $this->view->older;
        $list = $tickets -> PushListData();
        //print_r($list);die;
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $tickets -> page = $this->view->newer;
                $list = $tickets -> PushListData();
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }

        $this->view->p_type = $params['type'];
        $this->view->p_page = $params['page'];

    }
    
    function checkItemAction()
    {
        $params = $this->getRequest()->getParams();
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity())
        {
            $update['checked'] = $params['check'];
            $update['modified_date'] = date('Y-m-d H:i:s');
            $userIdentity = $auth->getIdentity();
            $update['user_id'] = $userIdentity->id;
            $responseJson = array();
            //update tickets table;
            $ups['update_who'] = $userIdentity->id.'@'.$userIdentity->username;
            $ups['update_when'] = $update['modified_date'];
            $tickets = new Tickets();
            $tickets -> MakeRead(2, $params['id']);
           

        try {    
            $this->db->update('tickets',$ups,"id = {$params['id']}");
            $this->db->update('track', $update, "id = {$params['lid']}");
            //load again
            $select = $this->db->select();
            $select->from('track');
            $select->joinLeft('users', 'track.user_id = users.id', array('users.realname'));
            $select->where('t_id =?',$params['id']);
            $select->order('list_id ASC');
            $rows = $this->db->fetchAll($select);
            $list['checked'] = array();
            $list['unchecked'] = array();
            foreach ($rows as $r)
            {
                if ($r['checked'])
                {
                    $list['checked'][$r['id']]['id'] = $r['id'];
                    $list['checked'][$r['id']]['checked'] = $r['checked'];
                    $list['checked'][$r['id']]['contents'] = $r['contents'];
                    $list['checked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['checked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['checked'][$r['id']]['realname'] = $r['realname'];
                    $list['checked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['checked'][$r['id']]['t_id']=$r['t_id'];
                }
                else
                {
                    $list['unchecked'][$r['id']]['id'] = $r['id'];
                    $list['unchecked'][$r['id']]['checked'] = $r['checked'];
                    $list['unchecked'][$r['id']]['contents'] = $r['contents'];
                    $list['unchecked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['unchecked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['unchecked'][$r['id']]['realname'] = $r['realname'];
                    $list['unchecked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['unchecked'][$r['id']]['t_id']=$r['t_id'];
                }
            }
    //                help($list);
            $this->view->lists = $list;

            echo $this->view->render('/index/olist.phtml');
            die;
        }  catch(Exception $e)
        {
            echo $e->getMessage();
        }
        }

    }
    
    function insertItemAction()
    {
        $params = $this->getRequest()->getParams();
        $select = $this->db->select();
        $select->from('track','list_id')
               ->where("t_id = ?",$params['id'])
               ->order("t_id DESC");
        $result = $this->db->fetchAll($select);
        foreach ($result as $rows)
        {
            $row[] = $rows['list_id'];
        }
        $bigest = $row[0];
        for($i=0;$i<count($row);$i++)
        {             
           if($bigest<$row[$i])
           {
               $bigest = $row[$i];
           }           
        }               
        $insert['contents'] = $params['newListItem'];
        $insert['checked'] = 0;
        $insert['t_id'] = $params['id'];
        $insert['list_id'] = $bigest+1;
        $ticket['update_when'] = date('Y-m-d H:i:s');
        $tickets = new Tickets();
        $tickets -> MakeRead(2, $params['id']);
        try
        {
            $this->db->update('tickets',$ticket,"id={$params['id']}");
            $this->db->insert('track', $insert);
            // Load the checklist again
            $select = $this->db->select();
            $select->from('track');
            $select->joinLeft('users', 'track.user_id = users.id', array('users.realname'));
            $select->where('t_id =?',$params['id']);
            $select->order('list_id ASC');
            $rows = $this->db->fetchAll($select);
            $list['checked'] = array();
            $list['unchecked'] = array();
            foreach ($rows as $r)
            {
                if ($r['checked'])
                {
                    $list['checked'][$r['id']]['id'] = $r['id'];
                    $list['checked'][$r['id']]['checked'] = $r['checked'];
                    $list['checked'][$r['id']]['contents'] = $r['contents'];
                    $list['checked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['checked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['checked'][$r['id']]['realname'] = $r['realname'];
                    $list['checked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['checked'][$r['id']]['t_id'] = $r['t_id'];
                }
                else
                {
                    $list['unchecked'][$r['id']]['id'] = $r['id'];
                    $list['unchecked'][$r['id']]['checked'] = $r['checked'];
                    $list['unchecked'][$r['id']]['contents'] = $r['contents'];
                    $list['unchecked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['unchecked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['unchecked'][$r['id']]['realname'] = $r['realname'];
                    $list['unchecked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['unchecked'][$r['id']]['t_id'] = $r['t_id'];
                }
            }
            $this->view->lists = $list;

            echo $this->view->render('/index/olist.phtml');
            die;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    function updateItemAction()
    {
            $params = $this->getRequest()->getParams();         
            $itemId = $params['id'];
            $update['contents'] = $this->_request->getPost('textarea_listContents'.$params['id']);
            $ticket['update_when'] = date('Y-m-d H:i:s');
            $tickets = new Tickets();
            $tickets -> MakeRead(2, $params['tid']);
            try
            {   
            $this->db->update('tickets',$ticket,"id={$params['tid']}");
            $this->db->update('track', $update, "id = {$itemId}");
            $responseJson['success'] = 1;
            }
            catch(Exception $e)
            {
            $responseJson['success'] = 0;
            $responseJson['message'] = $e->getMessage();
            }
      
        echo Zend_Json::encode($responseJson);
        die;
    }
    
    function deleteItemAction()
    {
        $params = $this->getRequest()->getParams();
        $itemId = $params['id'];
        $itmUp['update_when'] = date('Y-m-d H:i:s');
        $tickets = new Tickets();
        //update who Readed this ticket
        $tickets -> MakeRead(2, $params['tid']);
        
        try
        {
            $this->db->update('tickets',$itmUp,"id={$params['tid']}");
            $this->db->delete('track', "id = {$itemId}");
           // $responseJson['success'] = 1;
            
            // you are here!!
            $select = $this->db->select();
            $select->from('track');
            $select->joinLeft('users', 'track.user_id = users.id', array('users.realname'));
            $select->where('t_id =?',$params['tid']);
            $select->order('list_id ASC');
            $rows = $this->db->fetchAll($select);
            $list['checked'] = array();
            $list['unchecked'] = array();
            foreach ($rows as $r)
            {
                if ($r['checked'])
                {
                    $list['checked'][$r['id']]['id'] = $r['id'];
                    $list['checked'][$r['id']]['checked'] = $r['checked'];
                    $list['checked'][$r['id']]['contents'] = $r['contents'];
                    $list['checked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['checked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['checked'][$r['id']]['realname'] = $r['realname'];
                    $list['checked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['checked'][$r['id']]['t_id'] = $r['t_id'];
                }
                else
                {
                    $list['unchecked'][$r['id']]['id'] = $r['id'];
                    $list['unchecked'][$r['id']]['checked'] = $r['checked'];
                    $list['unchecked'][$r['id']]['contents'] = $r['contents'];
                    $list['unchecked'][$r['id']]['modified_date'] = $r['modified_date'];
                    $list['unchecked'][$r['id']]['user_id'] = $r['user_id'];
                    $list['unchecked'][$r['id']]['realname'] = $r['realname'];
                    $list['unchecked'][$r['id']]['list_id'] = $r['list_id'];
                    $list['unchecked'][$r['id']]['t_id'] = $r['t_id'];
                }
            }
            $this->view->lists = $list;

            echo $this->view->render('/index/olist.phtml');

        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
        die();
    }
    
    
    function addAction()
    {
        //check permission
        if(4 == $_SESSION["Zend_Auth"]["storage"]->level_mgt)
        {
             $this->_redirect('index/index');
        }
        
		$params = $this->_request->getParams();
		$this->view->title = "Add Ticket";
        
        switch(date("N"))
        {
            case 1:
            case 2:
            case 3:
                $this->view->deadline_max = 2;
                break;
            case 4:
            case 5:
            case 6:
                $this->view->deadline_max = 4;
                break;
            case 7:
                $this->view->deadline_max = 3;
                break;
        }
		
		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
		
		//create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new TicketForm();
		$now = date("Y-m-d H:i:s");
		$form->submitx->setLabel('Create Ticket');
		
        //check if it's from request
        if($params['from_request'])
        {
            $request_model = new Requests();
            $get_request = $request_model->fetchRow("id = '".$params['from_request']."'");
            
            if($get_request)
            {
                $make_parti = $get_request['composer'];
                if($get_request['participants'])
                {
                    $make_parti .= "|".$get_request['participants'];
                }
                
                $form->participants->setValue($users -> GetNameString($make_parti));
                $form->title->setValue($get_request['title']);
                $form->contents->setValue($get_request['contents']);
                $form->submitx->setLabel('Close Request and Create Ticket');
                $form->from_request->setValue($params['from_request']);
                $form->from_request_att->setValue($get_request['attachment']);
                
                //attachments for subject
				$att_string = new Filemap();
				$this->view->original_attachments = $att_string -> MakeDownloadLink($get_request['attachment']);
            }
        }elseif($params['from_ticket']) //duplicated ticket
        {
            $tickets_model = new Tickets();
            $get_ticket = $tickets_model->fetchRow("id = '".$params['from_ticket']."'");
            
            if($get_ticket)
            {   
                $form->participants->setValue($users->GetNameString($get_ticket['participants']));
                $form->category->setValue($get_ticket['category']);
                if($get_ticket['project'])
                {
                    $form->project->setValue($get_ticket['project']);
                }
                $form->priority->setValue($get_ticket['priority']);
                $form->title->setValue($get_ticket['title']);
                $form->contents->setValue($get_ticket['contents']);
                $form->from_ticket->setValue($params['from_ticket']);
                $form->from_ticket_att->setValue($get_ticket['attachment']);
                
                //attachments for subject
				$att_string = new Filemap();
				$this->view->original_attachments = $att_string -> MakeDownloadLink($get_ticket['attachment']);
            }
        }
        
        
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
                
				$check_user_string = new Users;
								
                //4, check dead line value
                if($form->getValue('dead_line'))
                {
                    if(strtotime($form->getValue('dead_line')) - time() > ($this->view->deadline_max + 1) * 24 * 3600)
                    {
                        $this->view->notice="You set a wrong deadline.";
						$form->populate($formData);
                        $error = 1;
                    }
                }

                //5, participants if exist
				if(trim($form->getValue('participants')))
				{
					$check_user_result3 = $check_user_string -> MakeString($form->getValue('participants'), 1);
					
					if(is_array($check_user_result3)) //error
					{
						if('error1' == $check_user_result3[0])
						{
							$this->view->notice="System can't find the participant: ".$check_user_result3[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result3[0])
						{
							$this->view->notice="You didn't choose a valid participant.";
							$form->populate($formData);
						}	
						$error = 1;
					}
				}
												
				if(!$error)
				{
					//insert to db
					$tickets = new Tickets();
				
					$row = $tickets->createRow();

					if($form->getValue('dead_line'))
					{
						$row->dead_line = $form->getValue('dead_line')." 23:59:59";
					}
					$row->category = $form->getValue('category');
                    if($form->getValue('project'))
					{
                        $row->project = $form->getValue('project');
                    }
					$row->priority = $form->getValue('priority');
					$row->title = $form->getValue('title');
					if($form->getValue('contents'))
					{
						$row->contents = $form->getValue('contents');
					}
					$row->status = $form->getValue('status');
					$get_status = $tickets -> UpdateTime($form->getValue('status'));
					if($get_status[0])
					{
						$row->{$get_status[0]} = $get_status[1];
					}
                    
					if($check_user_result3)
					{
						$row->participants = $check_user_result3;
					}
				
					//auto
					$row->created_date = $now;
					$row->composer = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
					$row->update_who = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
					$row->update_when = $now;
                   
					if(!empty($att_pool))
					{
						$row->attachment = implode("|", $att_pool);
                    }
                    
                    //add the attachment from request
                    if($form->getValue('from_request_att'))
                    {
                        $row->attachment = $row->attachment."|".$form->getValue('from_request_att');
                    }elseif($form->getValue('from_ticket_att'))
                    {
                        $row->attachment = $row->attachment."|".$form->getValue('from_ticket_att');
                    }
			       
   				    $pt = $row->save();
                    
                    if($form->getValue('trackList')){
                        $tId = $row->save();
                        $dataList = explode("\n", $form->getValue('trackList'));
                        $i = 1;
                        foreach ($dataList as $value) 
                        {
                            $data = array('t_id'=>$tId, 'contents'=>$value,'list_id'=>$i);
                            $this->db->insert('track',$data);
                            $i++;
                        }
                    }
                    
                    //add relation with request
                    if($form->getValue('from_request'))
                    {
                        $requests_tickets = new RequestsTickets();
                        $r_row = $requests_tickets->createRow();
                        $r_row->request_id = $form->getValue('from_request');
                        $r_row->ticket_id = $pt;
                        $r_row->save();
                    }
                    
                    //close the request
                    if($form->getValue('from_request'))
                    {
                        $requests_model = new Requests();
                        $requests_status_update = $requests_model->fetchRow("id = '".$form->getValue('from_request')."'");
                        $requests_status_update->status = 2; //closed
                        $requests_status_update->closed_date = $now;
                        $requests_status_update->closed_by = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
                        $requests_status_update->save();
                        
                        //and send mail of closing request
                        $mail = new Mail();
                        $mail->url = "requests/view/id/".$form->getValue('from_request');
                        $mail->ticket_title = $get_request['title'];
                        $mail->mail_contents = $mail->ContentsTemplate(6);
                        $mail->mail_subject = "Request Closed: ".$get_request['title'];
                        

                        if($user_list_for_send)
                        {
                            $mail->to = $check_user_string -> MakeMailUserList($check_user_string->GetUserIdArray($get_request['participants']));
                        }

                        $mail->cc = $check_user_string -> MakeMailUserList($check_user_string->GetUserIdArray($get_request['composer']));


                        //send
                        $mail->Send();
                    }
                    
                    //send email
                    $mail = new Mail();
                    $file_map = new Filemap();
                    $mail->url = "index/view/type/".$menu->GetType($form->getValue('status'))."/id/".$pt;
                    $mail->ticket_title = $form->getValue('title');
                    $mail->tx = $mail->FormatUrl($form->getValue('contents'));
                    if($row->attachment)
                    {
                        $mail->attachment = $file_map->MakeDownloadLink($row->attachment);
                    }
                    $mail->mail_contents = $mail->ContentsTemplate(1);
                    $mail->mail_subject = "Ticket Created: ".$form->getValue('title');
                    
                    if($check_user_result3)
                    {
                        $user_string = $check_user_result3;
                        $user_list_for_send = $check_user_string -> GetUserIdArray($user_string);
                        $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                    }
                    
                    $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));
                    
                    //send
                    $mail->Send();
                                    
					//redirect, depends on the initial status
					$menu = new Menu();
					$this->_redirect('index/view/type/'.$menu->GetType($form->getValue('status')).'/id/'.$pt);
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				//1, project
				if(!$formData['category'])
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//2, title
				if(!trim($formData['title']))
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}
                
                //3, dead line
                if($form->getValue('dead_line'))
                {
                    if(strtotime($form->getValue('dead_line')) - time() > ($this->view->deadline_max + 1) * 24 * 3600)
                    {
                        $this->view->notice="You set a wrong deadline.";
						$form->populate($formData);
                        $error = 1;
                    }
                }
				
				//check valid end
				///////////////////////////////////////////////////////////
				
				if(!$error)
				{
					$this->view->notice="Some information are inValid.";
					$form->populate($formData);
				}

			}
		}
    }
    
    function viewAction()
    {
		$params = $this->_request->getParams();
        //print_r($params);die;
		$this->view->title = "Ticket #".$params["id"];
		$theid = $params["id"];
        
        switch(date("N"))
        {
            case 1:
            case 2:
            case 3:
                $this->view->deadline_max = 2;
                break;
            case 4:
            case 5:
            case 6:
                $this->view->deadline_max = 4;
                break;
            case 7:
                $this->view->deadline_max = 3;
                break;
        }
        
        //division status control start
        $get_tickets_users = $this->db->select();
        $get_tickets_users -> from("tickets_users", array("id as uid", "ticket_id", "user_id", "user_type", "notes", "status", "creator", "workbook"));
        $get_tickets_users -> joinLeft("kpi_tickets", "tickets_users.id = kpi_tickets.tickets_users_id", array("id as tid", "tickets_users_id", "score", "efficiency", "suggestion_hour", "used_time"));
        $get_tickets_users -> where("tickets_users.ticket_id = ?", $params['id']);
        $get_tickets_users_array = $this->db->fetchAll($get_tickets_users);
            
        $check_user_string = new Users();
            
        foreach($get_tickets_users_array as $user_d_key => $user_d_val)
        {
            $get_tickets_users_array[$user_d_key]['user_name'] = $check_user_string -> GetRealName($user_d_val['user_id']);
            $get_tickets_users_array[$user_d_key]['user_type'] = $check_user_string -> UserType($user_d_val['user_type']);
        }
        
        $this->view->tickets_users_array = $get_tickets_users_array;
        
        //division status control over

		//make it is_read
		$tickets = new Tickets();
		$tickets -> MakeRead(1, $params["id"], $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);

		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
		$this->view->type = $params['type'];
		
		$now = date("Y-m-d H:i:s");
		
		$form = new TicketForm();
		$form->submitx->setLabel('Submit');
		$this->view->form = $form;

                $select = $this->db->select();
                $select->from('track');

                $select->joinLeft('users', 'track.user_id = users.id', array('users.realname'));
                $select->where('t_id =?', $params['id']);
                $select->order('list_id ASC');
                $rows = $this->db->fetchAll($select);


                $list['checked'] = array();
                $list['unchecked'] = array();

                foreach ($rows as $r)
                {
                    if ($r['checked'])
                    {
                        $list['checked'][$r['id']]['id'] = $r['id'];
                        $list['checked'][$r['id']]['checked'] = $r['checked'];
                        $list['checked'][$r['id']]['contents'] = $r['contents'];
                        $list['checked'][$r['id']]['modified_date'] = $r['modified_date'];
                        $list['checked'][$r['id']]['user_id'] = $r['user_id'];
                        $list['checked'][$r['id']]['realname'] = $r['realname'];
                        $list['checked'][$r['id']]['t_id'] = $r['t_id'];
                        $list['checked'][$r['id']]['list_id'] = $r['list_id'];
                    }
                    else
                    {
                        $list['unchecked'][$r['id']]['id'] = $r['id'];
                        $list['unchecked'][$r['id']]['checked'] = $r['checked'];
                        $list['unchecked'][$r['id']]['contents'] = $r['contents'];
                        $list['unchecked'][$r['id']]['modified_date'] = $r['modified_date'];
                        $list['unchecked'][$r['id']]['user_id'] = $r['user_id'];
                        $list['unchecked'][$r['id']]['realname'] = $r['realname'];
                        $list['unchecked'][$r['id']]['t_id'] = $r['t_id'];
                        $list['unchecked'][$r['id']]['list_id'] = $r['list_id'];
                    }
                }
                $this->view->lists = $list;
                       
		if($this->_request->isPost()){
            
			if(!$_SESSION['ticket_contents'][$theid]['ticket_auth'])
			{
				//change the attribute of the form model
				$form->project->setRequired(False);
				$form->priority->setRequired(False);
				$form->title->setRequired(False);
				$form->status->setRequired(False);
				$form->category->setRequired(False);
			}
		
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
				//file uploads
			$post_info = $form->getValues();
				if($_SESSION['ticket_contents'][$theid]['ticket_auth'])
				{
                    //new users
					$check_user_string = new Users;
								
					//4, check dead line value
                    if($form->getValue('dead_line'))
                    {
                        if(strtotime($form->getValue('dead_line')) - time() > ($this->view->deadline_max + 1) * 24 * 3600)
                        {
                            $this->view->notice="You set a wrong deadline.";
                            $form->populate($formData);
                            $error = 1;
                        }
                    }

                    //5, participant if exist
					if(trim($form->getValue('participants')))
					{
						$check_user_result3 = $check_user_string -> MakeString($form->getValue('participants'), 1);
					
						if(is_array($check_user_result3)) //error
						{
							if('error1' == $check_user_result3[0])
							{
								$this->view->notice="System can't find the participant: ".$check_user_result3[1];

								$form->populate($formData);
							}elseif('error2' == $check_user_result3[0])
							{
								$this->view->notice="You didn't choose a valid participant.";
								$form->populate($formData);
							}	
							$error = 1;
						}
					}
                    
                    //6, check all the tasks closed if ticket needs to be closed
                    if(3 == $form->getValue('status'))
                    {
                        $users_involved2 = $this->db->select();
                        $users_involved2 -> from("tickets_users as u", array("id as uid"));
                        $users_involved2 -> joinLeft("kpi_tickets as k", "k.tickets_users_id = u.id", array("id as kid", "suggestion_hour as khour", "used_time as kused"));
                        $users_involved2 -> where("u.ticket_id = ?", $form->getValue('id'));
                        $users_involved2 -> where("u.del='0' or u.del='' or u.del is null");
                        $users_involved2 -> where("u.status = ?", 1); //started
                        $users_involved2_array = $this->db->fetchAll($users_involved2);
                        
                        if(!empty($users_involved2_array))
                        {
                            $this->view->notice="You can't close the ticket if there are still active tasks.";
							$form->populate($formData);
                            $error = 1;
                        }
 
                    }
				
					//check valid end
					///////////////////////////////////////////////////////////				
				}				
								
				if(!$error)
				{
                    //Step1: insert comments if exists
					if($form->getValue('comments') || !empty($att_pool))
					{
						$comments = new Comments();
						$add_comments = $comments->createRow();
					
						$add_comments->ticket_id = $form->getValue('id');
						$add_comments->contents = $form->getValue('comments');
						$add_comments->composer = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
						$add_comments->created_date = $now;

						if(!empty($att_pool))
						{
							$add_comments->attachment = implode("|", $att_pool);
						}
					
						$add_comments->save();
					}
					
					//Step2: update ticket
					$tickets = new Tickets();
				
					$row = $tickets->fetchRow('id = "'.$form->getValue('id').'"');

					if($_SESSION['ticket_contents'][$theid]['ticket_auth'])
					{
                        if($form->getValue('dead_line'))
						{
							$row->dead_line = $form->getValue('dead_line')." 23:59:59";
						}
						$row->category = $form->getValue('category');
                        $row->project = $form->getValue('project');
						$row->priority = $form->getValue('priority');
						$row->title = $form->getValue('title');
						$row->status = $form->getValue('status');
						$get_status = $tickets -> UpdateTime($form->getValue('status'), $form->getValue('id'));
						if($get_status[0])
						{
							$row->{$get_status[0]} = $get_status[1];
						}
						$row->participants = $check_user_result3;
					}
				
					//auto
					$row->update_who = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
					$row->update_when = $now;
				
					$row->save();
                    
                    //check efficiency if it's closed
                    if(3 == $form->getValue('status'))
                    {
                        $kpi = new Kpi();
                        $kpi_tickets = new KpiTickets();
                        
                        $users_involved = $this->db->select();
                        $users_involved -> from("tickets_users as u", array("id as uid"));
                        $users_involved -> joinLeft("kpi_tickets as k", "k.tickets_users_id = u.id", array("id as kid", "suggestion_hour as khour", "used_time as kused"));
                        $users_involved -> where("u.ticket_id = ?", $form->getValue('id'));
                        $users_involved -> where("u.del='0' or u.del='' or u.del is null");
                        $users_involved -> where("u.status = ?", 0); //must be stopped
                        $users_involved_array = $this->db->fetchAll($users_involved);
                        
                        if(!empty($users_involved_array))
                        {
                            foreach($users_involved_array as $users_involved_val)
                            {
                                $eff = $kpi ->Efficiency($users_involved_val['khour'], $users_involved_val['kused']);
                                
                                $kt = $kpi_tickets->fetchRow("id = '".$users_involved_val['kid']."'");
                                $kt->efficiency = $eff;
                                $kt->save();
                            }
                        }
                    }
                    
                    //sending email
                    if($check_user_result3) //participants
                    {
                        $user_string = $check_user_result3;
                    }
                    //get staffs
                    $tickets_users = new TicketsUsers();
                    $staff_array = $tickets_users->GetUserArray($form->getValue('id'));
                    $user_list_for_send = $check_user_string -> GetUserIdArray($user_string);
                    $user_list_for_send = array_unique(array_merge($user_list_for_send, $staff_array));
                    
                    if(in_array($form->getValue('status'), array(3,4))) //closed/canceled
                    {
                        //send email close ticket
                        $mail = new Mail();
                        $file_map = new Filemap();
                        $mail->url = "index/view/type/".$params['type']."/id/".$form->getValue('id');
                        $mail->ticket_title = $form->getValue('title');
                        $mail->tx = $mail->FormatUrl($add_comments->contents);
                        $mail->attachment = $file_map->MakeDownloadLink($add_comments->attachment);
                        $mail->mail_contents = $mail->ContentsTemplate(3);
                        $mail->mail_subject = "Ticket Closed: ".$form->getValue('title');

                        if($user_list_for_send)
                        {
                            $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                        }

                        $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));


                        //send
                        $mail->Send();
                    }else
                    {
                        if($form->getValue('comments') || !empty($att_pool)) 
                        {
                            //send email update ticket
                            $mail = new Mail();
                            $file_map = new Filemap();
                            $mail->url = "index/view/type/".$params['type']."/id/".$form->getValue('id');
                            $mail->ticket_title = $row->title;
                            $mail->tx = $mail->FormatUrl($add_comments->contents);
                            $mail->attachment = $file_map->MakeDownloadLink($add_comments->attachment);
                            $mail->mail_contents = $mail->ContentsTemplate(2);
                            $mail->mail_subject = "Ticket Updated: ".$row->title;

                            if($user_list_for_send)
                            {
                                $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                            }

                            $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));


                            //send
                            $mail->Send();
                        }
                    }
                    
					//unset session
					$theid = $form->getValue('id');
					unset($_SESSION['ticket_contents'][$theid]);
					
					//make is_read to blank
					$tickets -> MakeRead(2, $form->getValue('id'));
					
					//redirect
					if($params['division'])
                    {
                        $this->_redirect('index/division/type/'.$params['type'].'/id/'.$form->getValue('id'));
                    }else
                    {
                        $this->_redirect('index/view/type/'.$params['type'].'/id/'.$form->getValue('id'));
                    }
				}
			}else{
                            
				if($_SESSION['ticket_contents'][$theid]['ticket_auth'])
				{
					///////////////////////////////////////////////////////////
					//check valid start
				
					//1, project
					if(!$formData['category'])
					{
						$this->view->notice="Category is required.";
						$form->populate($formData);
						$error = 1;
					}
				
					//2, title
					if(!trim($formData['title']))
					{
						$this->view->notice="Title is required.";
						$form->populate($formData);
						$error = 1;
					}
				
					//check valid end
					///////////////////////////////////////////////////////////
				}				
				if(!$error)
				{
					$this->view->notice="Some information are inValid";
					$form->populate($formData);
				}
			}
			
			//push static data
			if($_SESSION['ticket_contents'][$theid])
			{
				$this->view->ticket = $_SESSION['ticket_contents'][$theid]['contents'];
				$this->view->attachments = $_SESSION['ticket_contents'][$theid]['attachments'];
				$this->view->comments_data = $_SESSION['ticket_contents'][$theid]['comments_data'];
				$this->view->users_array = $_SESSION['ticket_contents'][$theid]['users_array'];
				$this->view->ticket_auth = $_SESSION['ticket_contents'][$theid]['ticket_auth'];
			}
		}else
		{
            if($params['id'])
			{
                $ticket = $tickets->fetchRow('id="'.$params['id'].'"');
				//update contents
				$ticket->dead_line = substr($ticket->dead_line,0,10);
				$users = new Users();
				$ticket->participants = $users -> GetNameString($ticket->participants);				
				$this->view->id = $params['id'];
				$form->populate($ticket->toArray());
				
				//push static data for read only
                $ticket = $ticket->toArray();
				$project_class = new Projects();
				$get_project_name = $project_class -> fetchRow('id = "'.$ticket['project'].'"');
				$ticket['project'] = $get_project_name -> project_name;
				$ticket['status'] = $tickets -> GetStatusStr($ticket['status']);
				$ticket['priority'] = $tickets -> Priority($ticket['priority']);
				$ticket['skype'] = $users ->GetSkype($ticket['composer']);
				$ticket['composer'] = $users -> GetRealName($ticket['composer']);
				$ticket['make_read'] = $users -> GetNameString($ticket['make_read'], TRUE);
				$this->view->ticket = $ticket;
				$theid = $params['id'];
				$_SESSION['ticket_contents'][$theid]['contents'] = $ticket;
				
				//attachments for subject
				$att_string = new Filemap();
				$this->view->attachments = $att_string -> MakeDownloadLink($ticket['attachment']);
				$_SESSION['ticket_contents'][$theid]['attachments'] = $this->view->attachments;
				
				//push comments
				$comments = new Comments();
				$comments_data = $comments -> fetchAll('ticket_id = "'.$params['id'].'"');
                                $comments_array=array ();
				foreach($comments_data as $data_val)
				{
					//make object to array
					$data['ticket_id'] = $data_val->ticket_id;
					$data['contents'] = $data_val->contents;
					$data['attachment'] = $att_string -> MakeDownloadLink($data_val->attachment);
					$data['composer'] = $users -> GetRealName($data_val->composer);
					$data['created_date'] = $data_val->created_date;
                    $data['skype'] = $users ->GetSkype($data_val->composer);
					$comments_array[] = $data;
				}
				$this->view->comments_data = $comments_array;
				$_SESSION['ticket_contents'][$theid]['comments_data'] = $comments_array;
				
				//create user list
				$this->view->users_array = $users -> GetRealNameString();
				$_SESSION['ticket_contents'][$theid]['users_array'] = $this->view->users_array;
				
				//get ticket auth
				$this->view->ticket_auth = $users -> GetTicketAuth($params['id']);
				$_SESSION['ticket_contents'][$theid]['ticket_auth'] = $this->view->ticket_auth;
			}
        }
    }
    
    function callFileAction()
    {
    	$params = $this->_request->getParams();
    	
		$filemap = new Filemap();
		$data = $filemap ->fetchRow("indb like '%".$params['val']."%'");
		
		if($data['ftype'])
		{
			//print_r('/attachment/'.$data['indb']);die;
			header('Content-type: '.$data['ftype']);
			header('Content-Disposition: attachment; filename="'.$data['origine'].'"');
			readfile('../public/attachment/'.$data['indb']);
		}

		die;
    }
    
    function putSearchSessionProjectAction()
    {
    	$params = $this->_request->getParams();
    	$_SESSION['search_ticket_projects_current'] = $params['val'];
    }
    
    function putSearchSessionUserAction()
    {
    	$params = $this->_request->getParams();
    	$_SESSION['search_ticket_users_current'] = $params['val'];
    }
    
    function changeFocusAction()
    {
    	$params = $this->_request->getParams();

    	if($params['tid'])
    	{
			$tickets = new Tickets();
			$ticket = $tickets -> MakeFocus($params['tid']);
			
			//for dojo response
			print_r($ticket);
    	}
    	die;
    }
    
    function checkNewAction()
    {
    	if($_SESSION["Zend_Auth"]["storage"]->new_alert)
    	{
    		$tickets = new Tickets();
    		$status = $tickets -> GetLastOne();
    		if($status)
    		{
    			print_r(" [Ticket Notice] ".$status." ");
    		}
    	}
    	
    	die;
    }
    
    function batchAction()
    {
    	$params = $this->_request->getParams();
    	
    	$tickets = new Tickets();
    	

    	
    	if(!empty($params['tick']))
    	{
			if('read' == $params['act'])
			{
				foreach($params['tick'] as $tick)
				{
					$tickets -> MakeRead(1, $tick, $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username);
				}
				
			}elseif('move' == $params['act'] && $params['move'])
			{
				foreach($params['tick'] as $tick)
				{
					$origine = $tickets->select();
					$origine -> where("id = ?", $tick);
					$data = $tickets->fetchRow($origine);
					
					$data -> status = $params['move'];
					$data -> save();
				}
			}
    	}
    	
    	if($params['p_page'])
    	{
    		$p_page = "?page=".$params['p_page'];
    	}else
    	{
    		$p_page = "";
    	}    	
    	$this->_redirect("/index/index/type/".$params['p_type'].$p_page);    	
    	die;
    }
    function exportCsvAction()
    {
        $params = $this->_request->getParams();
        try
        {
        
        $select = $this->db->select();
        $select->from('track');       
        $select->where('t_id = ?',$params['id']);
        $select->order('list_id ASC');
        $result = $this->db->fetchAll($select);
        }
       catch (Exception $e)
       {
           echo $e->getMessage();
       }
       if(!empty ($result))
       {
           $i = 0;
           foreach ($result as $row)
          {
               if($row['checked'])
               {
                $rows[$i]['A'] = $row['list_id'];
                $rows[$i]['B'] = $row['contents'];
                $csvchecked[$i] = implode(";",$rows[$i]);
               }
              else 
               {
                $rows[$i]['A'] = $row['list_id'];
                $rows[$i]['B'] = $row['contents'];
                $csvUnchecked[$i] = implode(";",$rows[$i]);
               }
               $i++;
          }
       }
       $time = date("YmdHi");
       header("Content-type:application/vnd.ms-excel");
       header("Content-Disposition:filename=Check List {$time}.xls");
        echo "Check List {$time}\n";
        echo "UnChecked Lists:\n";
        foreach ($csvUnchecked as $csvUnCheckedData)
         {
           $a = str_replace("\n","",split(";",($csvUnCheckedData)));
             echo $a[0]."\t";
             echo $a[1]."\n";     
         }
         echo "Checked Lists:\n";
         foreach ($csvchecked as $csvCheckedData)
         {
             $b = str_replace("\n","",split(";",($csvCheckedData)));
             echo $b[0]."\t";
             echo $b[1]."\n";
         }
         die ();
    }
    
    function divisionAction()
    {
        $params = $this->_request->getParams();
		$this->view->title = "Ticket Division";
 
		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
        
        $this->view->id = $params['id'];
        $this->view->type = $params['type'];
        
        $get_ticket = $this->db->select();
        $get_ticket -> from("tickets", array("title", "id", "composer", "participants"));
        $get_ticket -> where("id = ?", $params['id']);
        $get_ticket_array = $this->db->fetchRow($get_ticket);
        
        if(!empty($get_ticket_array))
        {
            $this->view->ticket_array = $get_ticket_array;
            
            $get_tickets_users = $this->db->select();
            $get_tickets_users -> from("tickets_users", array("id as uid", "ticket_id", "user_id", "user_type", "notes", "status", "creator", "workbook"));
            $get_tickets_users -> joinLeft("kpi_tickets", "tickets_users.id = kpi_tickets.tickets_users_id", array("id as tid", "tickets_users_id", "score", "efficiency", "suggestion_hour", "used_time", "difficulty as diff"));
            $get_tickets_users -> where("tickets_users.ticket_id = ?", $params['id']);
            $get_tickets_users_array = $this->db->fetchAll($get_tickets_users);
            
            $users = new Users();
            $kpi_tickets = new KpiTickets();
            $diff_array = $kpi_tickets->DifficultyArray();
            $kpi_workbook = new KpiWorkbook();
            
            foreach($get_tickets_users_array as $user_d_key => $user_d_val)
            {
                $get_tickets_users_array[$user_d_key]['user_id'] = $users -> GetRealName($user_d_val['user_id']);
                $get_tickets_users_array[$user_d_key]['creator'] = $users -> GetRealName($user_d_val['creator']);
                $get_tickets_users_array[$user_d_key]['creator_id'] = $user_d_val['creator'];
                $get_tickets_users_array[$user_d_key]['user_type'] = $users ->UserType($user_d_val['user_type']);
                if($user_d_val['status'])
                {
                    $get_tickets_users_array[$user_d_key]['status_text'] = "<font color='green'>Processing</font>";
                }  else {
                    $get_tickets_users_array[$user_d_key]['status_text'] = "<font color='red'>Pending</font>";
                }
                $get_tickets_users_array[$user_d_key]['diff'] = $diff_array[$user_d_val['diff']];
                $workname = $kpi_workbook->GetOne($user_d_val['workbook']);
                $get_tickets_users_array[$user_d_key]['workbook'] = $workname['workname'];
                
            }
            
            $this->view->list = $get_tickets_users_array;
            
            //get difficulty
            $kpi_tickets = new KpiTickets();
            $this->view->diff_array = $kpi_tickets -> DifficultyArray();
            
            //create workbook options
            $workbook_model = new KpiWorkbook();
            $this->view->workbook_top = $workbook_model -> MakeTopLevel();

            $this->view->stype = $users ->UserType();

            $shour_array = array();
            for($n=0;$n<24;$n++)
            {
                if($n < 10)
                {
                    $m = "0".$n;
                }else{
                    $m = $n;
                }
                $shour_array[] = $m;
            }
            
            $smin_array = array(
                "0" => "00",
                "5" => "05",
                "10" => "10",
                "15" => "15",
                "20" => "20",
                "30" => "30",
                "40" => "40",
                "50" => "50"
            );

            $this->view->shour = $shour_array;
            $this->view->smin = $smin_array;

            if('fm' == $params['source'])
            {
                if(!$params['workbook'])
                {
                    $this->view->notice="Workbook is required.";
                    $error = 1;
                }

                if(!$params['staff'])
                {
                    $this->view->notice="Staff is required.";
                    $error = 1;
                }

                if(!$params['stype'])
                {
                    $this->view->notice="Staff type is required.";
                    $error = 1;
                }

                if(!$error)
                {
                    if($params['shour'] < 10)
                    {
                        $shour = "0".$params['shour'];
                    }else{
                        $shour = $params['shour'];
                    }
                    
                    if($params['smin'] < 10)
                    {
                        $smin = "0".$params['smin'];
                    }else{
                        $smin = $params['smin'];
                    }
                    
                    $stime = $shour.":".$smin.":00";
                    
                    //insert to db
                    $tickets_users = new TicketsUsers();
                    $row = $tickets_users->createRow();
                    $row->ticket_id = $params['id'];
                    $row->user_id = $params['staff'];
                    $row->user_type = $params['stype'];
                    $row->notes = $params['notes'];
                    $row->creator = $_SESSION["Zend_Auth"]["storage"]->id;
                    $row->workbook = $params['workbook'];
                    $row->status = 0; //stopped Initially
                    $row->sequence = time();
                    $tickets_users_id = $row->save();

                    $kpi_tickets = new KpiTickets();
                    $row2 = $kpi_tickets->createRow();
                    $row2->tickets_users_id = $tickets_users_id;
                    $row2->suggestion_hour = $stime;
                    $row2->difficulty = $params['diff'];
                    $row2->save();
                    
                    $users_model = new Users();
                    
                    //add user related
                    $tickets_model = new Tickets();
                    $tickets_model ->UserRelated(1, $params['id'], $users_model->MakeString(array($params['staff'])));

                    //send email
                    $mail = new Mail();
                    $mail->url = "index/view/type/".$params['type']."/id/".$get_ticket_array['id'];
                    $mail->ticket_title = $get_ticket_array['title'];
                    $mail->staff = $users->GetRealName($params['staff']);
                    $mail->user_type = $users->UserType($params['stype']);
                    $mail->mail_contents = $mail->ContentsTemplate(7);
                    $mail->mail_subject = $users->UserType($params['stype'])." ".$users->GetRealName($params['staff'])." has been assigned to the ticket: ".$get_ticket_array['title'];
                    
                    //create TO
                    $to_list = $users->GetUserIdArray($get_ticket_array['composer']);
                    
                    if($get_ticket_array['participants'])
                    {
                        $to_list = array_merge($to_list, $users->GetUserIdArray($get_ticket_array['participants']));
                    }
                    
                    $tickets_users = new TicketsUsers();
                    $staff_array = $tickets_users->GetUserArray($get_ticket_array['id']);
                    
                    if(!empty($staff_array))
                    {
                        $to_list = array_merge($to_list, $staff_array);
                    }
                    $to_list = array_unique($to_list);
                    
                    $mail->to = $users -> MakeMailUserList($to_list);
                    $mail->cc = $users -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));
                    
                    //send
                    $mail->Send();

                    //redirect, depends on the initial status
                    $menu = new Menu();
                    $this->_redirect('index/division/type/'.$params['type'].'/id/'.$params['id']);
                }
            }
        }else
        {
            echo "Ticket ID Invalid.";die;
        }
        
    }
    
    function divisionEditAction()
    {
        $params = $this->_request->getParams();
		$this->view->title = "Ticket Division Edit";
 
		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
        
        $this->view->tid = $params['tid'];
        $this->view->kid = $params['kid'];
        $this->view->type = $params['type'];
        
        if($params['kid'])
        {
            $kpi_tickets = new KpiTickets();
            $get_row = $kpi_tickets->fetchRow("id='".$params['kid']."'");
            
            if(count($get_row))
            {
                $t = explode(":", $get_row['suggestion_hour']);
                
                $shour_array = array();
                for($n=0;$n<24;$n++)
                {
                    if($n < 10)
                    {
                        $m = "0".$n;
                    }else{
                        $m = $n;
                    }
                    $shour_array[] = $m;
                }

                $smin_array = array(
                    "0" => "00",
                    "5" => "05",
                    "10" => "10",
                    "15" => "15",
                    "20" => "20",
                    "30" => "30",
                    "40" => "40",
                    "50" => "50"
                );

                $this->view->shour = $shour_array;
                $this->view->smin = $smin_array;
                $this->view->shour_selected = $t[0];
                $this->view->smin_selected = $t[1];
                
                if('fm' == $params['source'])
                {
                    if(!$error)
                    {
                        if($params['shour'] < 10)
                        {
                            $shour = "0".$params['shour'];
                        }else{
                            $shour = $params['shour'];
                        }

                        if($params['smin'] < 10)
                        {
                            $smin = "0".$params['smin'];
                        }else{
                            $smin = $params['smin'];
                        }

                        $stime = $shour.":".$smin.":00";

                        //update db
                        $kpi_tickets = new KpiTickets();
                        $row = $kpi_tickets->fetchRow("id='".$params['kid']."'");
                        $row->suggestion_hour = $stime;
                        $row->save();
                        
                        //redirect, depends on the initial status
                        $menu = new Menu();
                        $this->_redirect('index/division/type/'.$params['type'].'/id/'.$params['tid']);
                    }
                }
            }
        }else{
            echo "Invalid Actin.";
            die;
        }
    }

    function getWorkbookAction()
    {
        $params = $this->_request->getParams();
        	
        $kpi_workbook = new KpiWorkbook();
        
		$data = $kpi_workbook ->BuildTree($params['workbookID']);
			
        if(!empty($data))
		{
			foreach($data as $d)
            {
                foreach($d as $d_key => $d_val)
                {
                    $s .= '<option value="'.$d_key.'">'.$d_val.'</option>\n';
                }
            }
			
			echo $s;
			
        	die;
        }
    }

    function getStaffAction()
    {
        $params = $this->_request->getParams();
        
        $kpi_workbook_user = new KpiWorkbookUser();
        $user = new Users();
		$data = $kpi_workbook_user -> GetUser($params['workID']);
		
        if(!empty($data))
		{
			$s = '<option value="">Choose..</option>\n';
            
            foreach($data as $d)
            {
                $s .= '<option value="'.$d['user_id'].'">'.$user->GetRealName($d['user_id']).'</option>\n';
            }
			
			echo $s;
        }
        
        die;
			
    }
    
    function statusTriggerAction()
    {
        $params = $this->_request->getParams();
        $tickets_users = new TicketsUsers();
        $tickets_users ->UpdateStatus($params['uid'], $params['saction']);
        
        $this->_redirect("index/view/type/".$params['type']."/id/".$params['id']);
    }
    
    function delStaffAction()
    {
        $params = $this->_request->getParams();
        
        $tickets_users = new TicketsUsers();
        $tu_info = $tickets_users->fetchRow("id = '".$params['uid']."'");
        
        $tickets_model = new Tickets();
        $ticket_info = $tickets_model->fetchRow("id = '".$tu_info['ticket_id']."'");
        
        //delete
        $tickets_users->delete("id = '".$params['uid']."'");
        
        $users = new Users();
        
        //send mail
        $mail = new Mail();
        $mail->url = "index/view/id/".$ticket_info['id'];
        $mail->ticket_title = $ticket_info['title'];
        $mail->staff = $users->GetRealName($tu_info['user_id']);
        $mail->user_type = $users->UserType($tu_info['user_type']);
        $mail->mail_contents = $mail->ContentsTemplate(8);
        $mail->mail_subject = $users->UserType($tu_info['user_type'])." ".$users->GetRealName($tu_info['user_id'])." has been removed from the ticket: ".$ticket_info['title'];
        
        //create TO
        $to_list = $users->GetUserIdArray($ticket_info['composer']);
                    
        if($ticket_info['participants'])
        {
             $to_list = array_merge($to_list, $users->GetUserIdArray($ticket_info['participants']));
        }
                    
        $tickets_users = new TicketsUsers();
        $staff_array = $tickets_users->GetUserArray($ticket_info['id']);
                    
        if(!empty($staff_array))
        {
             $to_list = array_merge($to_list, $staff_array);
        }
        $to_list = array_unique($to_list);
                    
        $mail->to = $users -> MakeMailUserList($to_list);
        $mail->cc = $users -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));
                    
        //send
        $mail->Send();
        
        $this->_redirect("index/division/type/".$params['type']."/id/".$params['id']);
    }
    
    function recentUsedTimeAction()
    {
        $this->_helper->layout->disableLayout();
        $params = $this->_request->getParams();
        $kpi_time = new KpiTicketsTime();
        $this->view->rt = $kpi_time->GetRecentTime($params['uid']);
    }
    
    function fastTicketRegisterAction()
    {
        $params = $this->_request->getParams();
		$this->view->title = "Fast Ticket Register";
 
		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
           
        //get difficulty
        $kpi_tickets = new KpiTickets();
        $this->view->diff_array = $kpi_tickets -> DifficultyArray();
            
        //create workbook options
        $workbook_model = new KpiWorkbook();
        $this->view->workbook_top = $workbook_model -> MakeTopLevel();
        
        $users = new Users();
        $this->view->stype = $users ->UserType();
        
        //create category options
		$category_model = new Category();
		$category_array = $category_model -> BuildTree();
        
        $new_category_array[''] = "Choose..";
		foreach($category_array as $category_val)
		{
			foreach($category_val as $category_val_key => $category_val_val)
            {
                $new_category_array[$category_val_key] = $category_val_val;
            }
		}
        $this->view->tcat_array = $new_category_array;

        $shour_array = array(
            "5" => "5 mins",
            "10" => "10 mins",
            "15" => "15 mins",
            "20" => "20 mins",
            "30" => "30 mins",
            "40" => "40 mins",
            "50" => "50 mins",
            "60" => "1 hour",
            "120" => "2 hours",
            "180" => "3 hours",
            "240" => "4 hours",
            "360" => "6 hours",
            "480" => "8 hours"
        );

        $this->view->shour = $shour_array;

        if('fm' == $params['source'])
        {
            if(!$params['workbook'])
            {
                $this->view->notice="Workbook is required.";
                $error = 1;
            }

            if(!$params['staff'])
            {
                $this->view->notice="Staff is required.";
                $error = 1;
            }

            if(!$params['stype'])
            {
                $this->view->notice="Staff type is required.";
                $error = 1;
            }

            if(!$error)
            {
                switch($params['shour'])
                {
                    case 5:
                        $shour = "00:05:00";
                        break;
                    case 10:
                        $shour = "00:10:00";
                        break;
                    case 15:
                        $shour = "00:15:00";
                        break;
                    case 20:
                        $shour = "00:20:00";
                        break;
                    case 30:
                        $shour = "00:30:00";
                        break;
                    case 40:
                        $shour = "00:40:00";
                        break;
                    case 50:
                        $shour = "00:50:00";
                        break;
                    case 60:
                        $shour = "01:00:00";
                        break;
                    case 120:
                        $shour = "02:00:00";
                        break;
                    case 180:
                        $shour = "03:00:00";
                        break;
                    case 240:
                        $shour = "04:00:00";
                        break;
                    case 360:
                        $shour = "06:00:00";
                        break;
                    case 480:
                        $shour = "08:00:00";
                        break;
                    default:
                        $shour = "00:00:00";
                        break;
                }

                //insert to db
                $fast_ticket = new FastTicket();
                $row = $fast_ticket->createRow();
                $row->creator = $_SESSION["Zend_Auth"]["storage"]->id;
                $row->tname = $params['tname'];
                $row->tcontents = $params['tcontents'];
                $row->ticket_category = $params['tcat'];
                $row->difficulty = $params['diff'];
                $row->workbook = $params['workbook'];
                $row->staff = $params['staff'];
                $row->staff_type = $params['stype'];
                $row->refhour = $shour;
                $t_id = $row->save();
                
                if($t_id)
                {
                    $this->_redirect('index/fast-ticket');
                }else{
                    echo "Error when inserting.";
                    die;
                }
                

                    //redirect
                    $menu = new Menu();
                    $this->_redirect('index/fast-ticket');
            }
        }
    }
    
    function fastTicketAction()
    {
        $params = $this->_request->getParams();
		$this->view->title = "Create Fast Ticket";
 
		$menu = new Menu();
		$this->view->menu = $menu -> GetTicketMenu($params['type']);
        
        $fast_ticket = new FastTicket();
        $this->view->get_tree = $fast_ticket->GetTree($_SESSION["Zend_Auth"]["storage"]->id);
        
        if('fm' == $params['source'])
        {
            if(!$params['ttree'])
            {
                $this->view->notice="Ticket template is required.";
                $error = 1;
            }

            if(!$params['ttitle'])
            {
                $this->view->notice="Ticket title is required.";
                $error = 1;
            }

            if(!$error)
            {
				$get_template = $fast_ticket->fetchRow("id='".$params['ttree']."'");
                $now = date("Y-m-d H:i:s");
                
                if(count($get_template))
                {
                    //insert ticket
                    $tickets = new Tickets();
                    $users = new users();

                    $row = $tickets->createRow();

                    $row->category = $get_template['ticket_category'];
                    $row->title = $params['ttitle'];
                    $row->contents = $get_template['tcontents'];
                    $row->status = 2; //processing
                    $row->created_date = $now;
                    $row->processing_date = $now;
                    $row->priority = 1;
                    $row->composer = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
                    $row->update_who = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
                    $row->update_when = $now;
                    $row->user_related = $users->MakeString(array($get_template['staff']));

                    $pt = $row->save();

                    //add division
                    $tickets_users = new TicketsUsers();
                    $row2 = $tickets_users->createRow();
                    $row2->ticket_id = $pt;
                    $row2->user_id = $get_template['staff'];
                    $row2->user_type = $get_template['staff_type'];
                    $row2->creator = $_SESSION["Zend_Auth"]["storage"]->id;
                    $row2->workbook = $get_template['workbook'];
                    $row2->status = 0; //stopped Initially
                    $row2->sequence = time();
                    $tickets_users_id = $row2->save();

                    $kpi_tickets = new KpiTickets();
                    $row3 = $kpi_tickets->createRow();
                    $row3->tickets_users_id = $tickets_users_id;
                    $row3->suggestion_hour = $get_template['refhour'];
                    $row3->used_time = "00:00:01";
                    $row3->difficulty = $get_template['difficulty'];
                    $row3->save();
                    
                    $kpi_tickets_time = new KpiTicketsTime();
                    $row4 = $kpi_tickets_time->createRow();
                    $row4->tickets_users_id = $tickets_users_id;
                    $row4->event_time = time();
                    $row4->action_type = 1; //start
                    $row4->save();
                    $row5 = $kpi_tickets_time->createRow();
                    $row5->tickets_users_id = $tickets_users_id;
                    $row5->event_time = time() + 1;
                    $row5->action_type = 0; //stop
                    $row5->save();

                    //redirect, depends on the initial status
                    $menu = new Menu();
                    $this->_redirect('index/view/type/2/id/'.$pt);
                }else{
                    echo "Template Error.";
                    die;
                }
            }
        }
    }
    
    function deleteFastTicketAction()
    {
        $params = $this->_request->getParams();
        
        if($params['id'])
        {
            $fast_ticket = new FastTicket();
            $data = $fast_ticket->fetchRow("id='".$params['id']."'");
            
            if($data['creator'] == $_SESSION["Zend_Auth"]["storage"]->id)
            {
                $where = $fast_ticket->getAdapter()->quoteInto("id = ?", $params['id']);
                $fast_ticket->delete($where);
            }else{
                echo "Invalid Action.";
                die;
            }
        }
        
        $this->_redirect("/index/fast-ticket");
    }
}

