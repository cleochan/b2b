<?php

class Algorithms_Core_Menu
{
    function MenuForAdmin()
    {
        $menu = array(
            "Dashboard" => "/admin",
            "Merchants List" => "/admin/merchants",
            "Help Mgt" => "/admin/help-mgt"
        );
        
        return $menu;
    }
    
    function MenuForMerchant()
    {
        $menu = array(
            "Dashboard" => "/index",
            "Order Report" => "/index/order-report",
            "Financial Report" => "/index/financial-report",
            "Place Order" => "/index/place-order",
            "Import Order" => "/index/import-order",
            "Add Value" => "/index/add-value",
            "Help" => "/index/help"
        );
        
        return $menu;
    }
    
    function OtherLinks()
    {
        $menu = array(
            "Add Merchant" => "/admin/merchant-add",
        );
        
        return $menu;
    }
    
    function GetNavigation($params_array)
    {
        if(count($params_array))
        {
            $temp_link_pool = array();
            
            $link_array = array_merge($this->MenuForAdmin(), $this->MenuForMerchant(), $this->OtherLinks());
            
            foreach($params_array as $param)
            {
                $param_val = explode("|", $param);
                
                if(1 < count($param_val))
                {
                    $temp_link_a = $link_array[$param_val[0]]."/".$param_val[1];
                }else{
                    $temp_link_a = $link_array[$param_val[0]];
                }
                
                if($temp_link_a)
                {
                    $temp_link = "<a href='".$temp_link_a."'>".$param_val[0]."</a>";
                }else{
                    $temp_link = $param_val[0];
                }
                
                $temp_link_pool[] = $temp_link;
            }
            
            $result = implode("&nbsp;&nbsp;>&nbsp;&nbsp;", $temp_link_pool);
        }
        
        return $result;
    }
    
    function FormatMenu($menu_array, $current_page_action)
    {
        switch ($current_page_action)
        {
            case "index":
                $link = "Dashboard";
                break;
            case "merchants":
                $link = "Merchants List";
                break;
            case "help-mgt":
                $link = "Help Mgt";
                break;
            case "order-report":
                $link = "Order Report";
                break;
            case "financial-report":
                $link = "Financial Report";
                break;
            case "place-order":
                $link = "Place Order";
                break;
            case "import-order":
                $link = "Import Order";
                break;
            case "add-value":
                $link = "Add Value";
                break;
            case "help":
                $link = "Help";
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