<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use AccessViolation;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Signup implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    private $signupRequest;

    private $allianceJobRepository;

    private $allianceRepository;

    private $privateMessageSender;

    public function __construct(
        SignupRequestInterface $signupRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRepositoryInterface $allianceRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->signupRequest = $signupRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRepository = $allianceRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $alliance = $this->allianceRepository->find($this->signupRequest->getAllianceId());
        if ($alliance === null) {
            return;
        }

        $allianceId = (int) $alliance->getId();

        if (!$user->maySignup($allianceId)) {
            throw new AccessViolation();
        }
        $obj = $this->allianceJobRepository->prototype();
        $obj->setUser($user);
        $obj->setType(ALLIANCE_JOBS_PENDING);
        $obj->setAlliance($alliance);

        $this->allianceJobRepository->save($obj);

        $text = sprintf(
            'Der Siedler %s hat sich für die Allianz beworben',
            $user->getUser()
        );

        $this->privateMessageSender->send($userId, $alliance->getFounder()->getUserId(), $text);
        if ($alliance->getSuccessor()) {
            $this->privateMessageSender->send($userId, $alliance->getSuccessor()->getUserId(), $text);
        }

        $game->addInformation(_('Deine Bewerbung für die Allianz wurde abgeschickt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
