<?php

class AjaxController extends Zend_Controller_Action
{
  
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
        $auth = Zend_Auth::getInstance();
        if(!$auth->hasIdentity())
        {
            $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
        }
    }
	
    function indexAction()
    {
        echo "Invalid Action";
        die;
    }
    
    function treeAffectOfCategoryAction()
    {
        $params = $this->_request->getParams();
        
        if($params['UserID'] && $_SESSION['b2b']['merchant_feed_generation'][$params['UserID']])
        {
            $product_categories_model = new Databases_Tables_ProductCategories();
            $get_child_array = $product_categories_model->GetChildIdArray($params['TreeID'], $_SESSION['b2b']['merchant_feed_generation'][$params['UserID']]);
            
            if(!empty($get_child_array))
            {
                foreach($_SESSION['b2b']['merchant_feed_generation'][$params['UserID']] as $entire_tree_key => $entire_tree_val)
                {
                    if(in_array($entire_tree_val['category_id'], $get_child_array))
                    {
                        $_SESSION['b2b']['merchant_feed_generation'][$params['UserID']][$entire_tree_key]['checked'] = $params['Checked'];
                    }
                }
            }
            
            if($params['ParentID'])
            {
                //update parent status
                $get_parent_status = $product_categories_model->GetParentStatus($params['ParentID'], $_SESSION['b2b']['merchant_feed_generation'][$params['UserID']]);

                foreach($_SESSION['b2b']['merchant_feed_generation'][$params['UserID']] as $entire_tree_key => $entire_tree_val)
                {
                    if($entire_tree_val['category_id'] == $params['ParentID'])
                    {
                        $_SESSION['b2b']['merchant_feed_generation'][$params['UserID']][$entire_tree_key]['checked'] = $get_parent_status;
                    }
                }
            }

        }else{
            echo "Invalid Action";
            die;
        }
    }
    
    function refreshFeedAction()
    {
        $params = $this->_request->getParams();
        
        if($params['UserId'])
        {
            $model = new Algorithms_Core_Feed();
            $model->user_id_array = array($params['UserId']);
            $result = $model->Publish();
            echo $result;
        }else{
            echo "Error: UserId missed.";
        }
        
        die;
    }
    
    /**
     * Admin Refresh Pending Orders
     */
    function adminRefreshPendingOrdersAction()
    {
        /**
         * @var $operate_orders_model class Databases_Joins_OperateOrders
         * @todo PlaceOrder and SetOrderStatus
         */
        $operate_orders_model   =   new Databases_Joins_OperateOrders();
        $result = $operate_orders_model->PlaceOrder();

        $getorders_model = new Databases_Joins_GetOrders();
		$getorders_model->item_status  =   0;
        /*Get Pending Order Info*/
        $result['recent_orders_list'] = $getorders_model->PushList();

        echo Zend_Json::encode($result);
        die();
    }
    
    /**
     * Refresh Pending Orders
     * place pending order
     * get order pending order list
     * 
     */
    function refreshPendingOrdersAction()
    {
        $operate_orders_model   =   new Databases_Joins_OperateOrders();
        $result = $operate_orders_model->PlaceOrder();

        $getorders_model = new Databases_Joins_GetOrders();
	$getorders_model->item_status  =   0;
        $getorders_model->user_id      =    $this->params['user_id'];
        /*Get Pending Order Info*/
        $result['recent_orders_list'] = $getorders_model->PushList();

        echo Zend_Json::encode($result);
        die();
    }
}

