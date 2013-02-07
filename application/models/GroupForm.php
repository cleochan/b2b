<?php

class GroupForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Group');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));		
		
		$gname = new Zend_Form_Element_Text('gname');
		$gname -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		//create status options
		$users = new Users();
		$users_array = $users -> MakeList();
		$users_options = array();
		
		foreach($users_array as $uay)
		{
			$uay_id = $uay['id'];
			$uay_name = $uay['realname'];
			$users_options[$uay_id] = $uay_name;
			unset($uay_id);
			unset($uay_name);
		}	
		
		$members = new Zend_Form_Element_MultiCheckbox('members');
		$members -> addMultiOptions($users_options)
				 -> setDecorators(array(array('ViewHelper'),));
				 //-> setValue(true);

		$submit = new Zend_Form_Element_Submit('submit');
		$submit -> setDecorators(array(array('ViewHelper'),));


		$this -> addElements(
								array(
										$id,
										$gname,
										$members,
										$submit
									)
							);
	}
}
