<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\AllianceApplication;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AcceptApplication implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACCEPT_APPLICATION';

    public function __construct(
        private AcceptApplicationRequestInterface $acceptApplicationRequest,
        private AllianceActionManagerInterface $allianceActionManager,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Applications::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            throw new AccessViolationException();
        }

        $application = $this->allianceApplicationRepository->find($this->acceptApplicationRequest->getApplicationId());
        if ($application === null || $application->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $applicant = $application->getUser();
        $applicant->setAlliance($alliance);
        $applicationsOfUser = $this->allianceApplicationRepository->getByUser($applicant->getId());

        $this->cancelOtherApplications($applicationsOfUser, $application);

        $this->userRepository->save($applicant);

        $this->allianceApplicationRepository->delete($application);

        $text = sprintf(
            _('Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz %s'),
            $alliance->getName()
        );

        $alliance->getMembers()->add($application->getUser());

        $this->privateMessageSender->send($userId, $applicant->getId(), $text);

        $game->getInfo()->addInformation(_('Die Bewerbung wurde angenommen'));
    }

    /** @param array<AllianceApplication> $applications */
    private function cancelOtherApplications(array $applications, AllianceApplication $currentApplication): void
    {
        $text = sprintf(
            'Der Siedler %s wurde bei einer anderen Allianz aufgenommen',
            $currentApplication->getUser()->getName()
        );

        foreach ($applications as $application) {
            if ($application === $currentApplication) {
                continue;
            }

            $alliance = $application->getAlliance();

            $founderJob = $alliance->getFounder();
            foreach ($founderJob->getUsers() as $founder) {
                $this->privateMessageSender->send(UserConstants::USER_NOONE, $founder->getId(), $text);
            }

            $successorJob = $alliance->getSuccessor();
            if ($successorJob !== null) {
                foreach ($successorJob->getUsers() as $successor) {
                    $this->privateMessageSender->send(UserConstants::USER_NOONE, $successor->getId(), $text);
                }
            }

            $this->allianceApplicationRepository->delete($application);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
