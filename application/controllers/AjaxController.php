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
    
    /**
     * Update Invoice Comments
     */
    function updateInvoiceCommentsAction(){
        $params =   $this->_request->getParams();
        $invoice_model  =   new Databases_Tables_InvoiceList();
        $invoice_model->invoice_id  =   $params['invoice_id'];
        $invoice_model->comments    =   $params['comments'];
        $result =   $invoice_model->updateComments();
        if($result){
            echo json_encode(array('result_data'=>'1','invoice_id'=>$params['invoice_id'],'comments'=>$params['comments']));
        }else{
            echo json_encode(array('result_data'=>'0'));
        }
        die;
    }
    
    function updateDdProductStatusAction(){
        $update_product_ids =   array();
        $params =   $this->_request->getParams();
        $update_product_ids[]   =   $params['product_id'];
        $dd_products_model  =   new Databases_Tables_DdProducts();
        $dd_products_model->product_id_array    =   $update_product_ids;
        $dd_products_model->status              =   $params['status'];
        $result             =   $dd_products_model->updateProductStatusWithStatus();
        if($result){
            $dd_products_model->product_id  =   $params['product_id'];
            $dd_product_info                =   $dd_products_model->getDdProductInfo();
            if($dd_product_info){
                echo json_encode(array('result'=>'1','product_id'=>$params['product_id'],'product_status'=>$dd_product_info['status']));
            }else{
                echo json_encode(array('result'=>'0'));
            }
        }
        die;
    }
    
    function updateDdProductDataAction(){
        $update_product_ids =   array();
        $params =   $this->_request->getParams();
        $update_product_ids[]   =   $params['product_id'];
        $dd_products_model  =   new Databases_Tables_DdProducts();
        $dd_products_model->product_id    =   $params['product_id'];
        $dd_products_model->data_name         =   $params['data_name'];
        $dd_products_model->data_value        =   $params['data_value'];
        $result             =   $dd_products_model->updateDdProductData();
        if($result){
            $dd_products_model->product_id  =   $params['product_id'];
            $dd_product_info                =   $dd_products_model->getDdProductInfo();
            if($dd_product_info){
                echo json_encode(array('result'=>'1','product_data'=>$dd_product_info->toArray()));
            }else{
                echo json_encode(array('result'=>'0'));
            }
        }else{
            echo json_encode(array('result'=>'0'));
        }
        die;
    }
    
    public function saveFileAction(){
        $params     =   $this->_request->getParams();
        $search_sku =   $params['sku_file'];
        $contents   =   $params['desc_contents'];
        $system_params_model    =   new Databases_Tables_Params();
        $logs_path  =   $system_params_model->GetVal('merchant_feed_txt_path');
        $file_path  =   $logs_path.$search_sku.".txt";
        $contents   =   preg_replace("/\n/i", "\r\n", $contents);
        $contents   =   iconv('UTF-8', 'ISO-8859-1', $contents);
        //exit($contents);
        if(file_put_contents($file_path, $contents)>0){
            echo json_encode(array('result'=>1));        
        }else{
            echo json_encode(array('result'=>10));   
        }
        die;
    }
    
    public function publishFileAction(){
        $params     =   $this->_request->getParams();
        $search_sku =   $params['sku_file'];
        $contents   =   $params['desc_contents'];
        $system_params_model    =   new Databases_Tables_Params();
        $logs_path  =   $system_params_model->GetVal('merchant_feed_txt_path');
        $file_path  =   $logs_path.$search_sku.".txt";
        $contents   =   preg_replace("/\n/i", "\r\n", $contents);
        $contents   =   iconv('UTF-8', 'ISO-8859-1', $contents);
        if(file_put_contents($file_path, $contents)>0){
            echo json_encode(array('result'=>1));        
        }else{
            echo json_encode(array('result'=>0));   
        }
        $merchant_ftp_array     =   array(
            'ftp_host'      =>  'interface.dealsdirect.com.au',
            'ftp_port'      =>  '21',
            'ftp_user'      =>  'tp_crazysales',
            'ftp_pass'      =>  '3Ws5maLm',
            'txt_path'      =>  'outgoing/inventory/descriptions/',
        );
        $ftp                =   new Algorithms_Core_Ftp($merchant_ftp_array['ftp_host'], $merchant_ftp_array['ftp_port'], $merchant_ftp_array['ftp_user'], $merchant_ftp_array['ftp_pass']);
        $ftp_server_path    =   $merchant_ftp_array['txt_path'].$search_sku.".txt";
        $ftp->up_file($ftp_server_path, $file_path);
        $ftp->close();
        die;
    }
}

