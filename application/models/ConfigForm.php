<?php

class ConfigForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Config');
		
		$sys_ver = new Zend_Form_Element_Text('sys_ver');
		$sys_ver -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$sys_title = new Zend_Form_Element_Text('sys_title');
		$sys_title -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$sys_path = new Zend_Form_Element_Text('sys_path');
		$sys_path -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$smtp_server = new Zend_Form_Element_Text('smtp_server');
		$smtp_server -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$smtp_account = new Zend_Form_Element_Text('smtp_account');
		$smtp_account -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$smtp_pw = new Zend_Form_Element_Text('smtp_pw');
		$smtp_pw -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$sender_account = new Zend_Form_Element_Text('sender_account');
		$sender_account -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');
		
		$sender_name = new Zend_Form_Element_Text('sender_name');
		$sender_name -> setDecorators(array(array('ViewHelper'),))
			   	  -> setAttrib('size',50)
			   	  -> addFilter('StripTags')
		       	  -> addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit -> setDecorators(array(array('ViewHelper'),));


		$this -> addElements(
								array(
										$sys_ver,
										$sys_title,
										$sys_path,
										$smtp_server,
										$smtp_account,
										$smtp_pw,
										$sender_account,
										$sender_name,
										$submit
									)
							);
	}
}
