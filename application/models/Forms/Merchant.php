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
                
                
                //create flat_rate_shipping radio
		$flat_rate_shipping_array = array(1=>"On", 0=>"Off");
		$flat_rate_shipping = new Zend_Form_Element_Radio('flat_rate_shipping');
		$flat_rate_shipping    -> setDecorators(array(array('ViewHelper'),))
                                        -> addMultiOptions($flat_rate_shipping_array)
                                        -> setSeparator(' ')
                                        -> setRequired(True)
                                        -> setValue(1)
                                        -> addValidator('NotEmpty');

                $week_array             =   array(
                                                1   =>  'Monday',
                                                2   =>  'Tuesday',
                                                3   =>  'Wednesday',
                                                4   =>  'Thursday',
                                                5   =>  'Friday',
                                                6   =>  'Saturday',
                                                7   =>  'Sunday',
                                            );
                $invoice_value_1        =   new Zend_Form_Element_Select('invoice_value_1');
                $invoice_value_1        ->  setMultiOptions($week_array)
                                        ->  setRequired(true)
                                        ->  addValidator('NotEmpty', true);
                
                $invoice_value_2        =   new Zend_Form_Element_Text('invoice_value_2');
                
                $invoice_type_array     =   array(1=>'By Week', 2=>'By Month');
                $invoice_type           =   new Zend_Form_Element_Radio('invoice_type');
                $invoice_type           ->  setDecorators(array(array('ViewHelper'),))
                                        ->  addMultiOptions($invoice_type_array)
                                        ->  setSeparator(' ')
                                        ->  setRequired(True)
                                        ->  setValue(1)
                                        ->  addValidator('NotEmpty');
		$company = new Zend_Form_Element_Text('company');
		$company -> setDecorators(array(array('ViewHelper'),));
        
                $address = new Zend_Form_Element_Text('address');
		$address -> setDecorators(array(array('ViewHelper'),));
                
                $post_code = new Zend_Form_Element_Text('post_code');
		$post_code -> setDecorators(array(array('ViewHelper'),));
                
                $suburb = new Zend_Form_Element_Text('suburb');
		$suburb -> setDecorators(array(array('ViewHelper'),));
                
                $state = new Zend_Form_Element_Text('state');
		$state -> setDecorators(array(array('ViewHelper'),));
                
                $contact_name = new Zend_Form_Element_Text('contact_name');
		$contact_name -> setDecorators(array(array('ViewHelper'),));
                
        
                $contact_phone = new Zend_Form_Element_Text('contact_phone');
		$contact_phone -> setDecorators(array(array('ViewHelper'),));
        
                $credit = new Zend_Form_Element_Text('credit');
		$credit -> setDecorators(array(array('ViewHelper'),));
        
                $discount = new Zend_Form_Element_Text('discount');
		$discount -> setDecorators(array(array('ViewHelper'),));

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
                                                                $address,
                                                                $post_code,
                                                                $suburb,
                                                                $state,
                                                                $contact_phone,
                                                                $credit,
                                                                $discount,
								$submitx,
                                                                $flat_rate_shipping,
                                                                $invoice_type,
                                                                $invoice_value_1,
                                                                $invoice_value_2,
							)
						);
	}
}
