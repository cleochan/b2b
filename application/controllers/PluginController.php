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
    
    function testGeneratorAction()
    {
        $g = new Algorithms_Extensions_Bpay();
        echo $g->RefGenerator(1003);
        
        die;
    }
    
    function getPathAction()
    {
        echo $_SERVER['HTTP_HOST'];
        die;
    }
    
    function feedTestAction()
    {
        $model = new Algorithms_Core_Feed();
        $model->user_id_array = array(3);
        $result = $model->Publish();
        echo $result;
        die;
    }
    
    function csvTestAction()
    {
        $filename = 'feeds/test.csv';
        $fp = fopen($filename,'w');
        if(!$fp){
         echo "不能打开文件 $filename";
         exit;
        }
        $data = array(
          array(
         'id'=>'1',
         'cid'=>'2',
         'name'=>'这是第一个开户名称',
         'account'=>'3323423432432',
         'bank'=>'建设银行'  ,
          ),
        array(
         'id'=>'2',
         'cid'=>'3',
         'name'=>'这是第二个开户名称',
         'account'=>'3323423432433',
         'bank'=>'工商银行'  ,
          ),
        );
        //我的编码是gbk,如果是utf-8,使用转码语句 mb_convert_encoding('申请ID','gb2312','UTF-8').',';
        $csv_content = '';
        $csv_content .= '申请ID,';
        $csv_content .= '合同编号,';
        $csv_content .= '开户名称,';
        $csv_content .= '银行账号,';
        $csv_content .= '开户行';
        $csv_content .= "\n\r";
        foreach ($data as  $ditem){
         //echo $ditem['id'];

            $csv_content .= $ditem['id'].',';
         $csv_content .= $ditem['cid'].',';
         $csv_content .= $ditem['name'].',';
            $csv_content .= $ditem['account'].',';
         $csv_content .= $ditem['bank'];
            $csv_content .= "\n\r";


        }

        if (fwrite($fp, $csv_content) === FALSE) {
                echo "不能写入到文件 $filename";
                exit;
           }
        fclose($fp);
        echo "<br />End<br />";
        die;
    }
    
    function randTestAction()
    {
        $table = new Databases_Tables_ProductInfo1();
        $table->Abc();
        echo "End.";
        die;
    }
    
    function replaceTestAction()
    {
        $a = "/<{abc}>/";
        $b = "hahaha";
        $c = "111111111111111<{abc}>1111111111111111111";
        
        echo preg_replace($a, $b, $c);
        die;
    }
}
