<?php

class ProjectsController extends Zend_Controller_Action
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
		$this->view->title = "Projects Management";
		$params = $this->_request->getParams();
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetProjectsMenu($this->getRequest()->getActionName());
		
		$projects = new Projects();
		$projects_list = $projects -> MakeList();
		
		$this->view->list = $projects_list;
    }
    
    function addAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Add Project";
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetProjectsMenu($this->getRequest()->getActionName());
		
		$form = new ProjectForm();
		$form->submit->setLabel('Create Project');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start

				//new projects
				$check_project_string = new Projects();
				
				//password check
				if(!$form->getValue('project_name'))
				{
					$this->view->notice="Project name is required.";
					$form->populate($formData);
					$error = 1;
				}
                
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					//insert to db
					$users = new Projects();
				
					$row = $users->createRow();
					
					$row->project_name = $form->getValue('project_name');
					$row->status = $form->getValue('status');
					$row->creator = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
					$row->create_time = date("Y-m-d H:i:s");
				
					$row->save();
					$this->_redirect('projects/index');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!$formData['project_name'])
				{
					$this->view->notice="Project name is required.";
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
    
    function editAction()
    {
		$params = $this->_request->getParams();
		$this->view->title = "Edit Project";
		
		$menu = new Menu();
		$this->view->menu = $menu ->GetProjectsMenu($this->getRequest()->getActionName());
		
        $projects = new Projects();

		$form = new ProjectForm();
		$form->submit->setLabel('Update');
		$this->view->form = $form;
		
		if($this->_request->isPost()){
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				$form->getValues();
				
				///////////////////////////////////////////////////////////
				//check valid start

				//new projects
				$check_project_string = new Projects();
				
				//password check
				if(!trim($form->getValue('project_name')))
				{
					$this->view->notice="Project name is required.";
					$form->populate($formData);
					$error = 1;
				}
                
				//check valid end
				///////////////////////////////////////////////////////////
								
				if(!$error)
				{
					//insert to db
                    $row = $projects->fetchRow('id = "'.$form->getValue('id').'"');

					$row->project_name = $form->getValue('project_name');
					$row->status = $form->getValue('status');
					$row->create_time = date("Y-m-d H:i:s");
				
					$row->save();
					//unset session
					$theid = $form->getValue('id');
                    unset($_SESSION['project_contents'][$theid]);					
                    //redirect
					$this->_redirect('projects/index');
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				if(!trim($formData['project_name']))
				{
					$this->view->notice="Project name is required.";
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
			if($_SESSION['project_contents'][$theid])
			{
				$this->view->data = $_SESSION['project_contents'][$theid];
			}
		}else
		{
            if($params['id'])
			{
				$theid = $params['id'];
				$project = $projects->fetchRow('id="'.$params['id'].'"');
				$form->populate($project->toArray());
				$this->view->data = $project;
				$_SESSION['project_contents'][$theid] = $project;
			}
		}
    }

}
