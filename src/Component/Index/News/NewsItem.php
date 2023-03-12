<?php

declare(strict_types=1);

namespace Stu\Component\Index\News;

use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\NewsInterface;

final class NewsItem implements NewsItemInterface
{
    private ParserWithImageInterface $parserWithImage;

    private NewsInterface $news;

    public function __construct(
        ParserWithImageInterface $parserWithImage,
        NewsInterface $news
    ) {
        $this->parserWithImage = $parserWithImage;
        $this->news = $news;
    }

    public function getSubject(): string
    {
        return $this->news->getSubject();
    }

    public function getText(): string
    {
        return $this->parserWithImage->parse($this->news->getText())->getAsHTML();
    }

    public function getDate(): int
    {
        return $this->news->getDate();
    }

    public function getLinks(): array
    {
        return $this->news->getLinks();
    }
}
