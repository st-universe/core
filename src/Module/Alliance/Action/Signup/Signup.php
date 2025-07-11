<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Override;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Signup implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    public function __construct(private SignupRequestInterface $signupRequest, private AllianceJobRepositoryInterface $allianceJobRepository, private AllianceRepositoryInterface $allianceRepository, private PrivateMessageSenderInterface $privateMessageSender, private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $alliance = $this->allianceRepository->find($this->signupRequest->getAllianceId());
        if ($alliance === null) {
            return;
        }

        if (!$this->allianceUserApplicationChecker->mayApply($user, $alliance)) {
            throw new AccessViolationException();
        }

        $obj = $this->allianceJobRepository->prototype();
        $obj->setUser($user);
        $obj->setType(AllianceJobTypeEnum::PENDING);
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
