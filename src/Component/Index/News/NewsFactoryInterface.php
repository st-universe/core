<?php

namespace Stu\Component\Index\News;

use Stu\Orm\Entity\NewsInterface;

interface NewsFactoryInterface
{
    public function createNewsItem(NewsInterface $news): NewsItemInterface;
}
