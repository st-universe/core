<?php

namespace Stu\Module\Control;

use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameConfigInterface;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
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

    public ?GameTurnInterface $currentRound = null;

    /** @var array<string> */
    public array $achievements = [];

    /** @var array<int, mixed> $viewContext */
    public array $viewContext = [];

    /** @var array{currentTurn:int, player:int, playeronline:int, gameState:int, gameStateTextual:string} */
    public ?array $gameStats = null;

    /** @var array<int, SysvSemaphore> */
    public array $semaphores = [];

    /** @var GameConfigInterface[]|null */
    public ?array $gameConfig = null;

    public ?GameRequestInterface $gameRequest = null;

    public function __construct()
    {
        $this->gameInformations = new InformationWrapper();
    }
}
