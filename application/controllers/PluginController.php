<?php

class PluginController extends Zend_Controller_Action
{
	
	function init(){
		$this->db = Zend_Registry::get("db");
		
	}
    
    function preDispatch()
	{
		//disable layout for Login page
		$this->_helper->layout->disableLayout();
	}
	
    function indexAction()
    {
        $a = new Tables_Users();
        
        $a->DumpData();
        
        die;
    }
    
    function testAction()
    {
        $data = array();
        $data['email'] = "mark560@gmail.com";
        $data['password'] = "212121";
        $data['user_type'] = 3;
        $data['user_status'] = 4;
        
        $data_xml = Algorithms_Core_Api::Array2Xml($data);
        $target = "http://demo.local.b2b/plugin/test-terminal";
        
        try{
            Algorithms_Core_Api::PostXml($data_xml, $target);
        }  catch (Zend_Exception $exp){
            var_dump($exp->getMessage());
        }
        
        die;
    }
    
    function testTerminalAction()
    {
        $params = file_get_contents('php://input');
        $array = Algorithms_Core_Api::Xml2Array($params);
        
        $db = new Databases_Tables_Users;
        $db->email = $array['email'];
        $db->password = $array['password'];
        $db->user_type = $array['user_type'];
        $db->user_status = $array['user_status'];
        $db->AddUser();
        
        die;
    }
    
    function t1Action()
    {
        Algorithms_Extensions_Plugin::FormatArray($this->getRequest()->getActionName());
        die;
    }
    
    function phpinfoAction()
    {
        phpinfo();
        die;
    }
    
    function stringTestAction()
    {
        $string = "[1|OK][2|NO]";
        
        $length = strlen($string);
        
        $result_1 = array();
        
        while($length)
        {
            $from = strpos($string, "[");
            $to = strpos($string, "]");
            
            if(FALSE !== $from && FALSE !== $to && $from < $to)
            {
                $result_1[] = substr($string, $from+1, $to-$from-1);

                $string = substr($string, $to+1);

                $length = strlen($string);
            }else{
                $length = 0; // Exit
            }
        }
        
        //Algorithms_Extensions_Plugin::FormatArray($result_1);
        
        die;
    }
    
    function feedTestAction()
    {
        $model = new Algorithms_Core_Feed();
        $model->user_id_array = array(4);
        $result = $model->Publish();
        echo $result;
        die;
    }
    
    function testXmlAction()
    {
        $result = array();
        
        $a = array(
            "Category" => "Baby",
            "Brand" => "Adidas",
            "Product Name" => "ABC"
        );
        
        $result[] = $a;
        
        $b = array(
            "Category" => "Man",
            "Brand" => "Nike",
            "Product Name" => "DEF"
        );
        
        $result[] = $b;
        
        $aip = new Algorithms_Core_Api();
        
        //$xml = $aip->Array2Xml($result);
        
$xml2 = <<<XML
<?xml version='1.0'?> 
<document>
    <a>
        <Brand>Cool</Brand>
        <Category>Category 1</Category>
        <Colour>Colour 150</Colour>
        <Dimension>D"ime"n&amp;sion 150</Dimension>
        <ListPrice>1250.00</ListPrice>
    </a>
</document>
XML;
        
        Algorithms_Extensions_Plugin::FormatArray($aip->Xml2Array($xml2));die;
    }
    
    function apiTestAction()
    {
        $api_model = new Algorithms_Core_Api();
        
        $api_model->api_target = 1;
        $api_model->api_type = 1;
        $api_model->original_xml_array = array("a","b","c");
        $api_model->Push();
        die;
    }
    
    function numTestAction()
    {
        echo number_format(-1000, 2);die;
    }
}
