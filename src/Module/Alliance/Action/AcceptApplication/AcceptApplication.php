<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AcceptApplication implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_ACCEPT_APPLICATION';

    private AcceptApplicationRequestInterface $acceptApplicationRequest;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        AcceptApplicationRequestInterface $acceptApplicationRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->acceptApplicationRequest = $acceptApplicationRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Applications::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            throw new AccessViolation();
        }

        $appl = $this->allianceJobRepository->find($this->acceptApplicationRequest->getApplicationId());
        if ($appl === null || $appl->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $applicant = $appl->getUser();
        $applicant->setAlliance($alliance);
        $applicationsOfUser = $this->allianceJobRepository->getByUser($applicant->getId());

        $this->cancelOtherApplications($applicationsOfUser, $appl);

        $this->userRepository->save($applicant);

        $this->allianceJobRepository->delete($appl);

        $text = sprintf(
            _('Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz %s'),
            $alliance->getName()
        );

        $alliance->getMembers()->add($appl->getUser());

        $this->privateMessageSender->send($userId, $applicant->getId(), $text);

        $game->addInformation(_('Die Bewerbung wurde angenommen'));
    }

    /** @param array<AllianceJobInterface> $applications */
    private function cancelOtherApplications(array $applications, AllianceJobInterface $currentApplication): void
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

            $this->privateMessageSender->send(UserEnum::USER_NOONE, $alliance->getFounder()->getUserId(), $text);
            if ($alliance->getSuccessor() !== null) {
                $this->privateMessageSender->send(UserEnum::USER_NOONE, $alliance->getSuccessor()->getUserId(), $text);
            }

            $this->allianceJobRepository->delete($application);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
