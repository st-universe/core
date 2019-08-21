<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use AccessViolation;
use AllianceJobs;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;

final class EditDetails implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_UPDATE_ALLIANCE';

    private $editDetailsRequest;

    public function __construct(
        EditDetailsRequestInterface $editDetailsRequest
    ) {
        $this->editDetailsRequest = $editDetailsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        $name = $this->editDetailsRequest->getName();
        $faction_mode = $this->editDetailsRequest->getFactionMode();
        $description = $this->editDetailsRequest->getDescription();
        $homepage = $this->editDetailsRequest->getHomepage();
        $acceptApplications = $this->editDetailsRequest->getAcceptApplications();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($alliance->mayEditFactionMode()) {
            if ($faction_mode === 1) {
                $alliance->setFactionId($user->getFaction());
            } else {
                $alliance->setFactionId(0);
            }
        }
        if ($acceptApplications === 1) {
            $alliance->setAcceptApplications(1);
        } else {
            $alliance->setAcceptApplications(0);
            AllianceJobs::truncatePendingMembers($alliance->getId());
        }

        if (mb_strlen(trim(BBCode()->parse($name))) < 5) {
            $game->addInformation(_('Der Name muss aus mindestens 5 Zeichen bestehen'));
            return;
        }
        if (mb_strlen($homepage) > 0) {
            if (strpos($homepage, 'http') !== 0) {
                $game->addInformation(_('Diese Homepage-Adresse ist nicht gültig'));
                return;
            }
        }
        $alliance->setName($name);
        $alliance->setHomepage($homepage);
        $alliance->setDescription($description);
        $alliance->save();

        $game->addInformation(_('Die Allianz wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
