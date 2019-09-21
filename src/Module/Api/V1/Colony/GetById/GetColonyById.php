<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\GetById;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\ActionError;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class GetColonyById extends Action
{
    private $colonyRepository;

    private $session;

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
        if ($colonyId === 0) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$colonyId}`.");
        }

        $colony = $this->colonyRepository->find($colonyId);

        if ($colony === null || $colony->getUserId() !== $this->session->getUser()->getId()) {
            return $response->withError(
                ActionError::RESOURCE_NOT_FOUND
            );
        }

        return $response->withData([
            'id' => $colony->getId(),
            'name' => $colony->getName()
        ]);
    }
}