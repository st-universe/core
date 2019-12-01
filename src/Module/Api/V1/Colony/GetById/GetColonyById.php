<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\GetById;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class GetColonyById extends Action
{
    private ColonyRepositoryInterface $colonyRepository;

    private SessionInterface $session;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        SessionInterface $session
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->session = $session;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $colonyId = (int) $args['colonyId'] ?? 0;

        $colony = $this->colonyRepository->find($colonyId);

        if ($colony === null || $colony->getUserId() !== $this->session->getUser()->getId()) {
            return $response->withError(
                ErrorCodeEnum::NOT_FOUND,
                'Not found'
            );
        }

        return $response->withData([
            'colonyId' => $colony->getId(),
            'name' => $colony->getName()
        ]);
    }
}
