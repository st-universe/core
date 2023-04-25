<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Ship;

interface ShipResetInterface
{
    public function deactivateAllTractorBeams(): void;

    public function undockAllDockedShips(): void;

    public function deleteAllTradeposts(): void;

    public function deleteAllShips(): void;

    public function deleteAllUserBuildplans(): void;
}
