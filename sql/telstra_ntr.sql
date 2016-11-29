drop table telstra_ntr;

CREATE TABLE `telstra_ntr` (
  `seq` varchar(15) NOT NULL,
  `ProductBillingIdentifier` varchar(8) NOT NULL,
  `BillingElementCode` varchar(8) NOT NULL,
  `ActivityCompletedIndicator` varchar(1) NOT NULL,
  `ServChargeItemGrp` varchar(20) NOT NULL,
  `UnbilledIndicator` varchar(1) NOT NULL,
  `GSTPercentageRate` float(7,4) NOT NULL,
  `RateStructureStartDate` date NOT NULL,
  `RateStructureEndDate` date NOT NULL,
  `AmountSignIndicator` char(1) NOT NULL,
  `UnitRate` float(15,7) NOT NULL,
  `BillingElementCategoryCode` char(1) NOT NULL,
  `Description` varchar(94) NOT NULL,
  `UnitHighQuantity` int(11) NOT NULL,
  `UnitLowQuantity` int(11) NOT NULL,
  `CAID` int(11) NOT NULL,
  `AgreementStartDate` date NOT NULL,
  `AgreementEndDate` date NOT NULL,
  `TariffChangeIndicator` char(1) NOT NULL,
  KEY `seq` (`seq`)
);