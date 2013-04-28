<?php
$url = 'http://10.0.0.186:8743/ProductService.svc?wsdl';
$client = new SoapClient($url);

class PaginationType{
    public $EntriesPerPage;
    public $PageNumber;
}

class PaginationRequest{
    public $DetailsLevel;
    public $Pagination;
}

class GetProductsRequest extends PaginationRequest {
  public $CategoryIDs; 
}
//$product->Category = array($orderItem);

$req = new GetProductsRequest();
$paginationType =   new PaginationType();
$paginationType->EntriesPerPage =   100;
$paginationType->PageNumber     =   1;
$req->Pagination    =   $paginationType;

print_R(array('request' => $req));

$response = $client->GetProducts(array('request' => $req)); 
echo '<pre>';
var_dump($response);
echo '</pre>';