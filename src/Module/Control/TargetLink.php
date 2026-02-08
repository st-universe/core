<?php

namespace Stu\Module\Control;

final class TargetLink
{
    public function __construct(private string $url, private string $title) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
