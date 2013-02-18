<?php

class Algorithms_Core_Menu
{
    function MenuForMerchant()
    {
        $menu = array(
            "Dashboard" => "/admin",
            "Merchants List" => "/admin/merchants",
            "Help Mgt" => "/admin/help"
        );
        
        return $menu;
    }
    
    function MenuForAdmin()
    {
        $menu = array(
            "Dashboard" => "/index",
            "Order Report" => "/index/order-report",
            "Financial Report" => "/index/financial-report",
            "Place Order" => "/index/place-order",
            "Improt Order" => "/index/import-order",
            "Add Value" => "/index/add-value",
            "Help" => "/index/help"
        );
        
        return $menu;
    }
    
    function FormatMenu($menu_array, $current_page_action)
    {
        switch ($current_page_action)
        {
            case "t1":
                $link = "Add Value";
                break;
            default:
                $link = "Dashboard";
                break;
        }
        
        $html_array = array();
        
        
        foreach($menu_array as $m_key => $m_val)
        {
            if($m_key == $link)
            {
                $strong_head = "<strong>";
                $strong_end = "</strong>";
            }else{
                $strong_head = "";
                $strong_end = "";
            }
            
            $html_array[] = "<a href='".$m_val."'>".$strong_head.$m_key.$strong_end."</a>";
        }
        
        $menu_html = implode("&nbsp;&nbsp;&nbsp;&nbsp;", $html_array);
        
        return $menu_html;
    }
}