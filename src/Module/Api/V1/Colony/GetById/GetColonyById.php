<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\GetById;

use Colony;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\ActionError;
use Stu\Module\Api\Middleware\SessionInterface;

final class GetColonyById extends Action
{
    private $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * @return ResponseInterface
     * @throws HttpBadRequestException
     */
    protected function action(): ResponseInterface
    {
        $colonyId = (int) $this->resolveArg('colonyId');

        $colony = new Colony($colonyId);

        if ($colony->getUserId() != $this->session->getUser()->getId()) {
            return $this->respondWithError(
                new ActionError(ActionError::RESOURCE_NOT_FOUND)
            );
        }

        return $this->respondWithData([
            'id' => $colony->getId(),
            'name' => $colony->getName()
        ]);
    }
}