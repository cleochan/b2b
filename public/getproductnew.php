<?php
$url = 'http://203.213.123.150:8753/ProductService.svc?wsdl';
$client = new SoapClient($url);

class PaginationType{
    public $EntriesPerPage;
    public $PageNumber;
}

class PaginationRequest{
    public $DetailsLevel;
    public $Pagination;
}

class ProductDetailsLevelType{
    public $AdditionalFlag;
    public $OptionFlag;
	public $PackageFlag;
	public $PictureFlag;
	public $ProductCodeFlag;
	public $PromotionFlag;
	public $PurchaseFlag;
	public $ShippingDetailsFlag;
	public $WarehouseFlag;
}


class GetProductsRequest extends PaginationRequest {
  public $CategoryIDs; 
}
//$product->Category = array($orderItem);

$req = new GetProductsRequest();
$paginationType =   new PaginationType();
$paginationType->EntriesPerPage =   1;
$paginationType->PageNumber     =   370;

$productDetailsLeveltype =   new ProductDetailsLevelType();
$productDetailsLeveltype->AdditionalFlag =  1;
$productDetailsLeveltype->OptionFlag     =   1;
$productDetailsLeveltype->PackageFlag =   1;
$productDetailsLeveltype->PictureFlag     =   1;
$productDetailsLeveltype->ProductCodeFlag =   1;
$productDetailsLeveltype->PromotionFlag     =   1;
$productDetailsLeveltype->PurchaseFlag =   1;
$productDetailsLeveltype->ShippingDetailsFlag     =   1;
$productDetailsLeveltype->WarehouseFlag =   1;


$req->Pagination    =   $paginationType;
$req->DetailsLevel    =   $productDetailsLeveltype;

print_R(array('request' => $req));

$response = $client->GetProducts(array('request' => $req)); 
echo '<pre>';
var_dump($response);
echo '</pre>';