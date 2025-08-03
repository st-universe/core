<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use BadMethodCallException;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\StuTestCase;

class ModuleEnumTest extends StuTestCase
{
    public function testGetComponentEnumExpectOutdatedWhenValueUndefined(): void
    {
        $result = ModuleEnum::GAME->getComponentEnum('UNKNOWN');

        $this->assertEquals(GameComponentEnum::OUTDATED, $result);
    }

    public function testGetComponentEnumExpectEnumWhenValueDefined(): void
    {
        $result = ModuleEnum::GAME->getComponentEnum('NAGUS_POPUP');

        $this->assertEquals(GameComponentEnum::NAGUS, $result);
    }

    public function testGetComponentEnumExpectExceptionWhenModuleNotSupported(): void
    {
        static::expectException(BadMethodCallException::class);
        static::expectExceptionMessage('no components in this module view');

        ModuleEnum::STARMAP->getComponentEnum('WRONG');
    }
}
