<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

/**
 * @Entity
 */
class VisualNavPanelEntryData
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

    public function getMapfieldType(): int
    {
        return $this->type;
    }

    public function getFieldGraphicID(): int
    {
        $fieldId = $this->getMapfieldType();


        if ($fieldId === 1) {
            return 0;
        } else {

            return $fieldId;
        }
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

    public function getDirection1Count(): ?int
    {
        return $this->d1c;
    }

    public function getDirection2Count(): ?int
    {
        return $this->d2c;
    }

    public function getDirection3Count(): ?int
    {
        return $this->d3c;
    }

    public function getDirection4Count(): ?int
    {
        return $this->d4c;
    }
}
