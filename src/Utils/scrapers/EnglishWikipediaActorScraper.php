<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

//TODO: create the English scraper with matching patterns.

namespace App\Utils\Scrapers;
use \IntlDateFormatter;
use App\Utils\Scrapers\WikipediaActorScraperBase;

class EnglishWikipediaActorScraper extends WikipediaActorScraperBase
{

    public function __construct($em)
    {
        $lang_array = ['Death date and age' => '[Dd]öd datum och ålder',
            'Birth date' => '[Ff]ödelsedatum',
            'Death date' => '[Dd]ödsdatum',
            'Birth place' => '[Ff]ödelseplats',
            'Death place' => '[Dd]ödsplats',
            'men' => 'Men',
            'women' => 'Women',
            'category' => 'Category',
            'born' => '[Bb]orn',
            'dead' => '[Dd]ead'
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
                '/\| '.$lang_array['Birth date'].' = (?P<year>\d+)/',
                '/' . $lang_array['born'] . ' (\[\[(?P<day>\d+)?\s(?P<month>\w+)?[\]]+\s)?\[\[(?P<year>\d\d\d\d)]]\s+i\s+(?P<birth_place>[\w\såäöüÅÄÖÜ]+)/',
                '/' . $lang_array['born'] . '\s+((?P<day>\d+)\s+(?P<month>\w+)\s+)?(?P<year>\d\d\d\d)(\s+i\s+\[\[(?P<birth_place>[\w\såäöüÅÄÖÜ]+)]])?/'
            ];

        $this->singular_death_date_patterns =
            [
                '/' . $lang_array['dead'] . '\s+((?P<day>\d+)\s+(?P<month>\w+)\s+)?(?P<year>\d\d\d\d)(\s+i\s+\[\[(?P<death_place>[\w\såäöüÅÄÖÜ]+)]])?/',
                '/' . $lang_array['dead'] . ' (\[\[(?P<day>\d+)?\s(?P<month>\w+)?[\]]+\s)?\[\[(?P<year>\d\d\d\d)]]/'
            ];

        parent::__construct($em, 'en', $lang_array, $dateFormatter);
    }
}
?>
