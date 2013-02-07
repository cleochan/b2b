<?php

class TrainingLibraryForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('TrainingLibrary');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));
        
        $title = new Zend_Form_Element_Text('title');
		$title -> setDecorators(array(array('ViewHelper'),))
			   -> setRequired(True)
			   -> setAttrib('size',100)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
        
        $author = new Zend_Form_Element_Text('author');
		$author -> setDecorators(array(array('ViewHelper'),))
                -> setRequired(True)
			    -> setAttrib('size',70)
			    -> addFilter('StripTags')
		        -> addFilter('StringTrim');
        
        //create category options
		$category_model = new TrainingCategory();
		$category_array = $category_model -> GetCategory();
        $new_category_array[''] = "Choose";
		foreach($category_array as $category_key => $category_val)
		{
			$new_category_array[$category_key] = $category_val;
		}
		$category = new Zend_Form_Element_Select('category');
		$category -> setDecorators(array(array('ViewHelper'),))
				 -> addMultiOptions($new_category_array)
				 -> setRequired(True)
			     -> addValidator('NotEmpty');
        
        $description = new Zend_Form_Element_Textarea('description');
		$description -> setDecorators(array(array('ViewHelper'),))
		          -> addFilter('StringTrim')
                  -> setAttribs(array('style' => 'width:800px;height:300px;visibility:hidden;'));
        
        //attached files <=20
		//check folder existed
		$folder = date("Ym");
		if(!is_dir("../public/attachment/".$folder))
		{
			mkdir("../public/attachment/".$folder, 0777);
		}
		
		for($n=1;$n<21;$n++)
		{
			${"attachment".$n} = new Zend_Form_Element_File("attachment".$n);
		    ${"attachment".$n} -> setDecorators(array(array('File'),))
		    		     	   -> setDestination("../public/attachment/".$folder);
		}
        
		$submit = new Zend_Form_Element_Submit('submitx');
		$submit -> setDecorators(array(array('ViewHelper'),));

		$this -> addElements(
								array(
										$id,
                                        $title,
                                        $category,
                                        $description,
                                        $author,
										$attachment1,
										$attachment2,
										$attachment3,
										$attachment4,
										$attachment5,
										$attachment6,
										$attachment7,
										$attachment8,
										$attachment9,
										$attachment10,
										$attachment11,
										$attachment12,
										$attachment13,
										$attachment14,
										$attachment15,
										$attachment16,
										$attachment17,
										$attachment18,
										$attachment19,
										$attachment20,
										$submit
									)
							);
	}
}
