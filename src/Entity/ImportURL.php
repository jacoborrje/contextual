<?php

namespace App\Entity;

class ImportURL
{

    private $url;


    function __toString()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

}
