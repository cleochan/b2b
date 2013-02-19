<?php

class Databases_Joins_GetUserInfo
{
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
    function GetUserList($user_type=2)
    {
        $select = $this->db->select();
        $select->from("users as u", array("user_id", "email", "user_status"));
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name", "contact_phone", "join_date", "balance"));
        $select->where("user_type = ?", $user_type);
        $select->order(array("user_status DESC", "join_date DESC"));
        
        $data = $this->db->fetchAll($select);
        
        return $data;
    }
}