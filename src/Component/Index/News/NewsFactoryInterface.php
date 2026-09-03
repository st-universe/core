<?php

declare(strict_types=1);

namespace Stu\Component\Index\News;

use Stu\Orm\Entity\News;

interface NewsFactoryInterface
{
    public function createNewsItem(News $news): NewsItemInterface;
}
