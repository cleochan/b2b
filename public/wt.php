<?php
$url = 'http://10.0.0.186:8743/OrderService.svc?wsdl';
$client = new SoapClient($url, 
	array(
		'trace' => true, 
		'exceptions' => true,
		''
	)
);
//
// $response = $client->SetOrderStatus(array(
//	'request' => array('OrderStatus' => array(
//		array(
//			'OrderNumber' => 37344470,
//			'StatusID' => 3,
//			'Status' => 'Processing',	
//		)
//	))
//));

$response = $client->PlaceOrder(
            array(
                'request' => array(
                    'Orders' => array(
                        array(
                            "AffiliateID" => 1,
                            "BillingAddress_1" => "No.1 Villa Road",
                            "BillingAddress_2" => "Springvale VIC",
                            "BillingCity" => "Melbourne",
                            "BillingCompany" => "ABC",
                            "BillingCountryCode" => "AU",
                            "BillingFirstName" => "Mark",
                            "BillingLastName" => "Chan",
                            "BillingState" => "Victoria",
                            "BillingZipCode" => "3171",
                            "CreditCartCVV" => "123",
                            "CreditCartExpires" => "02/14",
                            "CreditCartHolderName" => "Mark Chan",
                            "CreditCartNmber" => "10000000000000",
                            "CustomerPickupLocationCode" => "3171",
                            "OrderAmount" => 120.90,
                            "OrderDiscount" => 12.95,
                            "OrderNotes" => "ABC",
                            "OrderNumber" => 31405,
                            "PaymentTypeID" => 1,
                            "PointsRate" => 15.30,
                            "RetailerAccountEmail" => "merchant@gmail.com",
                            "ShipAddress_1" => "No.1 Villa Road",
                            "ShipAddress_2" => "Springvale VIC",
                            "ShipAttention" => "ABC",
                            "ShipCarrier" => "DHL",
                            "ShipCity" => "Melbourne",
                            "ShipCompany" => "ABC",
                            "ShipCountryCode" => "AU",
                            "ShipEmail" => "markchan@gmail.com",
                            "ShipFirstName" => "Mark",
                            "ShipInstructions" => "ABC",
                            "ShipLastName" => "Chan",
                            "ShipMethod" => "DHL",
                            "ShipPhone" => "06412345678",
                            "ShipState" => "Victoria",
                            "ShipTitle" => "Mr.",
                            "ShipZipCode" => "3171",
                            "ShippingCost" => 9.95,
                            "ShippingServiceInfo" => "ABC",
                            "SiteID" => 1,
                            "OrderItems" => array(
                                array(
                                        "Dimension" => array(10.00, 10.00, "19", 10.00),
                                        "ExpectedItemCost" => 50.95,
                                        "FinalItemCost" => 45.95,
                                        "FinalShipCost" => 9.95,
                                        "FreeShipping" => FALSE,
                                        "ItemSku" => "FQ 330",
                                        "Notes" => "Ship ASAP!",
                                        "OrderItemNumber" => 1,
                                        "OrderNumber" => 12,
                                        "Quantity" => 1,
                                        "ShipCarrier" => "DHL",
                                        "ShipCost" => 9.95,
                                        "ShipDate" => "2013-02-02 10:00:00",
                                        "ShipMethod" => "DHL",
                                        "TrackingNumber" => "ABCDE",
                                        "Weight" => array(12, 2.00)
                                    )
                            )
                        )
                    )
                )
            )
        );

echo '<pre>';
var_dump($response);
echo '</pre>';


// *  <<<<<RETURN>>>>>>
// * 
// * object(stdClass)#2 (1) {
//  ["PlaceOrderResult"]=>
//  object(stdClass)#3 (2) {
//    ["Messages"]=>
//    object(stdClass)#4 (1) {
//      ["MessageType"]=>
//      object(stdClass)#5 (4) {
//        ["Action"]=>
//        string(34) " check input argument."
//        ["Created"]=>
//        string(33) "2013-03-28T21:04:56.3660859+11:00"
//        ["Description"]=>
//        string(31) "Incoming order request is null."
//        ["Level"]=>
//        string(5) "Error"
//      }
//    }
//    ["Orders"]=>
//    NULL
//  }
//}
 


