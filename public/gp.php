<?php
$url = 'http://10.0.0.186:8743/ProductService.svc?wsdl';
$client = new SoapClient($url);

class MoneyType {
  public $Value; // decimal
}
class QuantityType {
  public $Value; // int
}

class MessageType {
  public $Action; // string
  public $Created; // dateTime
  public $Description; // string
  public $Level; // string
}

class CategoryType{ 
  public $CategoryIDs; // int 
}
$cattype=new CategoryType();
$cattype->CategoryIDs=336;

class PaginationType {
  public $EntriesPerPage; // int
  public $PageNumber; // int
}



        
$pagtype = new PaginationType();
$pagtype->EntriesPerPage=100;
$pagtype->PageNumber=1;

class GetProductsRequest {
  public $ProductRequests; 
}

class Pagination{ 
  public $PaginationType; // int 
}
$pag=new Pagination();
$pag->PaginationType=array($PaginationType);

class GetProductsResponse {
  public $Product; 
}

$cattype->PaginationType = array($pagtype);

//$product->Category = array($orderItem);

$req = new GetProductsRequest();

$req->ProductRequests = array($pag);

$response = $client->GetProducts(array('request' => $req)); 
echo '<pre>';
var_dump($response);
echo '</pre>';