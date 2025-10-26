<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Signup;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Alliance\AllianceUserApplicationCheckerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\AllianceApplication;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Signup implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SIGNUP_ALLIANCE';

    public function __construct(
        private SignupRequestInterface $signupRequest,
        private AllianceRepositoryInterface $allianceRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private AllianceUserApplicationCheckerInterface $allianceUserApplicationChecker,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
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

        $application = $this->allianceApplicationRepository->prototype();
        $application->setAlliance($alliance);
        $application->setUser($user);
        $application->setDate(time());

        $this->allianceApplicationRepository->save($application);
        $this->entityManager->flush();

        $text = sprintf(
            'Der Siedler %s hat sich für die Allianz beworben',
            $user->getName()
        );

        $this->entityManager->refresh($alliance);

        $founderJob = $alliance->getFounder();
        foreach ($founderJob->getUsers() as $founder) {
            $this->privateMessageSender->send($userId, $founder->getId(), $text);
        }

        $successorJob = $alliance->getSuccessor();
        if ($successorJob !== null) {
            foreach ($successorJob->getUsers() as $successor) {
                $this->privateMessageSender->send($userId, $successor->getId(), $text);
            }
        }

        $game->getInfo()->addInformation(_('Deine Bewerbung für die Allianz wurde abgeschickt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
