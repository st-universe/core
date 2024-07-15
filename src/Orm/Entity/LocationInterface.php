<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;

interface LocationInterface
{
    public function getId(): int;

    public function getLayer(): ?LayerInterface;

    public function getSectorId(): ?int;

    public function getCx(): ?int;

    public function getCy(): ?int;

    public function getX(): int;

    public function getY(): int;

    public function getFieldId(): int;

    public function getFieldType(): MapFieldTypeInterface;

    public function setFieldType(MapFieldTypeInterface $mapFieldType): LocationInterface;

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getShips(): Collection;

    /**
     * @return Collection<int, AnomalyInterface>
     */
    public function getAnomalies(bool $onlyActive = true): Collection;

    public function hasAnomaly(AnomalyTypeEnum $type): bool;

    /**
     * @return Collection<int, FlightSignatureInterface>
     */
    public function getSignatures(): Collection;

    /**
     * @return Collection<int, BuoyInterface>
     */
    public function getBuoys(): Collection;

    public function getRandomWormholeEntry(): ?WormholeEntryInterface;

    public function isMap(): bool;

    public function isOverWormhole(): bool;

    public function getSectorString(): string;
}
