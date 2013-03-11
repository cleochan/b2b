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
    
    function Decolaction()
    {
        $result = array();
        
        if($this->api_target && $this->api_type && $this->original_xml_array)
        {
            
        }
        
        return $result;
    }
}