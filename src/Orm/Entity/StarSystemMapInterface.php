<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface StarSystemMapInterface
{
    public function getId(): int;

    public function getX(): int;

    public function getSx(): int;

    public function setSx(int $sx): StarSystemMapInterface;

    public function getY(): int;

    public function getSy(): int;

    public function setSy(int $sy): StarSystemMapInterface;

    public function getSystemId(): int;

    public function getSystem(): StarSystemInterface;

    public function setSystem(StarSystemInterface $starSystem): StarSystemMapInterface;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): StarSystemMapInterface;

    public function getFieldType(): MapFieldTypeInterface;

    public function getColony(): ?ColonyInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getAdministratedRegion(): ?MapRegionInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function getFieldStyle(): string;

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getShips(): Collection;

    /**
     * @return Collection<int, FlightSignatureInterface>
     */
    public function getSignatures(): Collection;

    /**
     * @return Collection<int, AnomalyInterface>
     */
    public function getAnomalies(): Collection;

    public function getRandomWormholeEntry(): ?WormholeEntryInterface;

    public function getSectorString(): string;
}
