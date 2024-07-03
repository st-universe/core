<?php

declare(strict_types=1);

namespace Stu\Component\Index\News;

use Override;
use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\NewsInterface;

final class NewsItem implements NewsItemInterface
{
    public function __construct(private ParserWithImageInterface $parserWithImage, private NewsInterface $news)
    {
    }

    #[Override]
    public function getSubject(): string
    {
        return $this->news->getSubject();
    }

    #[Override]
    public function getText(): string
    {
        return $this->parserWithImage->parse($this->news->getText())->getAsHTML();
    }

    #[Override]
    public function getDate(): int
    {
        return $this->news->getDate();
    }

    #[Override]
    public function getLinks(): array
    {
        return $this->news->getLinks();
    }
}
