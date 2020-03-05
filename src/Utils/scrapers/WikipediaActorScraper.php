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
use App\Entity\Correspondent;
use App\Entity\Mention;
use App\Entity\Volume;
use App\Entity\Institution;
use \DateTime;
use \Exception;

use App\Utils\ScraperBase;

class AlvinScraper extends ScraperBase
{

    protected $url, $client, $crawler, $html, $em, $actor_repository, $alvin_volume, $alvin_id, $alvin_url;
    protected $alvin_volume_id = "863";

    public function __construct($em)
    {
        $this->client = new Client();
        $this->em = $em;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->institution_repository = $this->em->getRepository(Institution::class);
        $this->alvin_volume = $this->em->getRepository(Volume::class)->find($this->alvin_volume_id);
    }

    public function connect($alvin_id)
    {
        $this->crawler =  $this->client->request('GET', "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-record:".$alvin_id);
        $this->alvin_id = $alvin_id;
        $this->alvin_url = "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-record:".$alvin_id;
        return true;
    }

    public function parse()
    {
        $this->debug = false;

        $this->source = new Source();
        $this->source->setVolume($this->alvin_volume);
        $this->source->setPlaceInVolume($this->alvin_id);

        $title = trim($this->crawler->filter('div.recordHeader')->children('h1.ltr')->text());
        $this->source->setTitle($title);

        $place_element = $this->crawler->filter('a[href*="alvin-place"]');
        $place_string = $place_element->text().$place_element->getNode(0)->nextSibling->textContent;
        $place_array = explode(',', $place_string);

        $date_string = end($place_array);

        $this->text_date = $this->parseDate($title, $date_string);
        $this->source->setTextDate($this->text_date);
        $this->source->setDateByString($this->text_date);

        try {
            $abstract = $this->crawler->filterXPath('//h2[text()[contains(.,"Abstract")]]/following-sibling::div')->children()->children()->children()->html();
            $this->source->setExcerpt('<h3>Abstract</h3><p>'.$abstract.'</p>');
        }
        catch (Exception $e){

        }
        try {
            $physical_description = trim($this->crawler->filterXPath('//h2[text()[contains(.,"Physical description")]]')->getNode(0)->nextSibling->nextSibling->nextSibling->nextSibling->textContent);
            $this->source->setResearchNotes('<h3>Physical Description</h3>' . $physical_description);
            $notes = $this->crawler->filterXPath('//h2[text()[contains(.,"Notes")]]/following-sibling::div')->children()->children()->children()->html();
            $this->source->setResearchNotes($source->getResearchNotes() . '<h3>Notes</h3>' . $notes);
        }
        catch (Exception $e){

        }
        $this->source->setResearchNotes($this->source->getResearchNotes().'<p>Imported from <a href="'.$this->alvin_url.'">'.$this->alvin_url.'</a></p>');
        try {
            $transcription = $this->crawler->filterXPath('//h2[text()[contains(.,"Transcription")]]/following-sibling::div')->children()->children()->children()->html();
            $this->source->setTranscription($transcription);
        }
        catch (Exception $e){

        }

        $actor_names = [];
        $iterator = 0;

        $actor_elements = $this->crawler->filterXPath('//h2[text()[contains(.,"Persons")]]/following-sibling::div/div/ul');

        //echo $actor_elements->html();

        $this->iterator = 0;
        try {
            $actor_elements->children()->each(function (Crawler $element, $i) {
                //echo $this->iterator.": ".$element->text();
                $actor = null;
                //echo $element->textContent;
                if (strpos($element->text(), ",") !== false) {
                    $content_array = explode(", ", $element->text());
                    //print_r($content_array);
                    $fields['surname'] = $content_array[0];
                    $fields['first_name'] = $content_array[1];
                    //echo $this->iterator . ": ";
                    //print_r($fields);
                    $actor = $this->actor_repository->findOneBy($fields);
                    if (is_null($actor))
                        $actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);
                } else {
                    $content_array = explode(' (', $element->text());
                    $fields['name'] = $content_array[0];
                    $institution = $this->institution_repository->findOneBy($fields);
                }

                if (!is_null($actor) || !is_null($institution)) {
                    $sender = new Action();
                    if (!is_null($actor))
                        $sender->setCorrespondent($actor->getCorrespondent());
                    else if (!is_null($institution))
                        $sender->setCorrespondent($institution->getCorrespondent());
                    if (strpos(end($content_array), 'author') !== false) {
                        $sender->setType(1);
                        if (!empty($this->text_date))
                            $sender->setStartDateByString($this->text_date);
                    } else if (strpos(end($content_array), 'addressee'))
                        $sender->setType(2);
                    $this->source->addAction($sender);
                }
                //$actor_names[$this->iterator] = $element->text();
                $this->iterator++;
            });
        }
        catch (Exception $e){

        }

        try {
            $mentioned_actor_elements = $this->crawler->filterXPath('//h2[text()[contains(.,"Subject, persons")]]/following-sibling::div/div/ul');

            //echo $mentioned_actor_elements->html();

            $this->iterator = 0;
            $mentioned_actor_elements->children()->each(function (Crawler $element, $i) {
                //echo "<br>".$this->iterator.": ".$element->text();
                $actor = null;
                $institution = null;
                if (strpos($element->text(), ",") !== false) {
                    $content_array = explode(", ", $element->text());
                    $fields['surname'] = $content_array[0];
                    $fields['first_name'] = $content_array[1];
                    $actor = $this->actor_repository->findOneBy($fields);
                    if (is_null($actor))
                        $actor = $this->actor_repository->findOneByAltSurnamesAndAltFirstNames($fields);
                } else {
                    $fields['name'] = $element->text();
                    $institution = $this->institution_repository->findOneBy($fields);
                }

                if (!is_null($actor) || !is_null($institution)) {
                    $mention = new Mention();
                    if (!is_null($actor))
                        $mention->setActor($actor);
                    else if (!is_null($institution))
                        $mention->setInstitution($institution);
                    $this->source->addMention($mention);
                }
                //$mentioned_actor_names[$this->iterator] = $element->text();
                $this->iterator++;
            });
        }
        catch (Exception $e){

        }

        return $this->source;
    }

    private function parseDate($title_field, $origin_field){
        $text_date = "";
        $found_date = false;
        $alt_months = [
            ['September' => ['9br']]
            ];

        if ((strpos($title_field, 'Letter') === 0)){
            $text_date = substr($title_field, 7, strlen($title_field));
            $text_date = trim(explode(',', $text_date)[0]);

            $myDateTime = DateTime::createFromFormat("j F Y", $text_date);
            if($myDateTime->format("Y-m-d")!==false) {
                $text_date = $myDateTime->format("Y-m-d");
                $found_date = true;
            }
        }
        return $text_date;
    }

    public function getAlvinId()
    {
        return $this->alvin_id;
    }

}