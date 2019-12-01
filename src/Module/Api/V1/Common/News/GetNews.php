<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\News;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Orm\Entity\NewsInterface;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class GetNews extends Action
{
    private NewsRepositoryInterface $newsRepository;

    public function __construct(
        NewsRepositoryInterface $newsRepository
    ) {
        $this->newsRepository = $newsRepository;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        return $response->withData(
            array_map(
                function (NewsInterface $news): array {
                    return [
                        'headline' => $news->getSubject(),
                        'text' => $news->getText(),
                        'date' => $news->getDate(),
                        'links' => $news->getLinks(),
                    ];
                },
                $this->newsRepository->getRecent(5)
            )
        );
    }
}
