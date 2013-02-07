<?php

class UserForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('User');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));
		
		$passwd = new Zend_Form_Element_Password('passwd');
		$passwd -> setDecorators(array(array('ViewHelper'),))
			    -> setAttrib('size',30);
		
		$passwd_r = new Zend_Form_Element_Password('passwd_r');
		$passwd_r -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',30);
		
		//create dept options
		$dp = new Departments();
		$dp_array = $dp -> GetArray();
		$new_dp_array[''] = "Choose";
		foreach($dp_array as $dp_key => $dp_val)
		{
			$new_dp_array[$dp_key] = $dp_val;
		}
		$department = new Zend_Form_Element_Select('department');
		$department -> setDecorators(array(array('ViewHelper'),))
				 	-> addMultiOptions($new_dp_array)
				 	-> setRequired(True)
			     	-> addValidator('NotEmpty');			
		
		$username = new Zend_Form_Element_Text('username');
		$username -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$realname = new Zend_Form_Element_Text('realname');
		$realname -> setDecorators(array(array('ViewHelper'),))
				  -> setRequired(True)
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$skype = new Zend_Form_Element_Text('skype');
		$skype -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$first_name = new Zend_Form_Element_Text('first_name');
		$first_name -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$last_name = new Zend_Form_Element_Text('last_name');
		$last_name -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$team_title = new Zend_Form_Element_Text('team_title');
		$team_title -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$realname = new Zend_Form_Element_Text('realname');
		$realname -> setDecorators(array(array('ViewHelper'),))
				  -> setRequired(True)
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$supervisor = new Zend_Form_Element_Text('supervisor');
		$supervisor -> setDecorators(array(array('ViewHelper'),))
			   		-> setAttrib('size',50)
			   		-> addFilter('StripTags')
		       		-> addFilter('StringTrim');
		
		//create status options
		$st_array = array(
						 	0 => "Inactive",
						 	1 => "Active"
						 );
		$status = new Zend_Form_Element_Radio('status');
		$status -> setDecorators(array(array('ViewHelper'),))
				-> addMultiOptions($st_array)
				-> setSeparator(' ')
				-> setRequired(True)
				-> setValue(1)
			    -> addValidator('NotEmpty');
		
		//create default list options
		$dl_array = array(
						 	0 => "Ticket",
						 	1 => "Task"
						 );
		$default_list = new Zend_Form_Element_Radio('default_list');
		$default_list -> setDecorators(array(array('ViewHelper'),))
				-> addMultiOptions($dl_array)
				-> setSeparator(' ')
				-> setRequired(True)
				-> setValue(0)
			    -> addValidator('NotEmpty');
		
		$new_alert_array = array(1=>"Yes", 0=>"No");
		$new_alert = new Zend_Form_Element_Radio('new_alert');
		$new_alert -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($new_alert_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
				   -> setValue(0)
			       -> addValidator('NotEmpty');
		
		$reminder_array = array(1=>"ON", 0=>"OFF");
		$reminder = new Zend_Form_Element_Radio('reminder');
		$reminder -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($reminder_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
				   -> setValue(1)
			       -> addValidator('NotEmpty');
		
		//create view ticket level options
		$users = new Users();
		$tl_array = $users -> LevelViewTicketOptions();
		$level_view_tickets = new Zend_Form_Element_Radio('level_view_tickets');
		$level_view_tickets -> setDecorators(array(array('ViewHelper'),))
							-> addMultiOptions($tl_array)
							-> setSeparator(' ')
							-> setRequired(True)
							-> setValue(1)
			    			-> addValidator('NotEmpty');
		
		//create mgt level options
		$ml_array = $users -> LevelMgtOptions();
		$level_mgt = new Zend_Form_Element_Radio('level_mgt');
		$level_mgt -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($ml_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
				   -> setValue(1)
			       -> addValidator('NotEmpty');
		
		$email = new Zend_Form_Element_Text('email');
		$email -> setDecorators(array(array('ViewHelper'),))
			   -> setAttrib('size',50)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit -> setDecorators(array(array('ViewHelper'),));


		$this -> addElements(
								array(
										$id,
										$passwd,
										$passwd_r,
										$department,
										$username,
                                        $skype,
                                        $first_name,
                                        $last_name,
                                        $team_title,
										$realname,
										$supervisor,
										$status,
										$new_alert,
										$level_view_tickets,
										$level_mgt,
										$email,
										$reminder,
                                        $default_list,
										$submit
									)
							);
	}
}
