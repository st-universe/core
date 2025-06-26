<?php

namespace Stu\Component\Index\News;

use Stu\Orm\Entity\News;

interface NewsFactoryInterface
{
    public function createNewsItem(News $news): NewsItemInterface;
}
