<?php

declare(strict_types=1);

namespace Stu\Html\Game;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Game\View\ShowComponent\ShowComponent;
use Stu\TwigTestCase;

class ShowComponentTest extends TwigTestCase
{
    public static function getCombinationsDataProvider(): array
    {
        return [
            // GAME
            [GameComponentEnum::COLONIES],
            [GameComponentEnum::NAVIGATION],
            //[GameComponentEnum::NAGUS],         // not idempotent
            [GameComponentEnum::PM],
            [GameComponentEnum::RESEARCH],
            [GameComponentEnum::SERVERTIME_AND_VERSION],
            [GameComponentEnum::USER],
            [GameComponentEnum::OUTDATED],

            // COLONY (all other need ColonyGuiHelper)
            [ColonyComponentEnum::SHIELDING, ['id' => 42, 'hosttype' => 1]],
            [ColonyComponentEnum::EPS_BAR, ['id' => 42, 'hosttype' => 1]],
            [ColonyComponentEnum::SURFACE, ['id' => 42, 'hosttype' => 1]],
            [ColonyComponentEnum::STORAGE, ['id' => 42, 'hosttype' => 1]]
        ];
    }

    #[DataProvider('getCombinationsDataProvider')]
    public function testHandle(
        ComponentEnumInterface $component,
        array $requestVars = []
    ): void {

        $requestVars['component'] = sprintf('%s_%s', $component->getModuleView()->name, $component->getValue());

        $this->renderSnapshot(
            101,
            ShowComponent::class,
            $requestVars
        );
    }
}
