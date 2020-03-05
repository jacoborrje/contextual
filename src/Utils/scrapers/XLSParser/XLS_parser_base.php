<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once  APPPATH.'third_party/PHPExcel/Classes/PHPExcel/IOFactory.php';


class XLS_parser_base
{

    public function __construct($parameters)
    {
        $this->filePath = $parameters['file_path'];
        $this->objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $this->objReader->setReadDataOnly(true);
        $this->objPHPExcel = $this->objReader->load($this->filePath);
        $this->objWorksheet = $this->objPHPExcel->getActiveSheet();

        $this->highestRow = $this->objWorksheet->getHighestRow();
        $this->highestColumn = $this->objWorksheet->getHighestColumn();
        $this->highestColumnIndex = PHPExcel_Cell::columnIndexFromString($this->highestColumn);

    }

    public function parse($accepted_fields)
    {

    }
}