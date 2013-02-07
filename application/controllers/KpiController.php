<?php

class KpiController extends Zend_Controller_Action
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
		$this->view->title = "Non-review Tasks";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetKpiMenu($this->getRequest()->getActionName());
		
		$kpi = new Kpi();
        
        //get user list
        $user_model = new Users();
		$user_array = $user_model -> GetStaffInfoArray($_SESSION['search_ticket_users_current']);
        
        $this->view->user_array = $user_array;
        
        if($params['user'])
        {
            $kpi -> user = $params['user'];
            $this->view->user = $params['user'];
        }
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $kpi -> page = $current_page;
        
        $kpi_list = $kpi ->PushListData(1); //activate
        
		
		$this->view->list = $kpi_list;
        
        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;
        
        $tickets -> page = $this->view->older;
        $list = $kpi -> PushListData(1);
        //print_r($list);die;
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $tickets -> page = $this->view->newer;
                $list = $kpi -> PushListData(1);
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }
        
        //score drop down
        $this->view->score_list = $kpi ->ScoreArray();
    }
    
    function indexUpdateAction()
    {
        $params = $this->_request->getParams();
        
        if($params['score'])
        {
            $kpi_tickets = new KpiTickets();
            
            foreach($params['score'] as $s_key => $s_val)
            {
                if($s_val) // not 0 not null
                {
                    $data = $kpi_tickets->fetchRow("id='".$s_key."'");
                    $data->score = $s_val;
                    $data->save();
                }
            }
        }
        
        $this->_redirect('/kpi/index/user/'.$params['user']);
    }
	
    function inactivateAction()
    {
		$this->view->title = "Reviewed Tasks";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetKpiMenu($this->getRequest()->getActionName());
		
		$kpi = new Kpi();
        
        //get user list
        $user_model = new Users();
		$user_array = $user_model -> GetStaffInfoArray($_SESSION['search_ticket_users_current']);
        
        $this->view->user_array = $user_array;
        
        if($params['user'])
        {
            $kpi -> user = $params['user'];
            $this->view->user = $params['user'];
        }
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $kpi -> page = $current_page;
        
        $kpi_list = $kpi ->PushListData(2); //activate
        
		
		$this->view->list = $kpi_list;
        
        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;
        
        $tickets -> page = $this->view->older;
        $list = $kpi -> PushListData(2);
        //print_r($list);die;
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $tickets -> page = $this->view->newer;
                $list = $kpi -> PushListData(2);
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }
        
        //score drop down
        $this->view->score_list = $kpi ->ScoreArray();
    }
    
    function inactivateUpdateAction()
    {
        $params = $this->_request->getParams();
        
        if($params['score'])
        {
            $kpi_tickets = new KpiTickets();
            
            foreach($params['score'] as $s_key => $s_val)
            {
                if($s_val) // not 0 not null
                {
                    $data = $kpi_tickets->fetchRow("id='".$s_key."'");
                    $data->score = $s_val;
                    $data->save();
                }
            }
        }
        
        $this->_redirect('/kpi/inactivate/user/'.$params['user']);
    }
    
    function reviewAction()
    {
        $this->view->title = "My KPI Report";
        $params = $this->_request->getParams();
        
        $menu = new Menu();
		$this->view->menu = $menu ->GetKpiMenu($this->getRequest()->getActionName());
        
        $kpi_model = new Kpi();
        $error = 0; //no error initially
        
        if($params['d_from'] && $params['d_to'])
        {
            if($params['d_user'] && "all" != $params['d_user'])
            {
                //check valid
                if($kpi_model->IsValidViewKpi($_SESSION["Zend_Auth"]["storage"]->id, $params['d_user']))
                {
                    $terminal_user = $params['d_user'];
                }else
                {
                    $error = 2; //no permission
                }
            }elseif("all" == $params['d_user']){
                $terminal_user = "all";
            }else{
                $terminal_user = $_SESSION["Zend_Auth"]["storage"]->id;
            }
        }else
        {
            $error = 1; //miss from or to
        }
        
        if($error)
        {
            $this->_redirect("/kpi/review-search/error/".$error);
        }else{
            $this->view->terminal_user = $terminal_user;
            
            //initical params
            $kpi_model->date_from = $params['d_from'];
            $kpi_model->date_to = $params['d_to'];
            
            if($terminal_user && "all" != $terminal_user)
            {
                //display
                $this->view->staff_info = $kpi_model->GetStaffInfo($terminal_user);
                $this->view->user_id = $terminal_user;
                $this->view->start_date = $params['d_from'];
                $this->view->end_date = $params['d_to'];
                $this->view->task_info = $kpi_model->GetTaskInfo($terminal_user);
                $this->view->training_info = $kpi_model->GetTrainingInfo($terminal_user);
            }elseif("all" == $terminal_user){
                $user_model = new Users();
                $user_list = $user_model->GetItTeamMembers(1);
                
                if(!empty($user_list))
                {
                    $loop_user = array();
                    
                    foreach($user_list as $ul)
                    {
                        //display
                        $loop_user[$ul]['staff_info'] = $kpi_model->GetStaffInfo($ul);
                        $loop_user[$ul]['user_id'] = $ul;
                        $loop_user[$ul]['start_date'] = $params['d_from'];
                        $loop_user[$ul]['end_date'] = $params['d_to'];
                        $loop_user[$ul]['task_info'] = $kpi_model->GetTaskInfo($ul);
                        $loop_user[$ul]['training_info'] = $kpi_model->GetTrainingInfo($ul);
                        
                        $this->view->loop_user = $loop_user;
                    }
                }else{
                    echo "No data are found.";die;
                }
            }
        }
    }
    
    function reviewSearchAction()
    {
        $this->view->title = "My KPI Report";
        $params = $this->_request->getParams();
        
        $menu = new Menu();
		$this->view->menu = $menu ->GetKpiMenu($this->getRequest()->getActionName());
        
        $users = new Users();
        $this->view->user_list = $users->GetStaffInfoArray($_SESSION["Zend_Auth"]["storage"]->id);
        
        if(1 == $params['error'])
        {
            $this->view->notice = "Date Range is required.";
        }elseif(2 == $params['error']){
            $this->view->notice = "User Invalid.";
        }else{
            $this->view->notice = "";
        }
    }
    
    function kpiCalculationTaskByHourAction()
    {
        $this->_helper->layout->disableLayout();
        
        $params = $this->_request->getParams();
        
        $this->view->date_from = $params['date_from'];
        $this->view->date_to = $params['date_to'];
        
        $kpi_model = new Kpi();
        
        $kpi_model->user = $params['user'];
        $kpi_model->date_from = $params['date_from'];
        $kpi_model->date_to = $params['date_to'];
        
        //get user name
        $user_model = new Users();
        $this->view->user_name = $user_model->GetRealName($params['user']);
        
        $result = $kpi_model->KpiCalculationTaskByHour();
        
        if(!empty($result))
        {
            $this->view->self_data = $result[1];
            $this->view->team_data = $result[2];
        }else{
            echo "Error001.";
            die;
        }
    }
    
    function kpiCalculationUsageUserAction()
    {
        $this->_helper->layout->disableLayout();
        
        $params = $this->_request->getParams();
        
        $this->view->date_from = $params['date_from'];
        $this->view->date_to = $params['date_to'];
        
        $kpi_model = new Kpi();
        
        $kpi_model->user = $params['user'];
        $kpi_model->date_from = $params['date_from'];
        $kpi_model->date_to = $params['date_to'];
        
        $result = $kpi_model->KpiCalculationUsageUser();
        
        if(!empty($result))
        {
            $this->view->usage = $result['usage'];
            $this->view->diff = $result['diff'];
        }else{
            echo "Error001.";
            die;
        }
    }
    
    function kpiCalculationUsageLeaderAction()
    {
        $this->_helper->layout->disableLayout();
        
        $params = $this->_request->getParams();
        
        $this->view->date_from = $params['date_from'];
        $this->view->date_to = $params['date_to'];
        
        $kpi_model = new Kpi();
        
        $kpi_model->user = $params['user'];
        $kpi_model->date_from = $params['date_from'];
        $kpi_model->date_to = $params['date_to'];
        
        $result = $kpi_model->KpiCalculationUsageLeader();
        
        if(!empty($result))
        {
            $this->view->usage = $result['usage'];
            $this->view->diff = $result['diff'];
        }else{
            echo "Error001.";
            die;
        }
    }
    
    function kpiCalculationUsageAvgAction()
    {
        $this->_helper->layout->disableLayout();
        
        $params = $this->_request->getParams();
        
        $this->view->date_from = $params['date_from'];
        $this->view->date_to = $params['date_to'];
        
        $kpi_model = new Kpi();
        
        $kpi_model->user = $params['user'];
        $kpi_model->date_from = $params['date_from'];
        $kpi_model->date_to = $params['date_to'];
        
        $result = $kpi_model->KpiCalculationUsageAvg();
        
        if(!empty($result))
        {
            $this->view->usage = $result['usage'];
            $this->view->diff = $result['diff'];
        }else{
            echo "Error001.";
            die;
        }
    }
}
