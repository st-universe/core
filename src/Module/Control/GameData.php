<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Information\InformationWrapper;

final class GameData
{
    public InformationWrapper $gameInformations;

    public ?TargetLink $targetLink = null;

    /** @var array<string, string> */
    public array $siteNavigation = [];

    public string $pagetitle = '';
    public string $macro = '';

    /** @var array<int, mixed> $viewContext */
    public array $viewContext = [];

    public function __construct()
    {
        $this->gameInformations = new InformationWrapper();
    }
}
