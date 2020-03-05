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
use App\Entity\Source;
use App\Entity\Action;
use App\Entity\Actor;
use App\Entity\Place;
use App\Entity\Correspondent;
use App\Entity\Mention;
use App\Entity\Volume;
use App\Entity\Institution;
use \DateTime;
use \Exception;

use App\Utils\Scrapers\WikipediaActorScraperBase;

class SwedishWikipediaActorScraper extends WikipediaActorScraperBase
{

    public function __construct($em)
    {
        $lang_array = ['Death date and age' => '[Dd]öd datum och ålder',
            'Birth date' => '[Ff]ödd',
            'Death date' => '[Dd]öd',
            'Birth place' => '[Ff]ödelseplats',
            'Death place' => '[Dd]ödsplats'];

        parent::__construct($em, 'sv', $lang_array);
    }
}
?