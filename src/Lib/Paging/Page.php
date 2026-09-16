<?php
declare(strict_types=1);

namespace Stu\Lib\Paging;

class Page
{
    public function __construct(
        private readonly Paging $paging,
        private readonly string $label,
        private readonly int $mark,
        private readonly string $cssclass)
    {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCssClass(): string
    {
        return $this->cssclass;
    }

    public function getUrl(): string
    {
        return sprintf($this->paging->getUrlPattern(), $this->mark);
    }
}