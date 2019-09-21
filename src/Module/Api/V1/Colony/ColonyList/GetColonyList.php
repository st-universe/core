<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\ColonyList;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class GetColonyList extends Action
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
        return $response->withData(
            array_map(
                function (ColonyInterface $colony): int {
                    return (int) $colony->getId();
                },
                $this->colonyRepository->getOrderedListByUser($this->session->getUser()->getId())
            )
        );
    }
}