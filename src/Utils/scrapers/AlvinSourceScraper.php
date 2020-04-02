<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

namespace App\Utils\Scrapers;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Source;
use App\Entity\Action;
use App\Entity\Actor;
use App\Entity\Place;
use App\Entity\DatabaseFile;
use App\Entity\Correspondent;
use App\Entity\Mention;
use App\Entity\Volume;
use App\Entity\Institution;
use \DateTime;
use \Exception;
use \Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Utils\ScraperBase;
use App\Utils\Scrapers\AlvinActorScraper;
use App\Utils\Scrapers\AlvinPlaceScraper;

class AlvinSourceScraper extends ScraperBase
{

    protected $url, $client, $crawler, $html, $em, $actor_repository, $place_repository, $source_repository, $alvin_volume, $alvin_id, $alvin_url, $rootDir, $source;
    protected $alvin_volume_id = "863";
    protected $author_place, $recipient_place;
    protected $image_tiff_paths;
    protected $text_date;

    public function __construct(EntityManagerInterface $em, AlvinPlaceScraper $alvinPlaceScraper, AlvinActorScraper $alvinActorScraper, FileUploader $fileUploader, $rootDir)
    {
        $this->client = new Client();
        $this->em = $em;
        $this->rootDir = $rootDir;
        $this->actor_repository = $this->em->getRepository(Actor::class);
        $this->source_repository = $this->em->getRepository(Source::class);
        $this->place_repository =  $this->em->getRepository( Place::class);
        $this->institution_repository = $this->em->getRepository(Institution::class);
        $this->alvin_volume = $this->em->getRepository(Volume::class)->find($this->alvin_volume_id);
        $this->alvinPlaceScraper = $alvinPlaceScraper;
        $this->alvinActorScraper = $alvinActorScraper;
        $this->fileUploader = $fileUploader;
    }

    public function connect($alvin_id)
    {
        $this->crawler =  $this->client->request('GET', "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-record:".$alvin_id);
        $this->alvin_id = $alvin_id;
        $existing_source = $this->source_repository->findByAlvinID($alvin_id);
        if(!is_null($existing_source)){
            throw new Exception("The source with id ".$alvin_id." has already been imported from Alvin.");
        }
        else {
            $this->alvin_url = "https://www.alvin-portal.org/alvin/view.jsf?pid=alvin-record:" . $alvin_id;
            return true;
        }
    }

    public function parse()
    {
        $this->debug = false;

        $this->source = new Source();
        $this->source->setVolume($this->alvin_volume);
        $this->source->setPlaceInVolume($this->alvin_id);
        $this->source->setAlvinId($this->alvin_id);

        $title = trim($this->crawler->filter('div.recordHeader')->children('h1.ltr')->text());
        $this->source->setTitle($title);

        $this->author_place = null;
        $this->recipient_place = null;

        if ((strpos($title, 'Letter') === 0)){
            $title_array = explode(", ", $title);
            if(count($title_array)>1){
                $author_place_string = trim(explode(" to", $title_array[1])[0]);
                echo "Author place string: ".$author_place_string."<br>";
                $this->author_place = $this->place_repository->findOneBy(['name' => $author_place_string]);
                echo "Found author place: ". $this->author_place . "<br>";
            }
            if(count($title_array)>2) {
                $recipient_place_string = trim(($title_array[2]));
                $this->recipient_place = $this->place_repository->findOneBy(['name' => $recipient_place_string]);
                echo "Found recipient place: " . $this->recipient_place . "<br>";
            }
            $this->source->setType(1);
        }

        try{
            $place_element = $this->crawler->filter('a[href*="alvin-place"]');
            $place_string = $place_element->text().$place_element->getNode(0)->nextSibling->textContent;
            $place_array = explode(',', $place_string);
            $date_string = end($place_array);
            $date_string = explode(" (", $date_string)[0];
            $this->text_date = $this->parseDate($title,  $date_string);

            $this->source->setTextDate($this->text_date);
            $this->source->setDateByString($this->text_date);
        }
        catch (Exception $e){
            echo "Found no place information. <br>";
        }
        if(!isset($date_string)){
            $origin = trim($this->crawler->filterXPath('//h2[text()[contains(.,"Origin")]]')->getNode(0)->nextSibling->nextSibling->nextSibling->textContent);
            echo $origin;
            $date_string = explode(" ", $origin)[0];

            $this->text_date = $this->parseDate(" ",  $date_string);
            $this->source->setTextDate($this->text_date);
            $this->source->setDateByString($this->text_date);

        }

        try {
            $abstract = $this->crawler->filterXPath('//h2[text()[contains(.,"Abstract")]]/following-sibling::div')->children()->children()->children()->html();
            $this->source->setExcerpt('<h3>Abstract</h3><p>'.$abstract.'</p>');
        }
        catch (Exception $e){

        }
        try {
            $physical_description = trim($this->crawler->filterXPath('//h2[text()[contains(.,"Physical description")]]')->getNode(0)->nextSibling->nextSibling->nextSibling->nextSibling->textContent);
            $this->source->setResearchNotes('<h3>Physical Description</h3>' . $physical_description);
            //$notes = $this->crawler->filterXPath('//h2[text()[contains(.,"Notes")]]/following-sibling::div/div/ul/li');
            $notes = $this->crawler->filterXPath('//div[preceding-sibling::h2[1][text()[contains(.,"Notes")]]]/div/ul/li');
            $this->notes_string = "";
            $notes->each(function (Crawler $element, $i) {
                $this->notes_string .= "<p>".$element->text()."</p>";
            });
            $notes = $this->notes_string;
            echo "<br>The found notes are: ".$notes."<br>";
            $this->source->setResearchNotes($this->source->getResearchNotes() . '<h3>Notes</h3>' . $notes);
        }
        catch (Exception $e){
            echo $e->getMessage()."<br>";
        }
        $this->source->setResearchNotes($this->source->getResearchNotes().'<p>Imported from <a href="'.$this->alvin_url.'">'.$this->alvin_url.'</a></p>');
        try {
            $transcription = $this->crawler->filterXPath('//h2[text()[contains(.,"Transcription")]]/following-sibling::div')->children()->children()->children()->html();
            $this->source->setTranscription($transcription);
        }
        catch (Exception $e){

        }
            $actionActor = $this->crawler->filterXPath('//h2[text()[contains(.,"Persons")]]/following-sibling::div[1]/div/ul/li');

            $actionActor->each(function (Crawler $element, $i) {
                $actor = null;
                echo $element->html()."<br>";
                $href = $element->filterXPath("//a")->attr('href');
                echo $href . "<br>";
                $content_array = explode(", ", $element->text());
                //echo $i.": ".$href;
                if (strpos($element->text(), ",") !== false) {
                    $actionActorId = explode("%3A", $href)[1];
                    //echo " ".$actionActorId;
                    $this->alvinActorScraper->connect($actionActorId);
                    $actor = $this->alvinActorScraper->parse();
                    //print_r($actor);
                } else {
                    $content_array = explode(' (', $element->text());
                    $fields['name'] = $content_array[0];
                    $institution = $this->institution_repository->findOneBy($fields);
                    print_r($content_array);
                }
                if (!is_null($actor) || !is_null($institution)) {
                    $source_action = new Action();
                    if (!is_null($actor)) {
                        if(is_null($actor->getCorrespondent())){
                            echo "Actor does not have a correspondent!<br>";
                        }
                        $source_action->setCorrespondent($actor->getCorrespondent());
                        echo "Added correspondent: ".$source_action->getCorrespondentText()."<br>";
                    }
                    else if (!is_null($institution))
                        $source_action->setCorrespondent($institution->getCorrespondent());

                    echo end($content_array);
                    if (strpos(end($content_array), 'author') !== false) {
                        $source_action->setType(1);
                        if (!empty($this->text_date)) {
                            $source_action->setStartDateByString($this->text_date);
                        }
                        if(!is_null($this->author_place)){
                            $source_action->setPlace($this->author_place);
                        }
                    }
                    else if (strpos(end($content_array), 'addressee') !== false) {
                        $source_action->setType(2);
                        if(isset($this->recipient_place)){
                            $source_action->setPlace($this->recipient_place);
                        }
                    }
                    else if (strpos(end($content_array), 'signer') !== false) {
                        $source_action->setType(3);
                        if (!empty($this->text_date))
                            $source_action->setStartDateByString($this->text_date);

                    }
                    $this->source->addAction($source_action);
                }
            });


        try {
            $this->iterator = 0;

            $mentioned_actor_hrefs = $this->crawler->filterXPath('//h2[text()[contains(.,"Subject, persons")]]/following-sibling::div[1]/div/ul/li/a');
            echo "<br>Found ". $mentioned_actor_hrefs->count()." mentions in the source.<br>";

            $mentioned_actor_hrefs->each(function (Crawler $element, $i) {
                //echo "<br>".$this->iterator.": ".$element->text();
                $actor = null;
                $institution = null;
                $href = $element->attr('href');
                $content_array = explode(", ", $element->parents()->text());
                if (strpos($element->text(), ",") !== false) {
                    $actionActorId = explode("%3A", $href)[1];
                    //echo " ".$actionActorId;
                    try {
                        $this->alvinActorScraper->connect($actionActorId);
                        $this->alvinActorScraper->addSource($this->source);
                        $actor = $this->alvinActorScraper->parse();
                    }
                    catch(exception $e){
                        echo $e->getMessage()."<br>";
                    }
                    //print_r($actor);
                }
                else {
                    $content_array = explode(' (', $element->text());
                    $fields['name'] = $content_array[0];
                    $institution = $this->institution_repository->findOneBy($fields);
                }

                if (!is_null($actor) || !is_null($institution)) {
                    $mention = new Mention();
                    if (!is_null($actor))
                        $mention->setActor($actor);
                    else if (!is_null($institution))
                        $mention->setInstitution($institution);
                    $mention->setDate($this->source->getRawDate());
                    $mention->setDateAccuracy($this->source->getDateAccuracy());
                    $this->source->addMention($mention);
                }
                //$mentioned_actor_names[$this->iterator] = $element->text();
                $this->iterator++;
            });
        }
        catch (Exception $e){

        }
        $this->image_tiff_paths = [];
        $this->image_jpg_paths = [];
        $this->image_files = [];
        $this->tempFolder = $this->rootDir . '/public/sources/temp/';

        try{
            $images = $this->crawler->filterXPath('//img[contains(@alt,"thumbnail")]');
            echo "<br>Found ".$images->count()." images.<br>";
            $thumbnail_count = $images->count();
            $images->each(function (Crawler $image, $i) {
                $thumbnail_location = $image->attr("src");
                $location_array = explode("thumbnail/", $thumbnail_location);
                $location = "http://www.alvin-portal.org/alvin/attachment/download/";
                $location .= $location_array[1].'.tiff';
                $number = $i+1;
                $localFile = $this->tempFolder.$number.'.tiff';

                echo "Moving image file '".$location."' to ".$localFile.'<br>';
                try {
                    file_put_contents($this->tempFolder . $number . '.tiff', file_get_contents($location));
                    $this->image_tiff_paths[$i] = $this->tempFolder . $number . '.tiff';
                    //$this->image_tiff_paths[$i] = $location;
                }
                catch(Exception $e){
                    echo $e->getMessage()."<br>";
                }
            });
        }
        catch (Exception $e){

        }
        //print_r($image_png_paths);
        if(count($this->image_tiff_paths)>0) {
            $pdf = new Imagick($this->image_tiff_paths);
            $pdf->setImageFormat('pdf');
            $filename = $this->tempFolder . 'combined.pdf';

            $pdf->writeImages($filename, true);
            $pdf->clear();
            foreach ($this->image_tiff_paths as $image_path) {
                unlink($image_path);
            }

            $file = new UploadedFile($filename, "combined.pdf", null, null, true);

            $uploadedPdf = new DatabaseFile();
            $uploadedPdf->setFileContents($file);
            $this->source->addFile($uploadedPdf);
            $this->source = $this->fileUploader->uploadSourcePdf($this->source);
        }
        return $this->source;
    }

    private function parseDate($title_field, $origin_field){
        $text_date = "";
        $found_date = false;
        $alt_months = [
            ['September' => ['9br']]
            ];
        $origin_field = trim($origin_field);

        if ((strpos($title_field, 'Letter') === 0)){
            echo "Is a letter!";
            $text_date = substr($title_field, 7, strlen($title_field));
            $text_date = trim(explode(',', $text_date)[0]);

            $myDateTime = DateTime::createFromFormat("j F Y", $text_date);
            if($myDateTime->format("Y-m-d")!==false) {
                $text_date = $myDateTime->format("Y-m-d");
                $found_date = true;
            }
            return $text_date;
        }
        else if(DateTime::createFromFormat("Y-m-d", $origin_field)){
            $text_date = $origin_field;
            return $text_date;
        }

    }

    public function getAlvinId()
    {
        return $this->alvin_id;
    }

}