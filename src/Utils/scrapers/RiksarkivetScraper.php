<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."libraries/Scraper_base.php";

class Riksarkivet_scraper extends Scraper_base
{

    public function __construct($parameters)
    {
        parent::__construct($parameters);
    }


    public function connect()
    {
        return parent::connect();
    }

    public function parse()
    {
        $baseurl = $this->url;
        $debug = true;
        // Use TagFilter to parse the content.

        $html = TagFilter::Explode($this->result["body"], $this->htmloptions);

        // Retrieve a pointer object to the root node.
        $root = $html->Get();

        $tables = $root->Find(".cssIndentTable");
        $table = null;
        foreach($tables as $candidate){
            $table = $candidate;
        }

        $headers = $table->Find("td.subTblGridHCell");

        $header_num = 0;
        $header_names = array();
        foreach($headers as $header){
            $header_names[$header_num] = $header->GetPlainText();
            $header_num++;
        }

        $rows = $table->Find("tr[class^='subTblGridRow']");

        $row_num = 0;
        $data = array();
        foreach ($rows as $row){
            // Somewhat slower access.
            if($row_num>0) {
            $columns = $row->Find("td.subTblGridCellList");
                if($debug) echo "<b> Row ".$row_num."</b><br>";
            $column_num = 0;
            foreach ($columns as $column) {
                if($debug){echo $column->GetPlainText().", <br>";}
                if (strcmp($header_names[$column_num], "Referenskod")=== 0 ) {
                    $data[$row_num]['abbreviation'] = trim($column->GetPlainText());
                }
                else if (strcmp($header_names[$column_num], "Titel")=== 0 ) {
                    $data[$row_num]['name'] = $column->GetPlainText();
                }
                else if (strcmp($header_names[$column_num], "Tid")=== 0 ) {
                    if($column->Child(0)) {
                        $year_field = $column->Child(0)->GetInnerHTML();
                        if(strpos($year_field, "&ndash;")){
                            $year_array = explode("&ndash;", $year_field);
                            $data[$row_num]['start_date'] = trim($year_array[0]);
                            $data[$row_num]['end_date'] = trim($year_array[1]);
                        }
                        else{
                            $data[$row_num]['start_date'] = trim($year_field);
                        }
                    }
                }
                else if(strcmp($header_names[$column_num], "Anmärkning")=== 0 ) {
                    $data[$row_num]['description'] = $column->GetInnerHTML();
                }
                $column_num++;
            }
            }
            $row_num++;
        }
        return $data;
    }
}