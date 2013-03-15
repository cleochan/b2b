<?php

class Algorithms_Core_Export
{
    var $file_dir = 'feeds/';
    var $file_name;
    var $contents;
    
    function Push()
    {
        if($this->file_dir && $this->file_name && $this->contents)
        {
            $result = $this->file_name. " success.";

            $filename = $this->file_dir.$this->file_name;

            $fp = fopen($filename,'w');

            if(!$fp){
             $result = "The file ".$filename." can not be opened.";
             exit;
            }

            if (fwrite($fp, $this->contents) === FALSE) {
                    $result = "The file ".$filename."is unwritable.";
                    exit;
               }

            fclose($fp);
        }else{
            echo $this->file_dir."<br />";
            echo $this->file_name."<br />";
            echo $this->contents."<br />";
            die;
            
            $result = "Key parameters missed.";
        }

        
        return $result;
    }
}