<?php

declare(strict_types=1);

class SystemNewsData
{

    private $data = array();

    function __construct(&$data = array())
    {
        $this->data = $data;
    }

    function getId()
    {
        return $this->data['id'];
    }

    function getSubject()
    {
        return $this->data['subject'];
    }

    function getText()
    {
        return $this->data['text'];
    }

    function getDate()
    {
        return $this->data['date'];
    }

    function getRefs()
    {
        return $this->data['refs'];
    }

    private $links = null;

    function getLinks()
    {
        if ($this->links === null) {
            $this->links = $this->parseLinks();
        }
        return $this->links;
    }

    function parseLinks()
    {
        $lines = explode("\n", $this->getRefs());
        if (count($lines) == 0) {
            return false;
        }
        return $lines;
    }

}