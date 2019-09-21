<?php

namespace Stu\Orm\Entity;

use Ship;

interface ColonyShipRepairInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function setShipId(int $ship_id): ColonyShipRepairInterface;

    public function getShipId(): int;

    public function setFieldId(int $field_id): ColonyShipRepairInterface;

    public function getFieldId(): int;

    public function getField(): PlanetFieldInterface;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyShipRepairInterface;

    public function getShip(): Ship;
}