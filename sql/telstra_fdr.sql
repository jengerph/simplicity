drop table telstra_fdr;

CREATE TABLE `telstra_fdr` (
  `InterfaceFileTypeCode` varchar(3) NOT NULL,
  `FileSourceCode` varchar(5) NOT NULL,
  `FileRegistrationSequenceNumber` varchar(4) NOT NULL,
  `FileEventDate` date NOT NULL,
  `FileCreatDdate` date NOT NULL,
  PRIMARY KEY (`FileRegistrationSequenceNumber`)
) ;