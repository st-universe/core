<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

/**
 * @Entity
 */
class VisualPanelEntryData
{
    /** @Id @Column(type="integer") * */
    private int $posx = 0;
    /** @Id @Column(type="integer") * */
    private int $posy = 0;
    /** @Column(type="integer", nullable=true) * */
    private ?int $sysid = null;
    /** @Column(type="integer") * */
    private int $shipcount = 0;
    /** @Column(type="integer") * */
    private int $cloakcount = 0;
    /** @Column(type="string", nullable=true) * */
    private ?string $allycolor = null;
    /** @Column(type="string", nullable=true) * */
    private ?string $usercolor = null;
    /** @Column(type="string", nullable=true) * */
    private ?string $factioncolor = null;
    /** @Column(type="boolean", nullable=true) * */
    private ?bool $shieldstate = null;
    /** @Column(type="integer") * */
    private int $type = 0;
    /** @Column(type="integer", nullable=true) * */
    private ?int $d1c = null;
    /** @Column(type="integer", nullable=true) * */
    private ?int $d2c = null;
    /** @Column(type="integer", nullable=true) * */
    private ?int $d3c = null;
    /** @Column(type="integer", nullable=true) * */
    private ?int $d4c = null;

    public function getPosX(): int
    {
        return $this->posx;
    }

    public function getPosY(): int
    {
        return $this->posy;
    }

    public function getSystemId(): ?int
    {
        return $this->sysid;
    }

    public function setSystemId(int $systemId): VisualPanelEntryData
    {
        $this->sysid = $systemId;

        return $this;
    }

    public function getMapfieldType(): int
    {
        return $this->type;
    }

    public function setMapfieldType(int $type): VisualPanelEntryData
    {
        $this->type = $type;

        return $this;
    }

    public function getShipCount(): int
    {
        return $this->shipcount;
    }

    public function hasCloakedShips(): bool
    {
        return $this->cloakcount > 0;
    }

    public function getAllyColor(): ?string
    {
        return $this->allycolor;
    }

    public function getFactionColor(): ?string
    {
        return $this->factioncolor;
    }

    public function getUserColor(): ?string
    {
        return $this->usercolor;
    }

    public function getShieldState(): bool
    {
        return $this->shieldstate ?? false;
    }

    public function isSubspaceCodeAvailable(): bool
    {
        return $this->getDirection1Count() > 0
            || $this->getDirection2Count() > 0
            || $this->getDirection3Count() > 0
            || $this->getDirection4Count() > 0;
    }

    public function getDirection1Count(): int
    {
        return $this->d1c ?? 0;
    }

    public function getDirection2Count(): int
    {
        return $this->d2c ?? 0;
    }

    public function getDirection3Count(): int
    {
        return $this->d3c ?? 0;
    }

    public function getDirection4Count(): int
    {
        return $this->d4c ?? 0;
    }
}
