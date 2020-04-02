<?php

namespace App\Entity;

class SearchQuery
{

    private $queryText;


    function __toString()
    {
        return $this->queryText;
    }

    /**
     * @return mixed
     */
    public function getQueryText()
    {
        return $this->queryText;
    }

    /**
     * @param mixed $queryText
     */
    public function setQueryText($queryText): void
    {
        $this->queryText = $queryText;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($queryText)
    {
        $this->queryText = $queryText;
    }

}
