<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\News;

use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class GetNews
{
    private $newsRepository;

    public function __construct(
        NewsRepositoryInterface $newsRepository
    ) {
        $this->newsRepository = $newsRepository;
    }

    public function handle(): array
    {
        return array_map(
            function (NewsInterface $news): array {
                return [
                    'headline' => $news->getSubject(),
                    'text' => $news->getText(),
                    'date' => $news->getDate(),
                    'links' => $news->getLinks(),
                ];
            },
            $this->newsRepository->getRecent(5)
    );
    }
}