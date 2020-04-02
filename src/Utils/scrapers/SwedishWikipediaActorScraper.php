<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use App\Entity\Place;
use App\Utils\GeographyHelper;
use App\Utils\PlaceService;
use Doctrine\ORM\EntityManagerInterface;
use \IntlDateFormatter;
use App\Utils\Scrapers\WikipediaActorScraperBase;

class SwedishWikipediaActorScraper extends WikipediaActorScraperBase
{

    public function __construct(EntityManagerInterface $em, GeographyHelper $geographyHelper, PlaceService $placeService)
    {
        $lang_array = ['Death date and age' => '[Dd]öd datum och ålder',
            'Birth date' => '[Ff]ödelsedatum',
            'Death date' => '[Dd]ödsdatum',
            'Birth place' => '[Ff]ödelseplats',
            'Death place' => '[Dd]ödsplats',
            'men' => 'Män',
            'women' => 'Kvinnor',
            'category' => 'Kategori',
            'born' => '[Ff]ödd',
            'dead' => '[Dd]öd'
        ];

        $dateFormatter = new IntlDateFormatter(
            'sv_SE',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'GMT',
            IntlDateFormatter::GREGORIAN,
            'yyyy-MM-dd'
        );

        $this->combined_patterns =
            [
                '/{{'.$lang_array['Death date and age'].'\|(?P<death_year>\d+)\|(?P<death_month>\d+)\|(?P<death_day>\d+)\|(?P<birth_year>\d+)\|(?P<birth_month>\d+)\|(?P<birth_day>\d+)\|/',
                '/{{'.$lang_array['Death date and age'].'\|(?P<death_year>\d+)\|(?P<death_month>\d+)\|(?P<death_day>\d+)\|(?P<birth_year>\d+)\|(?P<birth_month>\d+)\|(?P<birth_day>\d+)}}/',
                '/\((?P<birth_month>\w+) (?P<birth_day>\d+), (?P<birth_year>\d+), \[\[(?P<birth_place>[\wüåäö]+)]] – (?P<death_month>\w+) (?P<death_day>\d+), (?P<death_year>\d+), \[\[(?P<death_place>[\wåäöü]+)\]\]\)+/'
            ];

        $this->singular_birthdate_patterns =
            [
                '/{{'.$lang_array['Birth date'].'\|df=yes\|(?P<year>\d+)\|(?P<month>\d+)\|(?P<day>\d+)}}/',
                '/\| '.$lang_array['Birth date'].' = (?P<year>\d\d\d\d)/',
                '/[Ff]ödd\s+(\[\[)?(?P<day>\d+)?\s+(?P<month>\w+)(]])?\s+(\[\[)?(\[\[)?(?P<year>\d\d\d\d)(]])?\s+i\s+(\[\[)?(?P<birth_place>[\w\såäöüÅÄÖÜ]+)(]])?/',
            ];

        $this->singular_death_date_patterns =
            [
                '/[Dd]öd\s+(\[\[)?(?P<day>\d+)\s+(?P<month>\w+)(]])?\s+(\[\[)?(?P<year>\d\d\d\d)(]])?\s+i\s+(\[\[)?(?P<death_place>[\w\såäöÅÄÖüÜéÉèÈ]+)(\|(?P<death_place_parent>[\w\såäöÅÄÖüÜéÉèÈ]+))?(]])?/'
            ];

        parent::__construct('sv', $lang_array, $dateFormatter, $em, $geographyHelper, $placeService);
    }
}
?>