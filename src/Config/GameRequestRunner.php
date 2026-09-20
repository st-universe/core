<?php

declare(strict_types=1);

namespace Stu\Config;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Component\Player\Register\RegistrationReferralTrackerInterface;
use Stu\Component\Game\RedirectionException;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameSessionInitializerInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;

final class GameRequestRunner implements GameRequestRunnerInterface
{
    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    public function __construct(
        private readonly GameControllerInterface $gameController,
        private readonly SessionStarterInterface $sessionStarter,
        private readonly GameSessionInitializerInterface $gameSessionInitializer,
        private readonly GameRequestRepositoryInterface $gameRequestRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly GameRequestSaverInterface $gameRequestSaver,
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly RegistrationReferralTrackerInterface $registrationReferralTracker
    ) {}

    #[\Override]
    public function run(ModuleEnum $module): void
    {
        $this->sessionStarter->start();

        if ($module === ModuleEnum::INDEX) {
            $redirectTarget = $this->registrationReferralTracker->captureFromRequest();
            if ($redirectTarget !== null) {
                header(sprintf('Location: %s', $redirectTarget), true, 302);
                return;
            }
        }

        $gameRequest = $this->getGameRequest();
        $errorOccured = false;

        try {
            $user = $this->gameSessionInitializer->initialize($module);
            $gameRequest->setUserId($user);
            $this->gameController->main($module, $gameRequest);
        } catch (SessionInvalidException) {
            $errorOccured = true;
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
            }
        } finally {
            $this->gameRequestSaver->save($gameRequest, $errorOccured);
        }
    }

    private function getGameRequest(): GameRequest
    {
        $gameRequest = $this->gameRequestRepository->prototype();
        $gameRequest->setTime(time());
        $gameRequest->setTurnId($this->gameTurnRepository->getCurrent());
        $gameRequest->setParameterArray(request::isPost() ? request::postvars() : request::getvars());
        $gameRequest->setRequestId($this->uuidGenerator->genV4());

        return $gameRequest;
    }
}
