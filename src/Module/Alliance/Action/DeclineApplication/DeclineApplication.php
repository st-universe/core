<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Applications\Applications;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;

final class DeclineApplication implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DECLINE_APPLICATION';

    public function __construct(
        private DeclineApplicationRequestInterface $declineApplicationRequest,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceJobManager->hasUserPermission($game->getUser(), $alliance, AllianceJobPermissionEnum::MANAGE_APPLICATIONS)) {
            throw new AccessViolationException();
        }

        $application = $this->allianceApplicationRepository->find($this->declineApplicationRequest->getApplicationId());
        if ($application === null || $application->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $applicant = $application->getUser();
        $this->allianceApplicationRepository->delete($application);

        $text = sprintf(
            _('Deine Bewerbung bei der Allianz %s wurde abgelehnt'),
            $alliance->getName()
        );

        $this->privateMessageSender->send(UserConstants::USER_NOONE, $applicant->getId(), $text);

        $game->setView(Applications::VIEW_IDENTIFIER);

        $game->getInfo()->addInformation(_('Die Bewerbung wurde abgelehnt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
