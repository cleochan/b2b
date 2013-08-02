<?php

class Forms_ProfileMerchant extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('ProfileMerchant');
        
        $password = new Zend_Form_Element_Password('password');
		$password -> setDecorators(array(array('ViewHelper')));
        
        $password_r = new Zend_Form_Element_Password('password_r');
		$password_r -> setDecorators(array(array('ViewHelper')));
               
        $address = new Zend_Form_Element_Text('address');
		$address -> setDecorators(array(array('ViewHelper')));
                
        $post_code = new Zend_Form_Element_Text('post_code');
		$post_code -> setDecorators(array(array('ViewHelper')));
                
        $suburb = new Zend_Form_Element_Text('suburb');
		$suburb -> setDecorators(array(array('ViewHelper')));
                
        $state = new Zend_Form_Element_Text('state');
		$state -> setDecorators(array(array('ViewHelper')));
                
        $contact_name = new Zend_Form_Element_Text('contact_name');
		$contact_name -> setDecorators(array(array('ViewHelper')));
                        
        $contact_phone = new Zend_Form_Element_Text('contact_phone');
		$contact_phone -> setDecorators(array(array('ViewHelper')));
        
		$submitx = new Zend_Form_Element_Submit('submitx');
		$submitx -> setDecorators(array(array('ViewHelper')));

		$this -> addElements(array(
                                $password,
                                $password_r,
                                $user_status,
                                $contact_name,
                                $address,
                                $post_code,
                                $suburb,
                                $state,
                                $contact_phone,
								$submitx
			)
		);
	}
}
