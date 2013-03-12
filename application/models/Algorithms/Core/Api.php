<?php

class Algorithms_Core_Api
{
    var $original_xml_array;
    var $api_type;
    var $api_target;

    //Switch array to xml
    public static function Array2Xml($arr, $xml = NULL)
    {
        $first = $xml;
        if($xml === NULL) $xml = new SimpleXMLElement('<root/>');
        foreach ($arr as $k => $v) 
        {
            is_array($v)
                ? self::Array2Xml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return ($first === NULL) ? $xml->asXML() : $xml;
    }
    
    //Switch xml to array
    public static function Xml2Array($xml)
    {
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }
    
    //Post a request via CURL in XML format
    public static function PostXml($xml_contents, $target)
    {
        if($xml_contents && $target)
        {
            $header[] = "Content-type: text/xml"; 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $target);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_contents);
            $result = curl_exec($ch);
            curl_close($ch);
        }else{
            $result = NULL;
        }
        
        return $result;
    }
    
    function XmlKeyFilter($xml) //remove space
    {
        return str_replace(' ', '', $xml);
    }
    
    function XmlValueFilter($xml) //remove special characters
    {
        $xml = str_replace('&', '&amp;', $xml);
        $xml = str_replace('<', '&lt;', $xml);
        $xml = str_replace('>', '&gt;', $xml);
        
        return $xml;
    }
    
    function Push()
    {
        $result = FALSE;
        
        if($this->api_target && $this->api_type && $this->original_xml_array)
        {        
            $data = $this->Decolaction();
            
            //insert log
            $logs_api_model = new Databases_Tables_LogsApi();
            $logs_api_model->contents = $data['xml_contents_array'];
            $logs_api_model->api_status = 0;
            $logs_api_model->api_step = 1;
            $logs_api_model->api_target = $this->api_target;
            $logs_api_model->api_type = $this->api_type;
            $logs_api_id = $logs_api_model->InsertLog();

            //make xml
            $xml_data = $this->Array2Xml($data['xml_contents_array']);
            
            //post
            $headers = array(
                "Content-type: text/xml"
                ,"Content-length: ".strlen($xml_data)
                ,"Connection: close"
            );

            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL,$data['target_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
            $response = curl_exec($ch); 
            
            if(curl_errno($ch))
            {
                print curl_error($ch);
            }else{
                curl_close($ch);
                $result = TRUE;
            }
            
            //update log
            $logs_api_model->logs_api_id = $logs_api_id;
            $logs_api_model->api_status = 1;
            $logs_api_model->api_response = $response;
            $logs_api_model->UpdateLog();
        }
        
        return $result;
    }
    
    function Decolaction()
    {
        $result = array();
        
        if($this->api_target && $this->api_type && $this->original_xml_array)
        {
            $common_part = $this->ApiContentsCommonPart();
            
            $xml_contents_array = array();
            
            $xml_contents_array['common'] = $common_part['common'];
            $xml_contents_array['params'] = $this->original_xml_array;
            
            $result['xml_contents_array'] = $xml_contents_array;
            $result['target_url'] = $common_part['target_url'];
        }
        
        return $result;
    }
    
    function ApiContentsCommonPart()
    {
        $result = array();
        
        if($this->api_target && $this->api_type)
        {
            $api_target_model = new Databases_Tables_ApiTargets();
            $api_target_model->api_targets_id = $this->api_target;
            $api_target = $api_target_model->GetTarget();
            
            $api_type_model = new Databases_Tables_ApiTypes();
            $api_type_model->api_types_id = $this->api_type;
            $api_type = $api_type_model->GetType();
            
            if(!empty($api_target) && !empty($api_type))
            {
                $common = array();
                
                $common['version'] = $api_type['api_types_version'];
                $common['action'] = $api_type['api_types_name'];
                $common['merchantid'] = $api_target['user_id'];
                $common['publickey'] = $api_target['public_key_1'];
                
                $result['common'] = $common;
                $result['target_url'] = $api_target['target_url'];
            }
        }
        
        return $result;
    }
}