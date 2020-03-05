<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers\XLSParser;

use App\Utils\Scrapers\XLSParser\XLSParserBase;


class XLSVolumesParser extends XLSParserBase
{

    public function __construct($parameters, $entityManager)
    {
        parent::__construct($parameters, $entityManager);
    }

    public function parse($accepted_fields)
    {
        $field_names = array();
        for($row = 1; $row < 2; ++$row){
            for ($col = 0; $col <= $this->highestColumnIndex; ++$col) {
                $header_value = $this->worksheet->getCellByColumnAndRow($col, $row)->getValue();
                if(in_array($header_value, $accepted_fields)) {
                    echo '"' . $header_value . '"' . " is in accepted fields.<br>";
                    $field_names[$col] = $header_value;
                }
            }
        }

        print_r($field_names);

        $data = array();
        for($row = 2; $row <= $this->highestRow; ++$row){
            foreach($field_names as $key=>$field_name){
                $data[$row][$field_name] = $this->worksheet->getCellByColumnAndRow($key, $row)->getValue();
            }
        }
        return $data;
    }
}