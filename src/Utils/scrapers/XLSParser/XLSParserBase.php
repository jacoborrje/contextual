<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */


namespace App\Utils\Scrapers\XLSParser;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Doctrine\ORM\EntityManagerInterface;

class XLSParserBase
{

    protected $pathname, $reader, $spreadsheet, $worksheet;
    protected $highestRow, $highestColumn, $highestColumnIndex;
    protected $entityManager;


    public function __construct($parameters, EntityManagerInterface $entityManager)
    {
        $this->pathname = $parameters['file_path'];
        $this->reader = IOFactory::createReaderForFile($this->pathname);
        $this->reader->setReadDataOnly(true);
        $this->spreadsheed = $this->reader->load($this->pathname);
        $this->worksheet = $this->spreadsheet->getActiveSheet();

        $this->highestRow = $this->worksheet->getHighestRow();
        $this->highestColumn = $this->worksheet->getHighestColumn();
        $this->highestColumnIndex = $highestColumnIndex = Coordinate::columnIndexFromString($this->highestColumn);
        $this->entityManager = $entityManager;
    }

    public function parse($accepted_fields)
    {

    }

}