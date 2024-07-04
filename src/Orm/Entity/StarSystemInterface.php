<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface StarSystemInterface
{
    public function getId(): int;

    public function getCx(): ?int;

    public function getCy(): ?int;

    public function getType(): StarSystemTypeInterface;

    public function setType(StarSystemTypeInterface $systemType): StarSystemInterface;

    public function getName(): string;

    public function setName(string $name): StarSystemInterface;

    public function getMaxX(): int;

    public function setMaxX(int $maxX): StarSystemInterface;

    public function getMaxY(): int;

    public function setMaxY(int $maxY): StarSystemInterface;

    public function getBonusFieldAmount(): int;

    public function setBonusFieldAmount(int $bonusFieldAmount): StarSystemInterface;

    public function getSystemType(): StarSystemTypeInterface;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemInterface;

    public function getLayer(): ?LayerInterface;

    public function getMap(): ?MapInterface;

    public function getBase(): ?ShipInterface;

    /**
     * @return Collection<int, StarSystemMapInterface>
     */
    public function getFields(): Collection;

    public function isWormhole(): bool;
}
