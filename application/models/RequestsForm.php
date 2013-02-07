<?php

class RequestsForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Requests');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));
		
		$dead_line = new Zend_Form_Element_Text('dead_line');
		$dead_line -> setDecorators(array(array('ViewHelper'),));
		
		//create category options
		$category_model = new RequestsCategory();
		$category_array = $category_model -> BuildTree();
        
		$new_category_array[''] = "Choose";
		foreach($category_array as $category_val)
		{
			foreach($category_val as $category_val_key => $category_val_val)
            {
                $new_category_array[$category_val_key] = $category_val_val;
            }
		}
		$category = new Zend_Form_Element_Select('category');
		$category -> setDecorators(array(array('ViewHelper'),))
				 -> addMultiOptions($new_category_array)
				 -> setRequired(True)
			     -> addValidator('NotEmpty');
        // //create 
        // status options
		$st_array = array(1 => "Pending",
                          2 => "Closed",
                          3 => "Canceled");
        
		$status = new Zend_Form_Element_Select('status');
		$status -> setDecorators(array(array('ViewHelper'),))
				-> addMultiOptions($st_array);
		
		//create priority radio
		$pr_array = array(1=>"normal", 2=>"urgent");
		$priority = new Zend_Form_Element_Radio('priority');
		$priority -> setDecorators(array(array('ViewHelper'),))
				  -> addMultiOptions($pr_array)
				  -> setSeparator(' ')
				  -> setRequired(True)
				  -> setValue(1)
			      -> addValidator('NotEmpty');
		
		$title = new Zend_Form_Element_Text('title');
		$title -> setDecorators(array(array('ViewHelper'),))
			   -> setRequired(True)
			   -> setAttrib('size',100)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
                
		$contents = new Zend_Form_Element_Textarea('contents');
		$contents -> setDecorators(array(array('ViewHelper'),))
		          -> addFilter('StringTrim')
                  -> setAttribs(array('style' => 'width:800px;height:300px;visibility:hidden;'));

		$comments = new Zend_Form_Element_Textarea('comments');
		$comments -> setDecorators(array(array('ViewHelper'),))
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
		
		$participants = new Zend_Form_Element_Text('participants');
		$participants -> setDecorators(array(array('ViewHelper'),))
			    -> setAttrib('size',70)
			    -> addFilter('StripTags')
		        -> addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submitx');
		$submit -> setDecorators(array(array('ViewHelper'),));

		$this -> addElements(
								array(
										$id,
										$dead_line,
										$category,
										$priority,
										$title,
										$contents,
										$comments,
                                        $status,
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
										$participants,
										$submit
									)
							);
	}
}
