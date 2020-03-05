<?php
namespace App\Utils;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class ScraperBase
{
    protected $url, $client, $crawler, $html, $rootPath;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->client = new Client();
    }

    public function connect($url)
    {
        $this->crawler =  $this->client->request('GET', $url);
        return true;
    }

    public function parse()
    {
        return $this->crawler->filter('title')->text();
    }
}