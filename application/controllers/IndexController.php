<?php

class IndexController extends Zend_Controller_Action
{
	
    function preDispatch()
    {
        echo "Invalid Action.";die;
    }
}

