<?php

class ScheduledController extends Zend_Controller_Action
{
    function indexAction()
    {
        echo "Invalid Action";
        die;
    }
    
    function placeOrderAction()
    {
        $logs_orders = new Databases_Tables_LogsOrders();
        $logs_orders->PostXmlToPlaceOrder();
        
        die;
    }
    
    function refreshProductsAction()
    {
        /**
         * truncate the secondary table
         * send initial request
         */
        $params_model = new Databases_Tables_Params();
        $secondary = $params_model->GetSecondaryProductTableName();
        
        if($secondary['table_num'])
        {
            $product_filter_model = new Databases_Joins_ProductFilter();
            if($product_filter_model->TruncateProductTable($secondary['table_num']))
            {
                $product_filter_model->PostXmlToRefreshProducts();
            }
        }
        
        echo "End.";
        die;
    }
}