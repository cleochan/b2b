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
}