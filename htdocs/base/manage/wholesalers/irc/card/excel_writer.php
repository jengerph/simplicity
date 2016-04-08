<?php

function php_excel($band){
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("X Integration");
$objPHPExcel->getProperties()->setLastModifiedBy("X Integration");
$objPHPExcel->getProperties()->setTitle("X Integration International Rate Card Template");
$objPHPExcel->getProperties()->setSubject("International Rate Card Template");
$objPHPExcel->getProperties()->setDescription("This is X Integration's International Rate Card Template");


// Add some data
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Band');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Destination');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Cost');

$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(43);
$worksheet = $objPHPExcel->getActiveSheet();

for ($i=0; $i < count($band); $i++) { 
    $worksheet->setCellValueByColumnAndRow(0, $i+2, trim($band[$i]["band"]));
    $worksheet->setCellValueByColumnAndRow(1, $i+2, trim($band[$i]["destination"]));
    $worksheet->setCellValueByColumnAndRow(2, $i+2, trim($band[$i]["cost"]));
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Rate Card Template');

		
// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="International Rate Card Template.xls"');
$objWriter->save('php://output');
}

function read_file($file) {
 //Check valid spreadsheet has been uploaded
if(isset($file)){
    $inputFile = $file['tmp_name'];
    // $extension = strtoupper(pathinfo($inputFile, PATHINFO_EXTENSION));
    $extension = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
        if($extension == 'XLSX' || $extension == 'ODS'){

            //Read spreadsheeet workbook
            try {
                 $inputFileType = PHPExcel_IOFactory::identify($inputFile);
                 $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                     $objPHPExcel = $objReader->load($inputFile);
            } catch(Exception $e) {
                    die($e->getMessage());
            }

            //Get worksheet dimensions
            $sheet = $objPHPExcel->getSheet(0); 
            $highestRow = $sheet->getHighestRow(); 
            $highestColumn = $sheet->getHighestColumn();

            //Loop through each row of the worksheet in turn
            for ($row = 1; $row <= $highestRow; $row++){ 
                    //  Read a row of data into an array
                    $rowData[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                    //Insert into database
            }
            return $rowData;
        }
    }
}