<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Component\Borg\BorgSpherePhaseEnum;

#[Entity]
#[Table(name: 'stu_borg_sphere_event')]
#[UniqueConstraint(name: 'borg_sphere_configuration_idx', columns: ['configuration_id'])]
#[UniqueConstraint(name: 'borg_sphere_ship_idx', columns: ['ship_id'])]
class BorgSphereEvent
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer')]
    private int $id;
    #[ManyToOne(targetEntity: BorgSphereConfiguration::class)]
    #[JoinColumn(name: 'configuration_id', referencedColumnName: 'id', nullable: false)]
    private BorgSphereConfiguration $configuration;
    #[Column(name: 'ship_id', type: 'integer', nullable: true)]
    private ?int $shipId = null;
    #[Column(type: 'string', enumType: BorgSpherePhaseEnum::class)]
    private BorgSpherePhaseEnum $phase = BorgSpherePhaseEnum::APPROACH;
    #[Column(name: 'first_disabled_at', type: 'integer', nullable: true)]
    private ?int $firstDisabledAt = null;
    #[Column(name: 'next_tick_at', type: 'integer')]
    private int $nextTickAt = 0;
    #[Column(name: 'transwarp_progress', type: 'integer')]
    private int $transwarpProgress = 10000;
    #[Column(name: 'transwarp_floor', type: 'integer')]
    private int $transwarpFloor = 1000;
    #[Column(name: 'initial_attack_done', type: 'boolean')]
    private bool $initialAttackDone = false;
    public function getId(): int
    {
        return $this->id;
    }
    public function getConfiguration(): BorgSphereConfiguration
    {
        return $this->configuration;
    }
    public function setConfiguration(BorgSphereConfiguration $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }
    public function getShipId(): ?int
    {
        return $this->shipId;
    }
    public function setShipId(?int $shipId): self
    {
        $this->shipId = $shipId;
        return $this;
    }
    public function getPhase(): BorgSpherePhaseEnum
    {
        return $this->phase;
    }
    public function setPhase(BorgSpherePhaseEnum $phase): self
    {
        $this->phase = $phase;
        return $this;
    }
    public function getFirstDisabledAt(): ?int
    {
        return $this->firstDisabledAt;
    }
    public function setFirstDisabledAt(?int $time): self
    {
        $this->firstDisabledAt = $time;
        return $this;
    }
    public function getNextTickAt(): int
    {
        return $this->nextTickAt;
    }
    public function setNextTickAt(int $time): self
    {
        $this->nextTickAt = $time;
        return $this;
    }
    public function getTranswarpProgress(): int
    {
        return $this->transwarpProgress;
    }
    public function setTranswarpProgress(int $value): self
    {
        $this->transwarpProgress = $value;
        return $this;
    }
    public function getTranswarpFloor(): int
    {
        return $this->transwarpFloor;
    }
    public function setTranswarpFloor(int $value): self
    {
        $this->transwarpFloor = $value;
        return $this;
    }
    public function isInitialAttackDone(): bool
    {
        return $this->initialAttackDone;
    }
    public function setInitialAttackDone(bool $done): self
    {
        $this->initialAttackDone = $done;
        return $this;
    }
    public function isFinished(): bool
    {
        return in_array($this->phase, [BorgSpherePhaseEnum::ESCAPED, BorgSpherePhaseEnum::DESTROYED], true);
    }
}
