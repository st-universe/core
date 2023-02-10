<?php

namespace Stu\Orm\Entity;

interface ColonyShipRepairInterface
{
    public function getId(): int;

    public function getColonyId(): int;

    public function getShipId(): int;

    public function setFieldId(int $field_id): ColonyShipRepairInterface;

    public function getFieldId(): int;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyShipRepairInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): ColonyShipRepairInterface;
}
