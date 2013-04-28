<?php

function object_array($array){
  if(is_object($array)){
    $array = (array)$array;
  }
  if(is_array($array)){
    foreach($array as $key=>$value){
      $array[$key] = object_array($value);
    }
  }
  return $array;
} 

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
$paginationType->EntriesPerPage =   1;
$paginationType->PageNumber     =   1;
$req->Pagination    =   $paginationType;

print_R(array('request' => $req));

$response = $client->GetProducts(array('request' => $req)); 
echo '<pre>';
var_dump(object_array($response));
echo '</pre>';