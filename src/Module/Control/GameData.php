<?php

namespace Stu\Module\Control;

use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;

final class GameData
{
    public InformationWrapper $gameInformations;

    public ?TargetLink $targetLink = null;

    /** @var array<string, string> */
    public array $siteNavigation = [];

    public string $pagetitle = '';
    public string $macro = '';

    public ?GameTurn $currentRound = null;

    /** @var array<int, mixed> $viewContext */
    public array $viewContext = [];

    public ?GameRequest $gameRequest = null;

    public function __construct()
    {
        $this->gameInformations = new InformationWrapper();
    }
}
