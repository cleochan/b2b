<?php

/**
 * S1Service class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class Algorithms_Core_S1Service extends SoapClient {

  private static $classmap = array(
                                   );

  public function S1Service($wsdl = "http://demo.local.b2b/wsdl/s1.wsdl", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param string $symbol
   * @return UNKNOWN
   */
  public function S1($symbol) {
    return $this->__soapCall('S1', array($symbol),       array(
            'uri' => 'http://demo.local.b2b/wsdl/s1.wsdl',
            'soapaction' => ''
           )
      );
    
    $a = array(
    "Orders" => array(
    0 => array(
            "AffiliateID" => "",
            "BillingAddress_1" => "No.1 Villa Road",
            "BillingAddress_2" => "Springvale VIC",
            "BillingCity" => "Melbourne",
            "BillingCompany" => "ABC",
            "BillingCountryCode" => "AU",
            "BillingFirstName" => "Mark",
            "BillingLastName" => "Chan",
            "BillingState" => "Victoria",
            "BillingZipCode" => "3171",
            "CreditCartCVV" => "",
            "CreditCartExpires" => "",
            "CreditCartHolderName" => "",
            "CreditCartNmber" => "",
            "CustomerPickupLocationCode" => "",
            "OrderAmount" => 120.90,
            "OrderDiscount" => 12.95,
            "OrderNotes" => "",
            "OrderNumber" => 31405,
            "PaymentTypeID" => 3,
            "PointsRate" => "",
            "RetailerAccountEmail" => "merchant@gmail.com",
            "ShippingAddress_1" => "No.1 Villa Road",
            "ShippingAddress_2" => "Springvale VIC",
            "ShippingAttention" => "",
            "ShippingCarrier" => "",
            "ShipCity" => "Melbourne",
            "ShippingCompany" => "ABC",
            "ShipCountryCode" => "AU",
            "ShipEmail" => "markchan@gmail.com",
            "ShipFirstName" => "Mark",
            "ShipInstructions" => "",
            "ShipLastName" => "Chan",
            "ShipMethod" => "",
            "ShipPhone" => "06412345678",
            "ShipState" => "Victoria",
            "ShipTitle" => "Mr.",
            "ShipZipCode" => "3171",
            "ShippingCost" => "9.95",
            "ShippingServiceInfo" => "",
            "SiteID" => "",
            "OrderItems" => array(
                0 => array(
                        "Dimension" => "",
                        "ExpectedItemCost" => "",
                        "FinalItemCost" => 45.95,
                        "FinalShipCost" => 9.95,
                        "FreeShipping" => FALSE,
                        "ItemSku" => "FQ 330",
                        "Notes" => "Ship ASAP!",
                        "OrderItemNumber" => 1120,
                        "OrderNumber" => 31405,
                        "Quantity" => 1,
                        "ShipCarrier" => "",
                        "ShipCost" => 9.95,
                        "ShipDate" => "",
                        "ShipMethod" => "",
                        "TrackingNumber" => "",
                        "Weight" => ""
                    ),
                1 => array(
                        "Dimension" => "",
                        "ExpectedItemCost" => "",
                        "FinalItemCost" => 52.95,
                        "FinalShipCost" => 8.90,
                        "FreeShipping" => FALSE,
                        "ItemSku" => "AK 460",
                        "Notes" => "",
                        "OrderItemNumber" => 1121,
                        "OrderNumber" => 31405,
                        "Quantity" => 2,
                        "ShipCarrier" => "",
                        "ShipCost" => 8.90,
                        "ShipDate" => "",
                        "ShipMethod" => "",
                        "TrackingNumber" => "",
                        "Weight" => ""
                    )
            )
        )
    )
);
  }

}

?>
