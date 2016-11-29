drop table telstra_ocr;

CREATE TABLE `telstra_ocr` (
  `seq` varchar(15) NOT NULL,
  `CustomerProductItemID` varchar(10) NOT NULL,
  `InputLinxOnlineeBillFileID` varchar(8) NOT NULL,
  `ProductBillingIdentifier` varchar(8) NOT NULL,
  `BillingElementCode` varchar(8) NOT NULL,
  `InvoiceArrangementID` varchar(10) NOT NULL,
  `ServicearrangementID` varchar(10) NOT NULL,
  `FullNationalNumber` varchar(29) NOT NULL,
  `PurchaseOrderNumber` varchar(16) NOT NULL,
  `FormattedProductEffectiveDate` date NOT NULL,
  `AmountSignIndicator` char(1) NOT NULL,
  `UnitRateExclGST` float(15,7) NOT NULL,
  `ServiceOrderItemQuantity` float(5,2) NOT NULL,
  `OrderNegotiatedRateIndicator` char(1) NOT NULL,
  `TotalInstalmentQuantity` varchar(2) NOT NULL,
  `BillingTransactionDescription` varchar(50) NOT NULL,
  `ProductDescriptionText1` varchar(30) NOT NULL,
  `ProductDescriptionText2` varchar(30) NOT NULL,
  `ProductDescriptionText3` varchar(30) NOT NULL,
  `Price` float(15,7) NOT NULL,
  KEY `seq` (`seq`)
);