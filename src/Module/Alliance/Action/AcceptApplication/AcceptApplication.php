<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptApplication;

use AccessViolation;
use AllianceJobs;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Applications\Applications;

final class AcceptApplication implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ACCEPT_APPLICATION';

    private $acceptApplicationRequest;

    public function __construct(
        AcceptApplicationRequestInterface $acceptApplicationRequest
    ) {
        $this->acceptApplicationRequest = $acceptApplicationRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }

        $appl = new AllianceJobs($this->acceptApplicationRequest->getApplicationId());
        if ($appl->getAllianceId() != $alliance->getId()) {
            new AccessViolation;
        }

        $game->setView(Applications::VIEW_IDENTIFIER);

        $appl->getUser()->setAllianceId($appl->getAllianceId());
        $appl->getUser()->save();
        $appl->deleteFromDatabase();

        $text = sprintf(
            _('Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz %s'),
            $alliance->getNameWithoutMarkup()
        );
        PM::sendPM(currentUser()->getId(), $appl->getUserId(), $text);

        $game->addInformation(_('Die Bewerbung wurde angenommen'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
