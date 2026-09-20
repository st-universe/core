<?php

declare(strict_types=1);

namespace Stu\Config;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameSessionInitializerInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Repository\GameRequestRepositoryInterface;

final class GameRequestRunner implements GameRequestRunnerInterface
{
    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    public function __construct(
        private readonly GameControllerInterface $gameController,
        private readonly SessionStarterInterface $sessionStarter,
        private readonly GameSessionInitializerInterface $gameSessionInitializer,
        private readonly GameRequestRepositoryInterface $gameRequestRepository,
        private readonly UuidGeneratorInterface $uuidGenerator
    ) {}

    #[\Override]
    public function run(ModuleEnum $module): void
    {
        $this->sessionStarter->start();

        $gameRequest = $this->getGameRequest();

        try {
            $user = $this->gameSessionInitializer->initialize($module);
            $gameRequest->setUserId($user);
            $this->gameController->main($module, $gameRequest);
        } catch (SessionInvalidException) {
            session_destroy();

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
            }
        }
    }

    private function getGameRequest(): GameRequest
    {
        $gameRequest = $this->gameRequestRepository->prototype();
        $gameRequest->setTime(time());
        $gameRequest->setParameterArray(request::isPost() ? request::postvars() : request::getvars());
        $gameRequest->setRequestId($this->uuidGenerator->genV4());

        return $gameRequest;
    }
}
