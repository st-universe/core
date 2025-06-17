<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;

interface TrumfieldInterface extends
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithInteractionCheckInterface
{
    public function getId(): int;

    public function getHull(): int;

    public function setHull(int $hull): TrumfieldInterface;

    public function getFormerRumpId(): int;

    public function setFormerRumpId(int $formerRumpId): TrumfieldInterface;

    public function setLocation(LocationInterface $location): TrumfieldInterface;
}
