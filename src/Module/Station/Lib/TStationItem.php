<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Doctrine\ORM\Mapping\Entity;
use Stu\Module\Spacecraft\Lib\TSpacecraftItem;

#[Entity]
class TStationItem extends TSpacecraftItem {}
