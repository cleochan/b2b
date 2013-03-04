<?php

class Algorithms_Extensions_Plugin
{
    function FormatArray($array)
    {
        echo "<pre>";
        print_r($array);
        echo "<pre>";
    }
    
    function EmailCheck($email)
    {
        $ret=false;

        if(strstr($email, '@') && strstr($email, '.'))
        {
            if(eregi("^([_a-z0-9]+([._a-z0-9-]+)*)@([a-z0-9]{2,}(.[a-z0-9-]{2,})*.[a-z]{2,3})$", $email))
            {
                $ret=true;
            }
        }

        return $ret;
    }
    
    function GetFeedPath($feed_name, $feed_extension)
    {
        $pro = "http://";
        $domain = $_SERVER['HTTP_HOST'];
        
        switch ($feed_extension)
        {
            case 1:
                $ext = ".csv";
                break;
            case 2:
                $ext = ".txt";
                break;
            case 3:
                $ext = ".xml";
                break;
            default :
                break;
        }
        
        $params_table = new Databases_Tables_Params();
        $path = $params_table->GetVal("feed_path");
        
        return $pro.$domain.$path.$feed_name.$ext;
    }
    
    function GenerateInitialFeedName($str)
    {
        $str = preg_replace("/[^a-zA-Z0-9]+/", "_", $str);
        $result = strtolower($str);
        
        return $result;
    }
}