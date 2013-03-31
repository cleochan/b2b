<?php
$url = 'http://10.0.0.186:8743/CategoryService.svc?wsdl';
$client = new SoapClient($url, 
	array(
		'trace' => true, 
		'exceptions' => true,
		''
	)
);

$response = $client->GetCategory(
        array('categoryRequest' => array(
                'GetCategoryRequest' => array(
                    'Pagination' => array(50, 1)
                )
            )
        )
        );

echo '<pre>';
var_dump($response);
echo '</pre>';