<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;

final class GetInfo extends Action
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
        $user = $this->session->getUser();

        return $response->withData([
            'id' => $user->getId(),
            'factionId' => $user->getFaction()->getId(),
            'name' => $user->getUser(),
            'allianceId' => $user->getAllianceId(),
            'avatarPath' => $user->getFullAvatarPath()
        ]);
    }
}
