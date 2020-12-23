<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;

final class ActivatorDeactivatorHelperInterface
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
