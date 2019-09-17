<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\ColonyList;

use Colony;
use ColonyData;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;

final class GetColonyList extends Action
{
    private $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        return $response->withData(
            array_map(
                function (ColonyData $colony): int {
                    return (int) $colony->getId();
                },
                Colony::getListBy('user_id = ' . $this->session->getUser()->getId())
            )
        );
    }
}