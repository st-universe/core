<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use AllianceData;
use AllianceJobs;
use AllianceJobsData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Create\Create;

final class CreateAlliance implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_ALLIANCE';

    private $createAllianceRequest;

    public function __construct(
        CreateAllianceRequestInterface $createAllianceRequest
    ) {
        $this->createAllianceRequest = $createAllianceRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $name = $this->createAllianceRequest->getName();
        $faction_mode = $this->createAllianceRequest->getFactionMode();
        $description = $this->createAllianceRequest->getDescription();

        if (mb_strlen($name) < 5) {
            $game->setView(Create::VIEW_IDENTIFIER);
            $game->addInformation(_('Der Name muss aus mindestens 5 Zeichen bestehen'));
            return;
        }

        $alliance = new AllianceData;
        $alliance->setName($name);
        $alliance->setDescription($description);
        $alliance->setDate(time());
        if ($faction_mode === 1) {
            $alliance->setFactionId($user->getFaction());
        }
        $alliance->save();

        $allianceId = $alliance->getId();

        $user->setAllianceId($allianceId);
        $user->save();

        AllianceJobs::delByUser($userId);

        $job = new AllianceJobsData();
        $job->setType(ALLIANCE_JOBS_FOUNDER);
        $job->setAllianceId($allianceId);
        $job->setUserId($userId);
        $job->save();

        $game->addInformation(_('Die Allianz wurde gegründet'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
