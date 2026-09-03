<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

interface BuildableRumpListItemInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getCategoryName(): string;

    public function getActiveShipCount(): int;

    public function getBuildplanCount(): int;
}
