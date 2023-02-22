<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Signup implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    private SignupRequestInterface $signupRequest;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker;

    public function __construct(
        SignupRequestInterface $signupRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceRepositoryInterface $allianceRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker
    ) {
        $this->signupRequest = $signupRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceRepository = $allianceRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->allianceUserApplicationChecker = $allianceUserApplicationChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $alliance = $this->allianceRepository->find($this->signupRequest->getAllianceId());
        if ($alliance === null) {
            return;
        }

        if (!$this->allianceUserApplicationChecker->mayApply($user, $alliance)) {
            throw new AccessViolation();
        }

        $obj = $this->allianceJobRepository->prototype();
        $obj->setUser($user);
        $obj->setType(AllianceEnum::ALLIANCE_JOBS_PENDING);
        $obj->setAlliance($alliance);

        $this->allianceJobRepository->save($obj);

        $text = sprintf(
            'Der Siedler %s hat sich für die Allianz beworben',
            $user->getName()
        );

        $this->privateMessageSender->send($userId, $alliance->getFounder()->getUserId(), $text);
        if ($alliance->getSuccessor() !== null) {
            $this->privateMessageSender->send($userId, $alliance->getSuccessor()->getUserId(), $text);
        }

        $game->addInformation(_('Deine Bewerbung für die Allianz wurde abgeschickt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
