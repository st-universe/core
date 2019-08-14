<?php

namespace Stu\Orm\Entity;

use ColfieldData;
use Colony;
use Ship;

interface ColonyShipRepairInterface
{
    public function getId(): int;

    public function setColonyId($colony_id): ColonyShipRepairInterface;

    public function getColonyId(): int;

    public function setShipId(int $ship_id): ColonyShipRepairInterface;

    public function getShipId(): int;

    public function setFieldId(int $field_id): ColonyShipRepairInterface;

    public function getFieldId(): int;

    public function getField(): ColfieldData;

    public function getColony(): Colony;

    public function getShip(): Ship;
}