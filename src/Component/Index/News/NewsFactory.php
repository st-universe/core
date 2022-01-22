<?php

declare(strict_types=1);

namespace Stu\Component\Index\News;

use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\NewsInterface;

final class NewsFactory implements NewsFactoryInterface
{
    private ParserWithImageInterface $parserWithImage;

    public function __construct(
        ParserWithImageInterface $parserWithImage
    ) {
        $this->parserWithImage = $parserWithImage;
    }

    public function createNewsItem(
        NewsInterface $knPost
    ): NewsItemInterface {
        return new NewsItem(
            $this->parserWithImage,
            $knPost
        );
    }
}
