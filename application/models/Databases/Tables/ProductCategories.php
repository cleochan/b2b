<?php

class Databases_Tables_ProductCategories extends Zend_Db_Table
{
    protected $_name = 'product_categories';
    
    var $category_id    =   '';
    var $category_name  =   '';
    var $parent_id      =   '';
    
    function __construct(){
        $this->db = Zend_Registry::get("db");
        parent::__construct();
    }
    
    function BuildTree()
    {
        $rows = $this->fetchAll("(category_id != 174 and parent_id != 174) or parent_id is NULL"); //without special category
        $data = $rows->toArray();
        
        $result = array();
        
        if(!empty($data))
        {
            foreach($data as $d)
            {
                $result[] = array(
                    "category_id" => $d['category_id'],
                    "category_name" => $d['category_name'],
                    "parent_id" => $d['parent_id']?$d['parent_id']:0
                    );
            }
        }
        
        return $result;
    }
    
    function TreeForMerchant($merchant_string)
    {
        $entire_tree = $this->BuildTree();
        
        $merchant_array = array_unique(explode(",", $merchant_string));
        
        $checked_array = array();
        
        while (!empty($merchant_array))
        {
            //get parent array
            $parent_array = $this->GetParentIdArray($merchant_array);
            
            foreach ($merchant_array as $m_val)
            {
                if(!in_array($m_val, $checked_array) && $m_val)
                {
                    $checked_array[] = $m_val;
                }
            }
            
            unset($merchant_array);
            
            $merchant_array = $parent_array;
        }
        
        foreach($entire_tree as $key=>$val)
        {
            if(in_array($val['category_id'], $checked_array) || empty($checked_array))
            {
                $entire_tree[$key]['checked'] = "true";
                $entire_tree[$key]['open'] = "false";
            }else{
                $entire_tree[$key]['checked'] = "false";
                $entire_tree[$key]['open'] = "false";
            }
        }
        
        return $entire_tree;
    }
    
    function GetParentIdArray($id_array)
    {
        $select = $this->select();
        $select->where("category_id IN (?)", $id_array);
        $data = $this->fetchAll($select);
        
        $result = array();
        
        if(!empty($data))
        {
            foreach($data as $d)
            {
                if($d['parent_id'])
                {
                    $result[] = $d['parent_id'];
                }
            }
        }
        
        return $result;
    }
    
    function GetChildIdArray($category_id, $entire_tree)
    {
        $source_array = array($category_id);
        $next_round = array($category_id);
        $continue = 1;
        
        while($continue)
        {
            $continue = 0;
            
            foreach($next_round as $sa_key => $sa_val)
            {
                foreach($entire_tree as $et)
                {
                    if($sa_val == $et['parent_id'] && !in_array($et['category_id'], $source_array))
                    {
                        $source_array[] = $et['category_id'];
                        $next_round[] = $et['category_id'];
                        $continue = 1;
                    }
                }
                
                unset($next_round[$sa_key]);
            }
        }
        
        return array_unique($source_array);
    }
    
    function GetParentStatus($target_parent_id, $entire_tree)
    {
        $result = FALSE;
        
        foreach($entire_tree as $et)
        {
            if($target_parent_id == $et['parent_id'] && $et['checked'])
            {
                $result = TRUE;
            }
        }
        
        return $result;
    }
    
    function IsAll($num)
    {
        $data = $this->select();
        $data->from($this->_name, array("count('category_id') as ct"));
        $data->where("(category_id != 174 and parent_id != 174) or parent_id is NULL");
        $d = $this->fetchRow($data);
        return ($d['ct']==$num?true:false);
    }
    
    function CheckedArray($array)
    {
        $result = array();
        
        if(!empty($array))
        {
            foreach($array as $a)
            {
                if("true" == $a['checked'])
                {
                    $result[] = $a['category_id'];
                }
            }
        }
        
        return array_unique($result);
    }
    
    function GetAllCategories()
    {
        $rows = $this->fetchAll();
        
        $result = array();
        
        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                $result[$row['category_id']] = $row['category_name'];
            }
        }
        
        return $result;
    }
    
    function truncateCategory()
    {
        $this->db->query('truncate table '.$this->_name);
    }
    
    function addCategory()
    {
        $data   = array(
            'category_id'   =>  $this->category_id,
            'category_name' =>  $this->category_name,
            'parent_id'     =>  $this->parent_id,
        );
        $this->db->insert($this->_name,$data);
    }
    
    function getCategoryInfo($category_id)
    {
        $result =   '';
        if($category_id)
        {
            $select =   $this->select();
            $select->where("category_id = ?", $category_id);
            $data =   $this->fetchRow($select);
            $result =   $data['category_name'];
        }
        
        return $result;
    }
    
    function getAllCategoryArray(){
        $result =   array();
        $category_list   =   $this->fetchAll()->toArray();
        foreach ($category_list as $category){
            $result[$category['category_id']]   =   $category;
        }
        return $result;
    }
}