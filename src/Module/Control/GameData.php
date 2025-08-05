<?php

namespace Stu\Module\Control;

use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use SysvSemaphore;

final class GameData
{
    public InformationWrapper $gameInformations;

    public ?TargetLink $targetLink = null;

    /** @var array<string, string> */
    public array $siteNavigation = [];

    public string $pagetitle = '';
    public string $macro = '';

    /** @var array<int, array<string>> */
    public array $execjs = [];

    public ?GameTurn $currentRound = null;

    /** @var array<string> */
    public array $achievements = [];

    /** @var array<int, mixed> $viewContext */
    public array $viewContext = [];

    /** @var array{currentTurn:int, player:int, playeronline:int, gameState:int, gameStateTextual:string} */
    public ?array $gameStats = null;

    /** @var array<int, SysvSemaphore> */
    public array $semaphores = [];

    public ?GameRequest $gameRequest = null;

    public function __construct()
    {
        $this->gameInformations = new InformationWrapper();
    }
}
