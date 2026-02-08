<?php

declare(strict_types=1);

namespace Stu\Component\Index\News;

use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\News;

final class NewsFactory implements NewsFactoryInterface
{
    public function __construct(private ParserWithImageInterface $parserWithImage) {}

    #[\Override]
    public function createNewsItem(
        News $news
    ): NewsItemInterface {
        return new NewsItem(
            $this->parserWithImage,
            $news
        );
    }
}
