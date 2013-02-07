<?php

class ProjectForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Project');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));		
		
		$project_name = new Zend_Form_Element_Text('project_name');
		$project_name -> setDecorators(array(array('ViewHelper'),))
			      -> setAttrib('size',30)
			      -> addFilter('StripTags')
		          -> addFilter('StringTrim');
		
		$status_array = array(1=>"Active", 0=>"Inactive");
		$status = new Zend_Form_Element_Radio('status');
		$status -> setDecorators(array(array('ViewHelper'),))
				   -> addMultiOptions($status_array)
				   -> setSeparator(' ')
				   -> setRequired(True)
				   -> setValue(1)
			       -> addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('submit');
		$submit -> setDecorators(array(array('ViewHelper'),));

		$this -> addElements(
								array(
										$id,
										$project_name,
										$status,
										$submit
									)
							);
	}
}
