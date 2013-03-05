<?php

class Databases_Tables_ProductInfo1 extends Zend_Db_Table
{
    protected $_name = 'product_info_1';
    
    function Abc()
    {
        for($n=1;$n<1000;$n++)
        {
            $param = mt_rand(1, 1000);
            
            $data = array(
                "product_id" => $n,
                "supplier_sku" => "SKU ".$param,
                "brand" => array_rand(array(1,2,3)),
                "mpn" => "MPN ".$param,
                "stock" => $param,
                "offer_price" => ($param+1000),
                "cost_price" => ($param+mt_rand(500, 800)),
                "product_name" => "Product Name ".$param,
                "features1" => "Features 1 ".$param,
                "features2" => "Features 2 ".$param,
                "features3" => "Features 3 ".$param,
                "features4" => "Features 4 ".$param,
                "features5" => "Features 5 ".$param,
                "product_details" => "Product Details ".$param,
                "specification" => "Specification ".$param,
                "dimension" => "Dimension ".$param,
                "colour" => "Colour ".$param,
                "size" => "Size ".$param,
                "factory_url" => "http://www.gmail".$param.".com",
                "package_content" => "Package Content ".$param,
                "warranty" => "Warranty ".$param,
                "category" => array_rand(array(45,46,47,50,53,57,61,62,65)),
                "weight" => $param,
                "image_url_1" => "http://www.image1".$param.".com",
                "image_url_2" => "http://www.image2".$param.".com",
                "image_url_3" => "http://www.image3".$param.".com",
                "image_url_4" => "http://www.image4".$param.".com",
                "image_url_5" => "http://www.image5".$param.".com",
                "pm" => "PM ".$param,
                "options" => "Option ".$param,
                "search_keyword" => "Search Keyword ".$param,
                "list_price" => ($param+1100),
                "shipping" => mt_rand(10, 50)
            );

            $this->insert($data);
        }

        return TRUE;
    }
}