<?php
namespace App\Utils\Scrapers;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Utils\ScraperBase;
use \Exception;

class BehindTheNameScraper extends ScraperBase
{
    protected $url, $client, $crawler, $html, $nameData, $apiKey;
    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = "ja294389420";
    }

    public function lookupName($name)
    {
        $this->url = 'https://www.behindthename.com/api/lookup.json?';
        $this->url .= 'name='.$name;
        $this->url .= '&key='.$this->apiKey;

        try {
            echo "Looking up name.<br>";
            $this->html = file_get_contents($this->url);
            $this->nameData = json_decode($this->html, true); // "true" to get PHP array instead of an object
            echo "Found name data for name ".$name."<br>";
            print_r($this->nameData);echo "<br>";
            return true;
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function getGender()
    {
        if(!is_null($this->nameData)){
            if($this->nameData['gender']==='f'){
                return 1;
            }
            else if ($this->nameData['gender']==='m') {
                return 0;
            }
        }
        else{
            throw new Exception("Must call function lookupName() before checking gender!");
        }
    }
}

