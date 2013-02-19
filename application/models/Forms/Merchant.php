<?php

class Forms_Merchant extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Merchant');
		
		$user_id = new Zend_Form_Element_Hidden('user_id');
		$user_id -> setDecorators(array(array('ViewHelper'),));
		
		$email = new Zend_Form_Element_Text('email');
		$email -> setDecorators(array(array('ViewHelper'),));
        
                $password = new Zend_Form_Element_Password('password');
		$password -> setDecorators(array(array('ViewHelper'),));
        
                $password_r = new Zend_Form_Element_Password('password_r');
		$password_r -> setDecorators(array(array('ViewHelper'),));
        
                //create status radio
		$user_status_array = array(1=>"Active", 0=>"Inactive");
		$user_status = new Zend_Form_Element_Radio('user_status');
		$user_status    -> setDecorators(array(array('ViewHelper'),))
                                        -> addMultiOptions($user_status_array)
                                        -> setSeparator(' ')
                                        -> setRequired(True)
                                        -> setValue(1)
                                        -> addValidator('NotEmpty');
        
		$company = new Zend_Form_Element_Text('company');
		$company -> setDecorators(array(array('ViewHelper'),));
        
                $contact_name = new Zend_Form_Element_Text('contact_name');
		$contact_name -> setDecorators(array(array('ViewHelper'),));
        
                $contact_phone = new Zend_Form_Element_Text('contact_phone');
		$contact_phone -> setDecorators(array(array('ViewHelper'),));
        
                $credit = new Zend_Form_Element_Text('credit');
		$credit -> setDecorators(array(array('ViewHelper'),));

		$submitx = new Zend_Form_Element_Submit('submitx');
		$submitx -> setDecorators(array(array('ViewHelper'),));

		$this -> addElements(
						array(
								$user_id,
                                                                $email,
                                                                $password,
                                                                $password_r,
                                                                $user_status,
                                                                $company,
                                                                $contact_name,
                                                                $contact_phone,
                                                                $credit,
								$submitx
							)
						);
	}
}
