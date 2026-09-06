<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ResubmitCrewRace;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\CrewRaceSubmission;

final class ResubmitCrewRace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RESUBMIT_CREW_RACE';

    public function __construct(private readonly CrewRaceSubmission $submission) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $this->submission->submit($game, true);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
