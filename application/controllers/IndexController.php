<?php

class IndexController extends Zend_Controller_Action
{
	
    function preDispatch()
    {
        $params_model    =   new Databases_Tables_Params();
        $running_mode   =   $params_model->GetVal('running_mode');
        if($running_mode=='production' && $_SERVER["HTTPS"]<>'on'){
            header('Location: https://' . $_SERVER['HTTP_HOST'] . '/merchant');
        }else{
            $this->_redirect('/merchant');
        }
    }
}

