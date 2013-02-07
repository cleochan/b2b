<?php

class ProfileForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Profile');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));
		
		$passwd = new Zend_Form_Element_Password('passwd');
		$passwd -> setDecorators(array(array('ViewHelper'),));
		
		$passwd_r = new Zend_Form_Element_Password('passwd_r');
		$passwd_r -> setDecorators(array(array('ViewHelper'),));
		
		$realname = new Zend_Form_Element_Text('realname');
		$realname -> setDecorators(array(array('ViewHelper'),))
			   	  -> setRequired(True)
			   	  -> setAttrib('size',30)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$email = new Zend_Form_Element_Text('email');
		$email -> setDecorators(array(array('ViewHelper'),))
			   -> setAttrib('size',50)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
		
		$skype = new Zend_Form_Element_Text('skype');
		$skype -> setDecorators(array(array('ViewHelper'),))
			   -> setAttrib('size',20)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
		
		$new_alert_array = array(1=>"Yes", 0=>"No");
		$new_alert = new Zend_Form_Element_Radio('new_alert');
		$new_alert -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($new_alert_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
			       -> addValidator('NotEmpty');
		
		$reminder_array = array(1=>"ON", 0=>"OFF");
		$reminder = new Zend_Form_Element_Radio('reminder');
		$reminder -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($reminder_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
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

		$submit = new Zend_Form_Element_Submit('submit');
		$submit -> setDecorators(array(array('ViewHelper'),));


		$this -> addElements(
								array(
										$id,
										$passwd,
										$passwd_r,
										$realname,
										$email,
										$skype,
										$new_alert,
										$reminder,
                                        $default_list,
										$submit
									)
							);
	}
}
