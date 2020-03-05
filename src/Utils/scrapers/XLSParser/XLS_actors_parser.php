<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."libraries/XLS_parser_base.php";

class XLS_actors_parser extends XLS_parser_base
{

    public function __construct($parameters)
    {
        $this->CI = & get_instance();
        $this->CI->load->model('place_model');

        parent::__construct($parameters);
    }

    public function parse($accepted_fields)
    {
        print_r($accepted_fields);
        echo "<br><br>";
        $field_names = array();
        for($row = 1; $row < 2; ++$row){
            for ($col = 0; $col <= $this->highestColumnIndex; ++$col) {
                $header_value = $this->objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                if(in_array($header_value, $accepted_fields)) {
                    echo '"' . $header_value . '"' . " is in accepted fields.<br>";
                    $field_names[$col] = $header_value;
                }
            }
        }

        print_r($field_names);
        echo "<br><br>";

        $data = array();
        for($row = 2; $row <= $this->highestRow; ++$row){
            foreach($field_names as $key=>$field_name){
                if(strcmp($field_name, "birth_place")===0){
                    $place_id = $this->CI->place_model->get_place_id_from_name($this->objWorksheet->getCellByColumnAndRow($key, $row)->getValue());
                    if($place_id){
                        $data[$row][$field_name] = $place_id;
                    }
                }
                else if (strcmp($field_name, "place_of_death")===0){
                    $place_id  = $this->CI->place_model->get_place_id_from_name($this->objWorksheet->getCellByColumnAndRow($key, $row)->getValue());
                    if($place_id){
                        $data[$row][$field_name] = $place_id;
                    }
                }
                else {
                    $data[$row][$field_name] = $this->objWorksheet->getCellByColumnAndRow($key, $row)->getValue();
                }
            }
        }
        return $data;
    }
}