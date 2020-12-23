<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;

interface ActivatorDeactivatorHelperInterface
{
    public function deactivate( int $shipId,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void;

    public function deactivateFleet( int $shipId,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void;
}
