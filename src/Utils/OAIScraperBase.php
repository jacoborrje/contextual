<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2019-02-01
 * Time: 20:22
 */

namespace App\Utils;

use GuzzleHttp\Client as GuzzleClient;
use \Phpoaipmh\Endpoint;
use Phpoaipmh\Client;
use Phpoaipmh\HttpAdapter\GuzzleAdapter;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use \DateTime;

use \App\Entity\Source;
use \App\Entity\Actor;
use \App\Entity\Action;


class OAIScraperBase
{

    protected $endpoint, $client, $guzzle, $stack, $container, $namespace, $em;

    public function __construct($service_url, $em)
    {
        $this->container = [];
        $this->em = $em;
        $this->dc_namespace = 'http://purl.org/dc/elements/1.1/';

        $this->stack = HandlerStack::create();
        $this->stack->push(Middleware::history($this->container));

        $this->guzzle = new GuzzleAdapter(new GuzzleClient([
            'connect_timeout' => 2.0,
            'timeout'         => 10.0
        ]));
        $this->client = new Client($service_url, $this->guzzle);
        $this->endpoint = new Endpoint($this->client);
    }

    public function listRecords()
    {
        return $this->endpoint->listRecords("oai_dc");
    }

    public function getRecord($identifier){
        return $this->endpoint->getRecord("oai:ALVIN.org:".$identifier, "oai_dc");
    }

    public function listMetadataFormats(){
        return $this->endpoint->listMetadataFormats();
    }

    public function identify(){
        return $this->endpoint->identify();
    }

    public function getRecordsOfActor($actorID){

    }

    public function getSource($identifier){

        $source = new Source();

        $record = $this->getRecord($identifier);
        $record->registerXPathNamespace('dc', $this->dc_namespace);
        $source->setTitle($record->xpath('//dc:title')[0]->__toString());
        $originalDate = $record->xpath('//dc:date')[0]->__toString();
        $myDateTime = DateTime::createFromFormat("j F Y", $originalDate);
        $new_date = $myDateTime->format("Y-m-d");
        $source->setDateByString($new_date);

        $repository = $this->em->getRepository(Actor::class);

        $metadata['date'] = $new_date;
        $iterator = 0;
        foreach($record->xpath('//dc:creator') as $item){
            $content_array = explode( ", ", $item->__toString());
            $fields['surname'] = $content_array[0];
            $fields['first_name'] = $content_array[1];

            $actor = null;
            $actor = $repository->findOneByAltSurnamesAndAltFirstNames($fields);

            if(!is_null($actor)){
                $sender = new Action();
                $sender->setCorrespondent($actor->getCorrespondent());
                $sender->setStartDateByString($new_date);
                $sender->setType(1);
                $source->addAction($sender);
            }

            $metadata['creator'][$iterator] = $actor;
        }

        $iterator = 0;
        foreach($record->xpath('//dc:contributor') as $item){
            $content_array = explode( ", ", $item->__toString());
            $fields['surname'] = $content_array[0];
            $fields['first_name'] = $content_array[1];

            $actor = null;
            $actor = $repository->findOneByAltSurnamesAndAltFirstNames($fields);

            if(!is_null($actor)){
                $recipient = new Action();
                $recipient->setCorrespondent($actor->getCorrespondent());
                $recipient->setStartDateByString($new_date);
                $recipient->setType(2);
                $source->addAction($recipient);
            }

            $metadata['contributor'][$iterator] = $actor;
        }

        return $source;
    }
}