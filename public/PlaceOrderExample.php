<?php
$url = 'http://203.213.123.150:8743/OrderService.svc?wsdl';
$client = new SoapClient($url);

class MoneyType {
  public $Value; // decimal
}

class DimensionType {
  public $Depth; // double
  public $Length; // double
  public $Units; // string
  public $Width; // double
}

class BooleanType {
  public $Value; // boolean
}

class QuantityType {
  public $Value; // int
}

class DateTimeType {
  public $Value; // dateTime
}

class WeightType {
  public $Units; // string
  public $Value; // double
}

class MessageType {
  public $Action; // string
  public $Created; // dateTime
  public $Description; // string
  public $Level; // string
}

class CrazySalesOrderType {
  public $AffiliateID; // int
  public $BillingAddress_1; // string
  public $BillingAddress_2; // string
  public $BillingCity; // string
  public $BillingCompany; // string
  public $BillingCountryCode; // string
  public $BillingFirstName; // string
  public $BillingLastName; // string
  public $BillingState; // string
  public $BillingZipCode; // string
  public $CreditCartCVV; // string
  public $CreditCartExpires; // string
  public $CreditCartHolderName; // string
  public $CreditCartNumber; // string
  public $CustomerPickupLocationCode; // string
  public $OrderAmount; // MoneyType
  public $OrderDiscount; // MoneyType
  public $OrderItems; // ArrayOfCrazySalesOrderItemType
  public $OrderNotes; // string
  public $OrderNumber; // int
  public $PaymentTypeID; // int
  public $PointsRate; // float
  public $RetailerAccountEmail; // string
  public $ShipAddress_1; // string
  public $ShipAddress_2; // string
  public $ShipAttention; // string
  public $ShipCarrier; // string
  public $ShipCity; // string
  public $ShipCompany; // string
  public $ShipCountryCode; // string
  public $ShipEmail; // string
  public $ShipFirstName; // string
  public $ShipInstructions; // string
  public $ShipLastName; // string
  public $ShipMethod; // string
  public $ShipPhone; // string
  public $ShipState; // string
  public $ShipTitle; // string
  public $ShipZipCode; // string
  public $ShippingCost; // MoneyType
  public $ShippingServiceInfo; // string
  public $SiteID; // int
}

class CrazySalesOrderItemType {
  public $Dimension; // DimensionType
  public $ExpectedItemCost; // MoneyType
  public $FinalItemCost; // MoneyType
  public $FinalShipCost; // MoneyType
  public $FreeShipping; // BooleanType
  public $ItemSku; // string
  public $Notes; // string
  public $OrderItemNumber; // int
  public $OrderNumber; // int
  public $Quantity; // QuantityType
  public $ShipCarrier; // string
  public $ShipCost; // MoneyType
  public $ShipDate; // DateTimeType
  public $ShipMethod; // string
  public $TrackingNumber; // string
  public $Weight; // WeightType
}

class CrazySalesOrderStatusType {
  public $AdminID; // int
  public $Comment; // string
  public $Level; // int
  public $OrderNumber; // int
  public $StatusName; // string
}

class PlaceOrderRequest {
  public $OrderRequests; // ArrayOfCrazySalesOrderType
}

class PlaceOrderResponse {
  public $Orders; // ArrayOfCrazySalesOrderType
}

class GeneralResponse {
  public $Messages; // ArrayOfMessageType
}

class PlaceOrder {
  public $request; // PlaceOrderRequest
}

$order = new CrazySalesOrderType();
$order->RetailerAccountEmail = 'unmind@gmail.com';
$order->PaymentTypeID = 6;
$order->ShipFirstName = "unmind";
$order->ShipAddress_1 = "12 Springvale Rd";
$order->ShipAddress_2 = "";
$order->ShipCity = "Springvale";
$order->ShipState = "VIC";
$order->ShipZipCode = "3172";
$order->ShipCountryCode = "AU";
$order->ShipPhone = "123321123";

$orderAmount = new MoneyType();
$orderAmount->Value = 200;
$order->OrderAmount = $orderAmount;

$order1 = new CrazySalesOrderType();
$order1->RetailerAccountEmail = 'unmind@gmail.com';
$order1->PaymentTypeID = 6;
$order1->ShipFirstName = "unmind";
$order1->ShipAddress_1 = "12 Springvale Rd";
$order1->ShipAddress_2 = "";
$order1->ShipCity = "Springvale";
$order1->ShipState = "VIC";
$order1->ShipZipCode = "3172";
$order1->ShipCountryCode = "AU";
$order1->ShipPhone = "123321123";

$order1->OrderAmount = $orderAmount;

$order2 = new CrazySalesOrderType();
$order2->RetailerAccountEmail = 'unmind@gmail.com';
$order2->PaymentTypeID = 6;
$order2->ShipFirstName = "unmind";
$order2->ShipAddress_1 = "12 Springvale Rd";
$order2->ShipAddress_2 = "";
$order2->ShipCity = "Springvale";
$order2->ShipState = "VIC";
$order2->ShipZipCode = "3172";
$order2->ShipCountryCode = "AU";
$order2->ShipPhone = "123321123";

$order2->OrderAmount = $orderAmount;

$orderItem = new CrazySalesOrderItemType();
$cost = new MoneyType();
$cost->Value = 10.25;
$orderItem->ExpectedItemCost = $cost;
$orderItem->FinalItemCost = $cost;
$qty = new QuantityType();
$qty->Value = 1;
$orderItem->Quantity = $qty;
$orderItem->ItemSku = "RABBIT2";

$orderItem1 = new CrazySalesOrderItemType();
$cost1 = new MoneyType();
$cost1->Value = 11.25;
$orderItem1->ExpectedItemCost = $cost;
$orderItem1->FinalItemCost = $cost;
$qty = new QuantityType();
$qty->Value = 1;
$orderItem1->Quantity = $qty;
$orderItem1->ItemSku = "RABBIT2";

$orderItem2 = new CrazySalesOrderItemType();
$cost2 = new MoneyType();
$cost2->Value = 12.25;
$orderItem2->ExpectedItemCost = $cost;
$orderItem2->FinalItemCost = $cost;
$qty = new QuantityType();
$qty->Value = 1;
$orderItem2->Quantity = $qty;
$orderItem2->ItemSku = "RABBIT2";

$order->OrderItems = array($orderItem,$orderItem1,$orderItem2);
$order1->OrderItems = array($orderItem1);
$order2->OrderItems = array($orderItem2);

$req = new PlaceOrderRequest();
$req->OrderRequests = array($order);
$response = $client->PlaceOrder(array('request' => $req)); 

echo '<pre>';
var_dump($response);
echo '</pre>';