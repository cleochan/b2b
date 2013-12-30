<?php

/**
 * Interact with WebService of CrazySales To Get Product Data from CrazySales
 * @author Tim Wu <TimWu@crazysales.com.au>
 */
class Algorithms_Core_ProductService extends SoapClient{
    
    /**
     * PaginationType
     * @var array
     */
    var $PaginationType =   array(
        'EntriesPerPage'    =>  '',
        'PageNumber'        =>  '',
    );
    
    /**
     * PaginationRequest
     * @var array
     */
    var $PaginationRequest  =   array(
        'DetailsLevel'      =>  '',
        'Pagination'        =>  '',
    );
   
    /**
     * GetProductsRequest
     * @var array
     */
    var $GetProductsRequest =   array(
        'CategoryIDs'       =>  '',
        'Pagination'        =>  '',
    );
    
    /**
     * ProductDetailsLevelType
     * @var array
     */
    var $ProductDetailsLevelType    =   array(
        'AdditionalFlag'    =>  '',
        'OptionFlag'        =>  '',
        'PackageFlag'       =>  '',
        'PictureFlag'       =>  '',
        'ProductCodeFlag'   =>  '',
        'PromotionFlag'     =>  '',
        'PurchaseFlag'      =>  '',
        'ShippingDetailsFlag'   =>  '',
        'WarehouseFlag'     =>  '',
    );
    
    /**
     * Entries Per Page
     * @var int
     */
    var $EntriesPerPage;
    
    /**
     * Current Pages
     * @var int 
     */
    var $PageNumber;
    
    private static $classmap = array();
    
    /**
     * __construct()
     * @param array $options
     */
    function __construct($options = array()) {
        $params_model   =   new Databases_Tables_Params();
        $web_service_url    =   $params_model->GetVal('web_service_url');
        $wsdl   =   $web_service_url."ProductService.svc?wsdl";
        foreach(self::$classmap as $key => $value) {
            if(!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        $options = array( 
            'encoding' => 'UTF-8',
            'soap_version'=>SOAP_1_1, 
            'exceptions'=>true, 
            'trace'=>1, 
            'cache_wsdl'=>WSDL_CACHE_NONE,
            "features" => SOAP_SINGLE_ELEMENT_ARRAYS,
         );
        parent::__construct($wsdl, $options);
    }
    
    /**
     * Change Oject To Array
     * @param array $array
     * @return array $array;
     */
    function object_array($array){
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value){
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }
    
    /**
     * Get Products from CrazySales
     * 
     * All of the flag in ProductDetailsLevelType are true to get all the data of product
     * EntriesPerPage and PageNumber must to give in the controller
     * 
     * @param int $EntriesPerPage Entries Per Page
     * @param int $PageNumber Current Pages
     * @return array $result
     */
    function WebServicesGetProducts()
    {
        set_time_limit(7200);
        $this->ProductDetailsLevelType['AdditionalFlag']    =   1;
        $this->ProductDetailsLevelType['OptionFlag']        =   1;
        $this->ProductDetailsLevelType['PackageFlag']       =   1;
        $this->ProductDetailsLevelType['PictureFlag']       =   1;
        $this->ProductDetailsLevelType['ProductCodeFlag']   =   1;
        $this->ProductDetailsLevelType['PromotionFlag']     =   1;
        $this->ProductDetailsLevelType['PurchaseFlag']      =   1;
        $this->ProductDetailsLevelType['ShippingDetailsFlag']    =   1;
        $this->ProductDetailsLevelType['WarehouseFlag']     =   1;
        $this->PaginationType['EntriesPerPage'] =   $this->EntriesPerPage;
        $this->PaginationType['PageNumber']     =   $this->PageNumber;
        $req    =   $this->GetProductsRequest;
        $req['Pagination']      =   $this->PaginationType;
        $req['DetailsLevel']    =   $this->ProductDetailsLevelType;
        try{
            $response   =   $this->GetProducts(array('request' => $req)); 
        }  catch (Exception $e){
            $response   =   $this->parseRawData($this->__getLastResponse());
        }
        $result     =   $this->object_array($response);
        return $result;
    }
    
    
    /**
     * if the Response from WSDL have error codes, Correct the error codes 
     * @param string $data
     * @return stdClass
     */
	function parseRawData($data)
	{
		// preparing object similar like created with XML SoapClient
		$rawObject = new stdClass();
		$rawObject->GetProductsResult = new stdClass();
		$rawObject->GetProductsResult->Products = new stdClass();
		$rawObject->GetProductsResult->PaginationResult = new stdClass();
		$rawObject->GetProductsResult->Products->CrazySalesProductType = array();
		
		$rawObject->validate = true;
		$rawObject->errors = array();

		$getProductsResultTag = $this->findValueBetweenTags($data, 'GetProductsResult', 'xmlns:a="http://crazysales.com.au/crazysales/messages/" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"');

		if($getProductsResultTag !== false) // RAW XML Data found! Digging for data!!
	      	{
			$PaginationResultTag = $this->findValueBetweenTags($getProductsResultTag, "a:PaginationResult", 'xmlns:b="http://crazysales.com.au/crazysales/entity/"');
	 		
			if($PaginationResultTag !== false)
			{
				$TotalNumberOfEntries = $this->findValueBetweenTags($getProductsResultTag, 'b:TotalNumberOfEntries');
				$TotalNumberOfPages = $this->findValueBetweenTags($getProductsResultTag, 'b:TotalNumberOfPages');

				if($TotalNumberOfEntries === false || $TotalNumberOfPages === false)
				{
					$rawObject->validate = false;
					$rawObject->errors[] = "TotalNumberOfEntries or TotalNumberOfPages not found in raw data!";
				}

				$rawObject->GetProductsResult->PaginationResult->TotalNumberOfEntries = ($TotalNumberOfEntries !== false) ? intval($TotalNumberOfEntries) : 0;
				$rawObject->GetProductsResult->PaginationResult->TotalNumberOfPages = ($TotalNumberOfPages !== false) ? intval($TotalNumberOfPages) : 0;

				unset($TotalNumberOfEntries);
				unset($TotalNumberOfPages);
				unset($PaginationResultTag); // free memory
			}
			else
			{
				$rawObject->validate = false;
				$rawObject->errors[] = "PaginationResult TAG not found in raw data!";	
			}

			$ProductsTag = $this->findValueBetweenTags($getProductsResultTag, "a:Products", 'xmlns:b="http://crazysales.com.au/crazysales/entity/"'); 

			if($ProductsTag !== false) // products array found, diggin!!
			{
				$ProductsTagArray = array();

				// this will check if more than one product on list
				// if more, explode to array, if one or less, simple find value between Tags is enough
				$moreThanOneProduct = stripos($ProductsTag, "</b:CrazySalesProductType><b:CrazySalesProductType>"); 

				if($moreThanOneProduct !== false) // got array, diggin
				{
					$ProductsTagArray = explode("</b:CrazySalesProductType><b:CrazySalesProductType>", $ProductsTag);
					$productsCount = count($ProductsTagArray);

					if($productsCount > 1)
					{
						// need to remember that after explode still first element of array contain <b:CrazySalesProductType> on begin
						// last element of array contain </b:CrazySalesProductType> on the end

						$ProductsTagArray[0] = substr($ProductsTagArray[0], strlen("<b:CrazySalesProductType>"));
						$ProductsTagArray[$productsCount-1] = substr($ProductsTagArray[$productsCount-1], 0, -strlen("</b:CrazySalesProductType>"));
					}
					else // this shouldn't not happend!!!
					{
						$rawObject->validate = false;
						$rawObject->errors[] = "In Products TAG: moreThanOneProduct is less than 2! This shouldn't not happend!";	

						$ProductsTagArray = array();
					}

				}
				else // one or less products found
				{
					$CrazySalesProductTypeTag = $this->findValueBetweenTags($ProductsTag, "b:CrazySalesProductType");

					if($CrazySalesProductTypeTag !== false) // product information found
					{
						$ProductsTagArray[] = $CrazySalesProductTypeTag;
					}
					else
					{
						$rawObject->errors[] = "Products not found in Products TAG!";	
					}
				}

				unset($ProductsTag); // clean memory

				foreach($ProductsTagArray as $CrazySalesProductTypeTag)
				{
					$CrazySalesProductType = new stdClass();
					$CrazySalesProductType->validate = true;
					$CrazySalesProductType->errors = array();
					$CrazySalesProductType->CasePackQuantity = new stdClass();
					$CrazySalesProductType->Category = new stdClass();
					$CrazySalesProductType->Cost = new stdClass();
					$CrazySalesProductType->DiscontinueFlag = new stdClass();
					$CrazySalesProductType->EstimatedHandlingCost = new stdClass();
					$CrazySalesProductType->EstimatedShippingCost = new stdClass();
					$CrazySalesProductType->LastUpdateDate = new stdClass();
					$CrazySalesProductType->ProductDimension = new stdClass();
					$CrazySalesProductType->ProductImages = new stdClass();
					$CrazySalesProductType->ProductWeight = new stdClass();
					$CrazySalesProductType->QuantityAvailable = new stdClass();
					$CrazySalesProductType->ShippingCourier = new stdClass();
					$CrazySalesProductType->StreetPrice = new stdClass();
					$CrazySalesProductType->SupplierPrice = new stdClass();
					$CrazySalesProductType->ProductImages->CrazySalesProductPictureType = array();

					$CrazySalesProductType->AccessorySkus = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:AccessorySkus", false, null);
					$CrazySalesProductType->BinNumber = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:BinNumber", false, null);
					$CrazySalesProductType->Brand = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Brand", false, null);
					$CrazySalesProductType->CasePackDimension = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CasePackDimension", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					
					$CasePackQuantityTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CasePackQuantity", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					if(!is_null($CasePackQuantityTag)) // not null, check inside
					{
						$CrazySalesProductType->CasePackQuantity->Value = $this->findValueBetweenTags($CasePackQuantityTag, "c:Value", false, "1"); 
						unset($CasePackQuantityTag);
					}
					else // not found, shouldn't not happend, set to null
					{
						$CrazySalesProductType->CasePackQuantity->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "CasePackQuantity TAG not found in raw data!";	
					}

					$CrazySalesProductType->Catalog = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Catalog", false, null);
					$CrazySalesProductType->CatalogEndDate = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CatalogEndDate", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->CatalogStartDate = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CatalogStartDate", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					$CategoryTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Category", false, null);

					if(!is_null($CategoryTag))
					{
						$CrazySalesProductType->CategoryID = $this->findValueBetweenTags($CategoryTag, "b:CategoryID", false, "0"); 
						$CrazySalesProductType->CategoryName = $this->findValueBetweenTags($CategoryTag, "b:CategoryName", false, ""); 
						$CrazySalesProductType->ParentID = $this->findValueBetweenTags($CategoryTag, "b:ParentID", false, "0"); 
						$CrazySalesProductType->Url = $this->findValueBetweenTags($CategoryTag, "b:Url", false, null); 
						unset($CategoryTag);
					}
					else
					{	
						$CrazySalesProductType->Url = null;
						$CrazySalesProductType->CategoryName = null;
						$CrazySalesProductType->CategoryID = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "Category TAG not found in raw data!";
					}

					$CostTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Cost", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($CostTag))
					{
						$CrazySalesProductType->Cost->Value = $this->findValueBetweenTags($CasePackQuantityTag, "c:Value", false, "0"); 
						unset($CostTag);
					}
					else
					{
						$CrazySalesProductType->Cost->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "Cost TAG not found in raw data!";
					}

					$CrazySalesProductType->CountryOfOrigin = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CountryOfOrigin", false, null);
					$CrazySalesProductType->CrossSellSkus = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:CrossSellSkus", false, null);
					$CrazySalesProductType->Description = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Description", false, null);

					$DiscontinueFlagTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:DiscontinueFlag", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					if(!is_null($DiscontinueFlagTag))
					{
						$CrazySalesProductType->DiscontinueFlag->Value = $this->findValueBetweenTags($DiscontinueFlagTag, "c:Value", false, "false"); 
						unset($DiscontinueFlagTag);
					}
					else
					{
						$CrazySalesProductType->DiscontinueFlag->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "DiscontinueFlag TAG not found in raw data!";
					}

					$CrazySalesProductType->EAN = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:EAN", false, null);

					$EstimatedHandlingCostTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:EstimatedHandlingCost", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($EstimatedHandlingCostTag))
					{
						$CrazySalesProductType->EstimatedHandlingCost->Value = $this->findValueBetweenTags($EstimatedHandlingCostTag, "c:Value", false, "0"); 
						unset($EstimatedHandlingCostTag);
					}
					else
					{
						$CrazySalesProductType->EstimatedHandlingCost->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "EstimatedHandlingCost TAG not found in raw data!";
					}

					$EstimatedShippingCostTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:EstimatedShippingCost", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($EstimatedShippingCostTag))
					{
						$CrazySalesProductType->EstimatedShippingCost->Value = $this->findValueBetweenTags($EstimatedShippingCostTag, "c:Value", false, "0"); 
						unset($EstimatedShippingCostTag);
					}
					else
					{
						$CrazySalesProductType->EstimatedShippingCost->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "EstimatedShippingCost TAG not found in raw data!";
					}

					$CrazySalesProductType->Features = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Features", false, null);
					$CrazySalesProductType->GTIN = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:GTIN", false, null);
					$CrazySalesProductType->ISBN = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ISBN", false, null);
					$CrazySalesProductType->Keywords = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Keywords", false, null);

					$LastUpdateDateTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:LastUpdateDate", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($LastUpdateDateTag))
					{
						$CrazySalesProductType->LastUpdateDate->Value = $this->findValueBetweenTags($LastUpdateDateTag, "c:Value", false, "0000-00-00T00:00:00"); 
						unset($LastUpdateDateTag);
					}
					else
					{
						$CrazySalesProductType->LastUpdateDate->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "LastUpdateDate TAG not found in raw data!";
					}

					$CrazySalesProductType->MPN = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:MPN", false, null);
					$CrazySalesProductType->Manufacturer = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Manufacturer", false, null);
					$CrazySalesProductType->MaxPurchaseQuantity = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:MaxPurchaseQuantity", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->MaxShippingSingleBox = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:MaxShippingSingleBox", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->MinPurchaseQuantity = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:MinPurchaseQuantity", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->PackageDimension = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:PackageDimension", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->PackageWeight = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:PackageWeight", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);
					$CrazySalesProductType->ProductCodeType = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductCodeType", false, null);
					$CrazySalesProductType->ProductCondition = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductCondition", false, null);

					$ProductDimensionTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductDimension", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($ProductDimensionTag))
					{
						$CrazySalesProductType->ProductDimension->Depth = $this->findValueBetweenTags($ProductDimensionTag, "c:Depth", false, "0"); 
						$CrazySalesProductType->ProductDimension->Length = $this->findValueBetweenTags($ProductDimensionTag, "c:Length", false, "0");
						$CrazySalesProductType->ProductDimension->Units = $this->findValueBetweenTags($ProductDimensionTag, "c:Units", false, "CM");
						$CrazySalesProductType->ProductDimension->Width = $this->findValueBetweenTags($ProductDimensionTag, "c:Width", false, "0");
						unset($ProductDimensionTag);
					}
					else
					{
						$CrazySalesProductType->ProductDimension->Units = null;
						$CrazySalesProductType->ProductDimension->Depth = null;
						$CrazySalesProductType->ProductDimension->Length = null;
						$CrazySalesProductType->ProductDimension->Width = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "ProductDimension TAG not found in raw data!";
					}

					$CrazySalesProductType->ProductID = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductID", false, null);

					$ProductImagesTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductImages");

					if(!is_null($ProductImagesTag))
					{
						$ProductImagesTagArray = array();

						// this will check if more than one product on list
						// if more, explode to array, if one or less, simple find value between Tags is enough
						$moreThanOneProductImage = stripos($ProductImagesTag, "</b:CrazySalesProductPictureType><b:CrazySalesProductPictureType>"); 

						if($moreThanOneProductImage !== false) // got array, diggin
						{
							$ProductImagesTagArray = explode("</b:CrazySalesProductPictureType><b:CrazySalesProductPictureType>", $ProductImagesTag);
							$productImagesCount = count($ProductImagesTagArray);

							if($productImagesCount > 1)
							{
								// need to remember that after explode still first element of array contain <b:CrazySalesProductPictureType> on begin
								// last element of array contain </b:CrazySalesProductPictureType> on the end

								$ProductImagesTagArray[0] = substr($ProductImagesTagArray[0], strlen("<b:CrazySalesProductPictureType>"));
								$ProductImagesTagArray[$productImagesCount-1] = substr($ProductImagesTagArray[$productImagesCount-1], 0, -strlen("</b:CrazySalesProductPictureType>"));
							}
							else // this shouldn't not happend!!!
							{
								$rawObject->validate = false;
								$CrazySalesProductType->errors[] = "In ProductImages TAG: moreThanOneProductImage is less than 2! This shouldn't not happend!";	

								$ProductImagesTagArray = array();
							}

						}
						else // one or less products found
						{
							$CrazySalesProductPictureTypeTag = $this->findValueBetweenTags($ProductImagesTag, "b:CrazySalesProductPictureType");

							if($CrazySalesProductPictureTypeTag !== false) // product information found
							{
								$ProductImagesTagArray[] = $CrazySalesProductPictureTypeTag;
							}
							else
							{
								$CrazySalesProductType->errors[] = "ProductImages not found in ProductImages TAG!";	
							}
						}

						unset($ProductImagesTag); // clean memory

						foreach($ProductImagesTagArray as $productImageTag)
						{
							$CrazySalesProductPictureTypee = new stdClass();

							$CrazySalesProductPictureType->BrandFlag = $this->findValueBetweenTags($productImageTag, "b:BrandFlag", false, "false");
							$CrazySalesProductPictureType->CleanFlag = $this->findValueBetweenTags($productImageTag, "b:CleanFlag", false, "false");
							$CrazySalesProductPictureType->DefalutFlag = $this->findValueBetweenTags($productImageTag, "b:DefalutFlag", false, "false");
							$CrazySalesProductPictureType->Description = $this->findValueBetweenTags($productImageTag, "b:Description", false, null);
							$CrazySalesProductPictureType->ExtraFlag = $this->findValueBetweenTags($productImageTag, "b:ExtraFlag", false, "false");
							$CrazySalesProductPictureType->ImageType = $this->findValueBetweenTags($productImageTag, "b:ImageType", false, "jpg");
							$CrazySalesProductPictureType->LargeSizeFlag = $this->findValueBetweenTags($productImageTag, "b:LargeSizeFlag", false, "false");
							$CrazySalesProductPictureType->MediumSizeFlag = $this->findValueBetweenTags($productImageTag, "b:MediumSizeFlag", false, "false");
							$CrazySalesProductPictureType->Path = $this->findValueBetweenTags($productImageTag, "b:Path", false, "");

							$CrazySalesProductType->ProductImages->CrazySalesProductPictureType[] = $CrazySalesProductPictureType;
						}
						unset($ProductImagesTagArray);
					}
					else
					{
						$CrazySalesProductType->errors[] = "ProductImages TAG not found in raw data!";
					}


					$CrazySalesProductType->ProductName = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductName", false, null);
					$CrazySalesProductType->ProductOptions = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductOptions", false, null);

					$ProductWeightTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ProductWeight", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($ProductWeightTag))
					{
						$CrazySalesProductType->ProductWeight->Units = $this->findValueBetweenTags($ProductWeightTag, "c:Units", false, "KG");
						$CrazySalesProductType->ProductWeight->Value = $this->findValueBetweenTags($ProductWeightTag, "c:Value", false, "0");
						unset($ProductWeightTag);
					}
					else
					{
						$CrazySalesProductType->ProductWeight->Units = null;
						$CrazySalesProductType->ProductWeight->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "ProductWeight TAG not found in raw data!";
					}

					$QuantityAvailableTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:QuantityAvailable", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($QuantityAvailableTag))
					{
						$CrazySalesProductType->ProductWeight->Value = $this->findValueBetweenTags($QuantityAvailableTag, "c:Value", false, "0");
						unset($QuantityAvailableTag);
					}
					else
					{
						$CrazySalesProductType->QuantityAvailable->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "QuantityAvailable TAG not found in raw data!";
					}


					$CrazySalesProductType->RetailerSku = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:RetailerSku", false, null);

					$ShippingCourierTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:ShippingCourier", false, null);

					if(!is_null($ShippingCourierTag))
					{
						$CrazySalesProductType->ShippingCourier->ClassID = $this->findValueBetweenTags($ShippingCourierTag, "b:ClassID", false, "11");
						$CrazySalesProductType->ShippingCourier->Name = $this->findValueBetweenTags($ShippingCourierTag, "b:Name", false, "DHL - Drop Shipping");
						$CrazySalesProductType->ShippingCourier->TrackingUrl = $this->findValueBetweenTags($ShippingCourierTag, "b:TrackingUrl", false, "");
						unset($ShippingCourierTag);
					}
					else
					{
						$CrazySalesProductType->ShippingCourier->ClassID = null;
						$CrazySalesProductType->ShippingCourier->Name = null;
						$CrazySalesProductType->ShippingCourier->TrackingUrl = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "ShippingCourier TAG not found in raw data!";
					}

					$CrazySalesProductType->Specification = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Specification", false, null);

					$StreetPriceTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:StreetPrice", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($StreetPriceTag))
					{
						$CrazySalesProductType->StreetPrice->Value = $this->findValueBetweenTags($StreetPriceTag, "c:Value", false, "0");
						unset($StreetPriceTag);
					}
					else
					{
						$CrazySalesProductType->StreetPrice->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "StreetPrice TAG not found in raw data!";
					}

					$SupplierPriceTag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:SupplierPrice", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"', null);

					if(!is_null($SupplierPriceTag))
					{
						$CrazySalesProductType->SupplierPrice->Value = $this->findValueBetweenTags($SupplierPriceTag, "c:Value", false, "0");
						unset($SupplierPriceTag);
					}
					else
					{
						$CrazySalesProductType->SupplierPrice->Value = null;
						$CrazySalesProductType->validate = false;
						$CrazySalesProductType->errors[] = "SupplierPrice TAG not found in raw data!";
					}


					$CrazySalesProductType->SupplierSku = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:SupplierSku", false, null);
					$CrazySalesProductType->UPC = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:UPC", false, null);
					$CrazySalesProductType->UseFreightFlag = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:UseFreightFlag", 'xmlns:c="http://crazysales.com.au/eds/framework/entity"/', null);
					$CrazySalesProductType->Warranty = $this->findValueBetweenTags($CrazySalesProductTypeTag, "b:Warranty", false, null);
	
					$rawObject->GetProductsResult->Products->CrazySalesProductType[] = $CrazySalesProductType;

				}	
				unset($CrazySalesProductType); 
			}
			else
			{
				$rawObject->validate = false;
				$rawObject->errors[] = "Products TAG not found in raw data!";	
			}

		}  
		else
		{
			$rawObject->errors[] = "GetProductsResult TAG not found in raw data!";
			$rawObject->validate = false;
		}

		return $rawObject;
	}

        /**
         * find error codes in every tag
         * @param sting $string
         * @param sting $tag
         * @param sting $with_attributes
         * @param sting $return_default
         * @return sting
         */
	function findValueBetweenTags($string, $tag, $with_attributes = false, $return_default = false)
	{
		$needle = "<".$tag.(($with_attributes === false) ? ">" : " ");

		$needleLength = strlen($needle);
		$attributeLength = ($with_attributes !== false) ? (int) strlen($with_attributes)+1 : 0; 

		$startIndex = stripos($string, $needle);

		//echo "\nStartIndex: ".$startIndex. ", findMe: ".$needle.", strlen: ".strlen($string)."\n";

		if($startIndex !== false) // found, not null
		{	
			// rewind startIndex: don't want $needle on begin
			$startIndex += ($needleLength+$attributeLength);

			$endIndex = stripos($string, "</".$tag.">", $startIndex);

			//echo "\nendIndex: ".$endIndex. ", arg: </".$tag.">            \n";

			if($endIndex !== false)
			{
				$endIndex -= $startIndex; // endIndex became string Length
				return substr($string, $startIndex, $endIndex);
			}

			// not found, check if not null when with_attributes was false
			if($with_attributes !== false) // atributes was given but still not found? may be null?
			{
				$needle = "<".$tag.' i:nil="true"';

				$prevStartIndex = $startIndex;
				$startIndex = stripos($string, $needle, $prevStartIndex); // start on same place, where we found tag first time

				// startIndex and prevStartIndex should be same, if no, error

				if($startIndex === $prevStartIndex) // found null value
				{
					return null; 
				}
			}
		}

		$needle = "<".$tag.(($with_attributes === false) ? ' i:nil="true"/>' : ' i:nil="true" ');

		if(stripos($string, $needle) !== false) // found null value
		{
			return null; 
		}

		// not found
		return $return_default;
	}
}