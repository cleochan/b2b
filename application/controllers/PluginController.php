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
        $data['email'] = "test2@test.com";
        $data['password'] = "212121";
        $data['user_type'] = 3;
        $data['user_status'] = 4;
        
        $data_xml = Algorithms_Core_Api::Array2Xml($data);
        
        
        $header[] = "Content-type: text/xml"; 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://demo.local.b2b/plugin/test-terminal');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_xml);
        $result = curl_exec($ch);
        curl_close($ch);
        Algorithms_Extensions_Plugin::FormatArray($result);
        
        die;
    }
    
    function testTerminalAction()
    {
        $params = $_REQUEST;
        Databases_Tables_Logs::AddLog($params);
        $data_array = Algorithms_Core_Api::Xml2Array($params);
        Algorithms_Extensions_Plugin::FormatArray($data_array);
        
        $databases_tables_users = new Databases_Tables_Users();
        
        $databases_tables_users -> email = $data_array['email'];
        $databases_tables_users -> password = $data_array['password'];
        $databases_tables_users -> user_type = $data_array['user_type'];
        $databases_tables_users -> user_status = $data_array['user_status'];
        
        $databases_tables_users ->AddUser();
        
        die;
    }
}
