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
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name", "contact_phone", "join_date", "balance", "credit", "discount", "bpay_ref"));
        $select->where("user_type = ?", $user_type);
        $select->order(array("user_status DESC", "join_date DESC"));
        
        $data = $this->db->fetchAll($select);
        
        return $data;
    }
    
    function GetUserInfo($user_id)
    {
        $select = $this->db->select();
        $select->from("users as u", array("user_id", "email", "user_status"));
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name", "contact_phone", "join_date", "balance", "credit", "discount", "bpay_ref"));
        $select->where("u.user_id = ?", $user_id);
        
        $data = $this->db->fetchRow($select);
        
        return $data;
    }
    
    function MerchantRefArray()
    {
        $data = $this->GetUserList();
        
        $ref_array = array();
        
        if(count($data))
        {
            foreach($data as $d)
            {
                $ref_array[] = $d['bpay_ref'];
            }
        }
        
        return $ref_array;
    }
}