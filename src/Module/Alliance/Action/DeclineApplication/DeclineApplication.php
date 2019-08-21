<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeclineApplication;

use AccessViolation;
use AllianceJobs;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Applications\Applications;

final class DeclineApplication implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DECLINE_APPLICATION';

    private $declineApplicationRequest;

    public function __construct(
        DeclineApplicationRequestInterface $declineApplicationRequest
    ) {
        $this->declineApplicationRequest = $declineApplicationRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }

        $appl = new AllianceJobs($this->declineApplicationRequest->getApplicationId());
        if ($appl->getAllianceId() != $alliance->getId()) {
            new AccessViolation;
        }
        $appl->deleteFromDatabase();

        $text = sprintf(
            _('Deine Bewerbung bei der Allianz %s wurde abgelehnt'),
            $alliance->getNameWithoutMarkup()
        );

        PM::sendPM(USER_NOONE, $appl->getUserId(), $text);

        $game->setView(Applications::VIEW_IDENTIFIER);

        $game->addInformation(_('Die Bewerbung wurde abgelehnt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
