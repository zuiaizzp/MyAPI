<?php
class ExcelYii{
    private $letter = array ( 'A' , 'B' , 'C' , 'D' , 'E' , 'F' , 'G' , 'H' , 'I' ,
        'J' , 'K' , 'M' , 'N' , 'P' , 'Q' , 'R' , 'S' , 'T' , 'U' , 'V' , 'W' , 'X' , 'Y' , 'Z' );
    public function __construct(){
        include "/home/www/caiwu/phpexcel/PHPExcel.php";
        include "/home/www/caiwu/phpexcel/PHPExcel/Writer/Excel2007.php";
    }
    public function outPutExcel($title,$header,$rows){
        $objPHPExcel = new PHPExcel();
        $l = 0;
        foreach ($header as $value){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($this->letter[$l].'1', $value);
            $l++;
        }
        $i = 2;
        foreach ($rows as $row){
            $l = 0;
            foreach ($header as $value){
                $objPHPExcel->getActiveSheet()->setCellValue($this->letter[$l] . $i, $row[$l]);
                $l++;
            }
            $i++;
        }
        $objPHPExcel->getProperties()->setTitle($title);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function excel2Array($file,$encode='utf-8'){
        $extension = strtolower( pathinfo($file, PATHINFO_EXTENSION) );
        if ($extension =='xlsx') {
            $objReader = new PHPExcel_Reader_Excel2007();
        } else if ($extension =='xls') {
            $objReader = new PHPExcel_Reader_Excel5();
        } else if ($extension=='csv') {
            $objReader = new PHPExcel_Reader_CSV();
        }
        $objPHPExcel = $objReader->load($file);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }
}