 drop table telstra_ser;
 
 CREATE TABLE `telstra_ser` (
  `seq` varchar(15) NOT NULL,
  `CustomerProductItemID` varchar(10) NOT NULL,
  `InputLinxOnlineeBillFileID` varchar(10) NOT NULL,
  `ProductBillingIdentifier` varchar(10) NOT NULL,
  `BillingElementCode` varchar(10) NOT NULL,
  `InvoiceArrangementID` varchar(10) NOT NULL,
  `ServicearrangementID` varchar(10) NOT NULL,
  `FullNationalNumber` varchar(29) NOT NULL,
  `ProductActionCode` varchar(10) NOT NULL,
  `PurchaseOrderNumber` varchar(10) NOT NULL,
  `FormattedProductEffectiveDate` date NOT NULL,
  `UnitRateExclGST` float(15,7) NOT NULL,
  `ItemQuantity` float(9,2) NOT NULL,
  `OrderNegotiatedRateIndicator` char(1) NOT NULL,
  `BillingTransactionDescription` varchar(50) NOT NULL,
  `ProductDescriptionText1` varchar(30) NOT NULL,
  `ProductDescriptionText2` varchar(30) NOT NULL,
  `ProductDescriptionText3` varchar(30) NOT NULL,
  `DataServiceType` varchar(4) NOT NULL,
  `Bandwidth` varchar(10) NOT NULL,
  `ChargeZone` varchar(25) NOT NULL,
  `ServiceLocation1` varchar(60) NOT NULL,
  `ServiceLocation2` varchar(60) NOT NULL,
  `AendServiceNbr` varchar(19) NOT NULL,
  `Aenddlci` varchar(4) NOT NULL,
  `Aendcir` varchar(7) NOT NULL,
  `Aendvpi` varchar(4) NOT NULL,
  `Aendvci` varchar(5) NOT NULL,
  `BendServiceNbr` varchar(19) NOT NULL,
  `Benddlci` varchar(4) NOT NULL,
  `Bendcir` varchar(7) NOT NULL,
  `Bendvpi` varchar(4) NOT NULL,
  `Bendvci` varchar(5) NOT NULL,
  KEY `seq` (`seq`)
);