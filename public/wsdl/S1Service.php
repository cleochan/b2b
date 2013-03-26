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
class S1Service extends SoapClient {

  private static $classmap = array(
                                   );

  public function S1Service($wsdl = "s1.wsdl", $options = array()) {
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
            'uri' => 'http://example.org/StockQuote',
            'soapaction' => ''
           )
      );
  }

}

?>
