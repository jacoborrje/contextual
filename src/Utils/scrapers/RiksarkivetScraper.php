<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Utils\FileUploader;


use App\Utils\ScraperBase;

class RiksarkivetScraper extends ScraperBase
{

    public function parse()
    {
        $this->debug = false;

        $this->crawler = $this->crawler->filter(".cssIndentTable")->last();

        $headers = $this->crawler->filter("td.subTblGridHCell");

        $header_num = 0;
        $this->header_names = array();
        foreach($headers as $header){
            $this->header_names[$header_num] = $header->textContent;
            $header_num++;
        }


        $rows = $this->crawler->filter("tr[class^='subTblGridRow']");

        $this->row_num = 0;
        $this->column_num = 0;
        $this->data = array();

        $rows->each(function (Crawler $row, $i) {
            // Somewhat slower access.
            if($this->row_num>0) {
                if($this->debug) echo "<b> Row ".$this->row_num."</b><br>";
                $this->column_num = 0;
                $row->filter('td')->each(function (Crawler $column, $i) {
                    if($this->debug){echo $column->text().", <br>";}
                    if (strcmp($this->header_names[$this->column_num], "Referenskod")=== 0 ) {
                        $this->data[$this->row_num]['abbreviation'] = trim($column->text());
                    }
                    else if (strcmp($this->header_names[$this->column_num], "Titel")=== 0 ) {
                        $this->data[$this->row_num]['name'] = $column->text();
                    }
                    else if (strcmp($this->header_names[$this->column_num], "Tid")=== 0 ) {
                        if($column->children(0)) {
                            $year_field = $column->children(0)->text();
                            if(strpos($year_field, "–")){
                                $year_array = explode("–", $year_field);
                                if(strpos($year_array[0], "talet")!==false)
                                    $year_array[0] = substr($year_array[0], 0,4);
                                if(strpos($year_array[1], "talet")!==false)
                                    $year_array[1] = substr($year_array[1], 0,4);
                                $this->data[$this->row_num]['start_date'] = trim($year_array[0]);
                                $this->data[$this->row_num]['end_date'] = trim($year_array[1]);
                            }
                            else{
                                $year_field = trim($year_field);
                                if(strpos($year_field, "talet")!== false)
                                    $year_field = substr($year_field, 0,4);
                                $this->data[$this->row_num]['start_date'] = $year_field;
                            }
                        }
                    }
                    else if(strcmp($this->header_names[$this->column_num], "Anmärkning")=== 0 ) {
                        $this->data[$this->row_num]['description'] = $column->text();
                    }
                    $this->column_num++;
            });
            }
            $this->row_num++;
        });
        return $this->data;
    }
}