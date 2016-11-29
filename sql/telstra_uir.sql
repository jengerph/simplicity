drop table telstra_uir;

CREATE TABLE `telstra_uir` (
  `seq` varchar(15) NOT NULL,
  `EventFileInstanceId` varchar(8) NOT NULL,
  `EventRecordSequenceNumber` varchar(8) NOT NULL,
  `InputLinxOnlineeBillFileId` varchar(8) NOT NULL,
  `ProductBillingIdentifier` varchar(8) NOT NULL,
  `BillingElementCode` varchar(8) NOT NULL,
  `InvoiceArrangementId` varchar(10) NOT NULL,
  `ServicearrangementID` varchar(10) NOT NULL,
  `FullNationalNumber` varchar(29) NOT NULL,
  `OriginatingNumber` varchar(25) NOT NULL,
  `DestinationNumber` varchar(25) NOT NULL,
  `OriginatingDate` date NOT NULL,
  `OriginatingTime` time NOT NULL,
  `ToArea` varchar(12) NOT NULL,
  `UnitofMeasureCode` varchar(5) NOT NULL,
  `Quantity` float(13,5) NOT NULL,
  `CallDuration` varchar(9) NOT NULL,
  `CallTypecode` varchar(3) NOT NULL,
  `RecordType` char(1) NOT NULL,
  `Price` float(15,7) NOT NULL,
  `DistanceRangeCode` varchar(4) NOT NULL,
  `ClosedUserGroupID` varchar(5) NOT NULL,
  `ReversalChargeIndicator` char(1) NOT NULL,
  `1900CallDescription` varchar(30) NOT NULL,
  KEY `OriginatingNumber` (`OriginatingNumber`)
);