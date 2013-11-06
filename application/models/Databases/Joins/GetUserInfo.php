<?php
/**
 * Resource model for Get User Info
 * @category    Databases
 * @package     Databases_Joins
 */
class Databases_Joins_GetUserInfo
{
    function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
    /**
     * Get User List in Active 
     * @param int $user_type
     * @return array
     */
    function GetUserList($user_type=2)
    {
        $select = $this->db->select();
        $select->from("users as u", array("user_id", "email", "user_status"));
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name", "contact_phone", "join_date", "balance", "credit", "discount", "bpay_ref","flat_rate_shipping"));
        $select->where("user_type = ?", $user_type);
        $select->where("user_status = ?", 1);
        $select->order(array("user_status DESC", "join_date DESC"));
        
        $data = $this->db->fetchAll($select);
        
        return $data;
    }
    
    /**
     * Ger user info with user_id
     * @param int $user_id
     * @return array
     */
    function GetUserInfo($user_id)
    {
        $select = $this->db->select();
        $select->from("users as u", array("user_id", "email", "user_status"));
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name","contact_phone","address","post_code","suburb","state", "join_date", "balance", "credit", "discount", "bpay_ref","flat_rate_shipping"));
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
    
    /**
     * Get user id with bpay_ref
     * @param string $bpay_ref
     * @return array
     */
    function GetUserId($bpay_ref)
    {
        $select = $this->db->select();
        $select->from("users as u", array("user_id", "email", "user_status"));
        $select->joinLeft("users_extension as e", "u.user_id=e.user_id", array("company", "contact_name","address","post_code","suburb","state", "join_date", "balance", "credit", "discount", "bpay_ref","flat_rate_shipping"));
        $select->where("e.bpay_ref = ?", $bpay_ref);
        $data = $this->db->fetchRow($select);
        return $data;
    }
}