<?php

class Algorithms_Core_Api
{
    public static function Array2Xml($arr, $xml = NULL)
    {
        $first = $xml;
        if($xml === NULL) $xml = new SimpleXMLElement('<root/>');
        foreach ($arr as $k => $v) 
        {
            is_array($v)
                ? self::from_array($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return ($first === NULL) ? $xml->asXML() : $xml;
    }

    public static function Xml2Array($xml)
    {
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }
}