<?php

class Databases_Tables_UsersFeed extends Zend_Db_Table
{
    protected $_name = 'users_feed';
    
    function GetFeedInfo($user_id)
    {
        $row = $this->fetchRow("user_id='".$user_id."'");
        
        return $row;
    }
}