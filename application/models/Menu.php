<?php
//<a href="/index/index/">All Tickets</a> | <a href="/index/index/type/777s1">Created</a> | <a href="/index/index/type/777s23"><strong>Dealing</strong></a> | <a href="/index/index/type/777s4">Finished</a> | <a href="/index/index/type/777s5">Closed</a> | <a href="/index/index/type/777s6">Canceled</a> | <a href="/index/index/type/777x">Searched</a>
class Menu
{
	function GetTicketMenu($param)
	{
		//count the tickets
		$tickets = new Tickets();		
		$status_count = $tickets -> CountData();
		
		switch($param)
		{
			case "1": //pending
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'><strong>Pending".$status_count['pending']."</strong></a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			case "2": //processing
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'><strong>Processing".$status_count['processing']."</strong></a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			case "3": //closed
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'><strong>Closed".$status_count['closed']."</strong></a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			case "4": //canceled
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'><strong>Canceled".$status_count['canceled']."</strong></a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			case "search": //search
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'><strong>Searched</strong></a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			case "add": //add
				$str = "<a href='/index/index/'>All Tickets".$status_count['all']."</a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'><strong>Add Ticket</strong></a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
			default://all
				$str = "<a href='/index/index/'><strong>All Tickets".$status_count['all']."</strong></a>";
				$str .= " | <a href='/index/index/type/1'>Pending".$status_count['created']."</a>";
				$str .= " | <a href='/index/index/type/2'>Processing".$status_count['processing']."</a>";
				$str .= " | <a href='/index/index/type/3'>Closed".$status_count['closed']."</a>";
				$str .= " | <a href='/index/index/type/4'>Canceled".$status_count['canceled']."</a>";
				$str .= " | <a href='/index/index/type/search'>Searched</a>";
				if(1 != $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/index/add/type/add'>Add Ticket</a> (<a href='/index/fast-ticket/type/add'>Fast</a>)";
                }
				break;
		}
		
		return $str;
	}
	
	function GetRequestsMenu($param)
	{
		switch($param)
		{
			case "add":
				$str = "<a href='/requests/index/'>Active Requests</a>";
                $str .= " | <a href='/requests/index-inactive/type/view_inactive'>Inactive Requests</a>";
				$str .= " | <a href='/requests/add/type/add'><strong>Add Request</strong></a>";
				break;
			case "view_inactive":
				$str = "<a href='/requests/index/'>Active Requests</a>";
                $str .= " | <a href='/requests/index-inactive/type/view_inactive'><strong>Inactive Requests</strong></a>";
				$str .= " | <a href='/requests/add/type/add'>Add Request</a>";
				break;
			default:
				$str = "<a href='/requests/index/'><strong>Active Requests</strong></a>";
                $str .= " | <a href='/requests/index-inactive/type/view_inactive'>Inactive Requests</a>";
				$str .= " | <a href='/requests/add/type/add'>Add Request</a>";
				break;
		}
		
		return $str;
	}
	
	function GetUsersMenu($param)
	{
		switch($param)
		{
			case "add":
            case "edit":
            case "division":
				$str = "<a href='/users/index/'>User List</a>";
				if(3 == $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/users/add/'><strong>Add User</strong></a>";
                }
				break;
			default:
				$str = "<a href='/users/index/'><strong>User List</strong></a>";
				if(3 == $_SESSION["Zend_Auth"]["storage"]->level_mgt)
                {
                    $str .= " | <a href='/users/add/'>Add User</a>";
                }
				break;
		}
		
		return $str;
	}
	
	function GetProjectsMenu($param)
	{
		switch($param)
		{
			case "add": //created
				$str = "<a href='/projects/index/'>Projects List</a>";
				$str .= " | <a href='/projects/add/'><strong>Add Project</strong></a>";
				break;
			default:
				$str = "<a href='/projects/index/'><strong>Projects List</strong></a>";
				$str .= " | <a href='/projects/add/'>Add Project</a>";
				break;
		}
		
		return $str;
	}
	
	function GetTrainingMenu($param)
	{
		switch($param)
		{
			case "library": //list
				$str = "<a href='/training/index/'>Training Calendar</a>";
				$str .= " | <a href='/training/library/'><strong>Training Library</strong></a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "lib-add":
				$str = "<a href='/training/index/'>Training Calendar</a>";
				$str .= " | <a href='/training/library/'><strong>Training Library</strong></a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "lib-edit":
				$str = "<a href='/training/index/'>Training Calendar</a>";
				$str .= " | <a href='/training/library/'><strong>Training Library</strong></a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "lib-view":
				$str = "<a href='/training/index/'>Training Calendar</a>";
				$str .= " | <a href='/training/library/'><strong>Training Library</strong></a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "add":
				$str = "<a href='/training/index/'><strong>Training Calendar</strong></a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "edit":
				$str = "<a href='/training/index/'><strong>Training Calendar</strong></a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "view":
				$str = "<a href='/training/index/'><strong>Training Calendar</strong></a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "score":
				$str = "<a href='/training/index/'><strong>Training Calendar</strong></a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
            case "history":
				$str = "<a href='/training/index/'>Training Calendar</a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'><strong>My Training History</strong></a>";
				break;
			default:
				$str = "<a href='/training/index/'><strong>Training Calendar</strong></a>";
				$str .= " | <a href='/training/library/'>Training Library</a>";
                $str .= " | <a href='/training/history/'>My Training History</a>";
				break;
		}
		
		return $str;
	}
    
	function GetGroupMenu($param)
	{
		switch($param)
		{
			case "add": //created
				$str = "<a href='/group/index/'>Group List</a>";
				$str .= " | <a href='/group/add/'><strong>Add Group</strong></a>";
				break;
			default:
				$str = "<a href='/group/index/'><strong>Group List</strong></a>";
				$str .= " | <a href='/group/add/'>Add Group</a>";
				break;
		}
		
		return $str;
	}
    
	function GetKpiMenu($param)
	{
		switch($param)
		{
			case "review":
				$str = "<a href='/kpi/review-search'><strong>My KPI Report</strong></a>";
                if(in_array($_SESSION["Zend_Auth"]["storage"]->level_mgt, array(2,3)))
                {
                    $str .= " | <a href='/kpi/index/'>Non-review Tasks</a>";
                    $str .= " | <a href='/kpi/inactivate/'>Reviewed Tasks</a>";
                }
				break;
			case "review-search":
				$str = "<a href='/kpi/review-search'><strong>My KPI Report</strong></a>";
                if(in_array($_SESSION["Zend_Auth"]["storage"]->level_mgt, array(2,3)))
                {
                    $str .= " | <a href='/kpi/index/'>Non-review Tasks</a>";
                    $str .= " | <a href='/kpi/inactivate/'>Reviewed Tasks</a>";
                }
				break;
			case "inactivate":
				$str = "<a href='/kpi/review-search'>My KPI Report</a>";
                if(in_array($_SESSION["Zend_Auth"]["storage"]->level_mgt, array(2,3)))
                {
                    $str .= " | <a href='/kpi/index/'>Non-review Tasks</a>";
                    $str .= " | <a href='/kpi/inactivate/'><strong>Reviewed Tasks</strong></a>";
                }
				break;
            default:
				$str = "<a href='/kpi/review-search'>My KPI Report</a>";
                if(in_array($_SESSION["Zend_Auth"]["storage"]->level_mgt, array(2,3)))
                {
                    $str .= " | <a href='/kpi/index/'><strong>Non-review Tasks</strong></a>";
                    $str .= " | <a href='/kpi/inactivate/'>Reviewed Tasks</a>";
                }
				break;
		}
		
		return $str;
	}
	
	function GetType($status_id)
	{
		switch($status_id)
		{
			case 1: //Pending
				$result = 1;
				break;
			case 2: //Processing
				$result = 2;
				break;
			case 3: //Closed
				$result = 3;
				break;
			case 4: //Canceled
				$result = 4;
				break;
			default: //do nothing
				$result = "";
				break;
		}
		
		return $result;
	}

	function GetTopMenu($ctl) //$ctl = controller name
	{
		$str1 = "<a href='/index/index/type/2'>Tickets</a>";
		$str1_on = "<a href='/index/index/type/2'><strong>Tickets</strong></a>";
		$str3 = "<a href='/projects/index'>Projects</a>";
		$str3_on = "<a href='/projects/index'><strong>Projects</strong></a>";
		$str4 = "<a href='/departments/index'>Departments</a>";
		$str4_on = "<a href='/departments/index'><strong>Departments</strong></a>";
		$str5 = "<a href='/users/index'>Users</a>";
		$str5_on = "<a href='/users/index'><strong>Users</strong></a>";
		$str6 = "<a href='/config/index'>System</a>";
		$str6_on = "<a href='/config/index'><strong>System</strong></a>";
		$str7 = "<a href='/profile/index'>Profile</a>";
		$str7_on = "<a href='/profile/index'><strong>Profile</strong></a>";
		$str9 = "<a href='/workbook/index'>Workbook</a>";
		$str9_on = "<a href='/workbook/index'><strong>Workbook</strong></a>";
		$str10 = "<a href='/category/index'>Category</a>";
		$str10_on = "<a href='/category/index'><strong>Category</strong></a>";
		$str11 = "<a href='/requests/index'>Requests</a>";
		$str11_on = "<a href='/requests/index'><strong>Requests</strong></a>";
		$str12 = "<a href='/kpi/review-search'>KPI</a>";
		$str12_on = "<a href='/kpi/review-search'><strong>KPI</strong></a>";
		$str13 = "<a href='/training/index'>Training</a>";
		$str13_on = "<a href='/training/index'><strong>Training</strong></a>";
		
		$menu = array();
		
		switch($_SESSION["Zend_Auth"]["storage"]->level_mgt)
		{
			case 1: //requests/tickets -- for other departments
				if(in_array($ctl, array("index", "tasks")))
				{
					$menu[] = $str11;
					$menu[] = $str1_on;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('requests' == $ctl)
				{
					$menu[] = $str11_on;
					$menu[] = $str1;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('training' == $ctl)
				{
					$menu[] = $str11;
					$menu[] = $str1;
					$menu[] = $str13_on;
                    $menu[] = $str7;
				}elseif('profile' == $ctl)
				{
					$menu[] = $str11;
					$menu[] = $str1;
					$menu[] = $str13;
                    $menu[] = $str7_on;
				}
				break;
            case 2: //requests/tickets/projects/workbook/kpi/users  -- for leaders
				if(in_array($ctl, array("index", "tasks")))
				{
					$menu[] = $str11;
                    $menu[] = $str1_on;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('requests' == $ctl)
				{
					$menu[] = $str11_on;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('projects' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3_on;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('workbook' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9_on;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('kpi' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12_on;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('users' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5_on;
					$menu[] = $str13;
                    $menu[] = $str7;
				}elseif('training' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13_on;
                    $menu[] = $str7;
				}elseif('profile' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str3;
					$menu[] = $str9;
					$menu[] = $str12;
                    $menu[] = $str5;
					$menu[] = $str13;
                    $menu[] = $str7_on;
				}
				break;
			case 3: //all --for admin
				if(in_array($ctl, array("index", "tasks")))
				{
					$menu[] = $str11;
                    $menu[] = $str1_on;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('projects' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3_on;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('departments' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4_on;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('users' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5_on;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('workbook' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9_on;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('category' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10_on;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('config' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6_on;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('profile' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7_on;
				}elseif('requests' == $ctl)
				{
					$menu[] = $str11_on;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('kpi' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12_on;
					$menu[] = $str6;
					$menu[] = $str13;
					$menu[] = $str7;
				}elseif('training' == $ctl)
				{
					$menu[] = $str11;
                    $menu[] = $str1;
					$menu[] = $str10;
					$menu[] = $str3;
					$menu[] = $str4;
					$menu[] = $str5;
					$menu[] = $str9;
                    $menu[] = $str12;
					$menu[] = $str6;
					$menu[] = $str13_on;
					$menu[] = $str7;
				}
				break;
			default: //tickets/kpi -- for IT staffs
				if(in_array($ctl, array("index", "tasks")))
				{
                    $menu[] = $str1_on;
					$menu[] = $str13;
					$menu[] = $str12;
					$menu[] = $str7;
				}elseif('training' == $ctl)
				{
                    $menu[] = $str1;
					$menu[] = $str13_on;
					$menu[] = $str12;
					$menu[] = $str7;
				}elseif('kpi' == $ctl)
				{
                    $menu[] = $str1;
					$menu[] = $str13;
					$menu[] = $str12_on;
					$menu[] = $str7;
				}elseif('profile' == $ctl)
				{
                    $menu[] = $str1;
					$menu[] = $str13;
					$menu[] = $str12;
					$menu[] = $str7_on;
				}
				break;
		}
		
		if(!empty($menu))
		{
			$str = implode(" | ", $menu);
		}else
		{
			$str = "";
		}
		
		return $str;
	}
}
