<?php
declare(strict_types=1);

namespace Stu\Lib\Paging;

class Paging
{
    /**
     * @var array<int, Page>
     */
    private array $pages;
    
    public function __construct(
        private readonly string $urlPattern,
        private readonly ?string $style)
    {
    }

    /**
     * @return array<int, Page>
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param array<int, Page> $pages
     */
    public function setPages(array $pages): void
    {
        $this->pages = $pages;
    }

    public function getUrlPattern(): string
    {
        return $this->urlPattern === "" ? '?mark=%d' : $this->urlPattern . '&mark=%d';
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }
}
