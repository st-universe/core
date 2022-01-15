<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface StarSystemMapInterface
{
    public function getId(): int;

    public function getSx(): int;

    public function setSx($sx): StarSystemMapInterface;

    public function getSy(): int;

    public function setSy($sy): StarSystemMapInterface;

    public function getSystemId(): int;

    public function setSystemId(int $systemId): StarSystemMapInterface;

    public function getSystem(): StarSystemInterface;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): StarSystemMapInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getColony(): ?ColonyInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getAdministratedRegion(): ?MapRegionInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function getFieldStyle(): string;

    public function getShips(): Collection;

    public function getSignatures(): Collection;
}
