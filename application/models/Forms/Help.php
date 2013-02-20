<?php

class Forms_Help extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Help');
		
		$helpdesk_id = new Zend_Form_Element_Hidden('helpdesk_id');
		$helpdesk_id -> setDecorators(array(array('ViewHelper'),));
		
                $get_category = new Databases_Tables_HelpdeskCategory();
                $category_array = $get_category->GetCategory();
                
                $category = new Zend_Form_Element_Select('category');
		$category -> setDecorators(array(array('ViewHelper'),))
				 -> addMultiOptions($category_array)
				 -> setRequired(True)
                                 -> addValidator('NotEmpty');
        
                $h_subject = new Zend_Form_Element_Text('h_subject');
		$h_subject -> setDecorators(array(array('ViewHelper'),))
			   -> setRequired(True)
			   -> setAttrib('size',100)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
               
               $h_contents = new Zend_Form_Element_Textarea('h_contents');
		$h_contents -> setDecorators(array(array('ViewHelper'),))
		          -> addFilter('StringTrim')
                  -> setAttribs(array('style' => 'width:800px;height:300px;visibility:hidden;'));

		$submitx = new Zend_Form_Element_Submit('submitx');
		$submitx -> setDecorators(array(array('ViewHelper'),));

		$this -> addElements(
						array(
								$helpdesk_id,
                                                                $category,
                                                                $h_subject,
                                                                $h_contents,
								$submitx
							)
						);
	}
}
