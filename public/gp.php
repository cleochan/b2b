<?php
$url = 'http://10.0.0.186:8743/ProductService.svc?wsdl';
$client = new SoapClient($url);

class products{ 
  public $CategoryIDs; // int 
}


class PaginationType {
  public $EntriesPerPage; // int
  public $PageNumber; // int
}

$pagtype = new PaginationType();
$pagtype->EntriesPerPage=1;
$pagtype->PageNumber=1;

class GetProductsRequest {
  public $ProductRequests; 
}


class GetProductsResponse {
  public $Product; 
}

$product=new products();
$product->CategoryIDs="";
$product->Pagination =$pagtype;

$req = new GetProductsRequest();
$req->ProductRequests = array($product);

$response = $client->GetProducts(array('request' => $req)); 
echo '<pre>';
var_dump($response);
echo '</pre>';