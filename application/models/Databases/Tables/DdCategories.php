<?php
class Databases_Tables_DdCategories extends Zend_Db_Table
{
    protected $_name = 'dd_categories';
    
    var $category_id;
    var $category_name;
    var $parent_id;
    
    function getAllCategoryArray(){
        $result =   array();
        $category_list   =   $this->fetchAll()->toArray();
        foreach ($category_list as $category){
            $result[$category['category_id']]   =   $category;
        }
        return $result;
    }
}

?>
