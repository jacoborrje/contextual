<?php
/**
 * Created by PhpStorm.
 * User: jacoborrje
 * Date: 2017-04-27
 * Time: 21:00
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."third_party/scraper/web_browser.php";
require_once APPPATH."third_party/scraper/tag_filter.php";



class Scraper_base
{

    public function __construct($parameters)
    {
        $this->url = $parameters['url'];
        //Retrieve the standard HTML parsing array for later use.
        $this->htmloptions = TagFilter::GetHTMLOptions();
        // Retrieve a URL.
        $this->web = new WebBrowser();
    }


    public function connect()
    {
        $this->result = $this->web->Process($this->url);
        if (!$this->result["success"]) {
            echo "Error retrieving URL.  " . $result["error"] . "\n";
            return false;
        } else if ($this->result["response"]["code"] != 200) {
            echo "Error retrieving URL.  Server returned:  " . $result["response"]["code"] . " " . $result["response"]["meaning"] . "\n";
            return false;
        } else {
            return true;
        }
    }

    public function parse()
    {
        $baseurl = $this->result["url"];
        // Use TagFilter to parse the content.
        $html = TagFilter::Explode($this->result["body"], $this->htmloptions);
        // Retrieve a pointer object to the root node.
        $root = $html->Get();
        // Find all anchor tags.
        echo "All the URLs:\n";
        $rows = $root->Find("a[href]");
        foreach ($rows as $row) {
            // Somewhat slower access.
            echo "\t" . $row->href . "\n";
            echo "\t" . HTTP::ConvertRelativeToAbsoluteURL($baseurl, $row->href) . "\n";
        }
        // Find all table rows that have 'th' tags.
        $rows = $root->Find("tr");
        foreach ($rows as $row) {
            echo "\t" . $row->GetOuterHTML() . "\n\n";
        }
    }
}