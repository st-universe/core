<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\Trait\SpacecraftHullColorStyleTrait;

#[Table(name: 'stu_spacecraft_condition')]
#[Entity]
class SpacecraftCondition
{
    use SpacecraftHullColorStyleTrait;

    #[Column(type: 'integer', length: 6)]
    private int $hull = 0;

    #[Column(type: 'integer', length: 6)]
    private int $shield = 0;

    #[Column(type: 'boolean')]
    private bool $is_disabled = false;

    #[Column(type: 'smallint', enumType: SpacecraftStateEnum::class)]
    private SpacecraftStateEnum $state = SpacecraftStateEnum::NONE;

    #[Id]
    #[OneToOne(targetEntity: Spacecraft::class, inversedBy: 'condition')]
    #[JoinColumn(name: 'spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $spacecraft;

    // transitive fields
    private bool $is_destroyed = false;

    public function __construct(Spacecraft $spacecraft)
    {
        $this->spacecraft = $spacecraft;
    }

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function getHull(): int
    {
        return $this->hull;
    }

    public function setHull(int $hull): SpacecraftCondition
    {
        $this->hull = $hull;
        return $this;
    }

    public function changeHull(int $amount): SpacecraftCondition
    {
        $this->hull += $amount;
        return $this;
    }

    public function getShield(): int
    {
        return $this->shield;
    }

    public function setShield(int $shield): SpacecraftCondition
    {
        $this->shield = $shield;
        return $this;
    }

    public function changeShield(int $amount): SpacecraftCondition
    {
        $this->shield += $amount;
        return $this;
    }

    public function isDestroyed(): bool
    {
        return $this->is_destroyed;
    }

    public function setIsDestroyed(bool $isDestroyed): SpacecraftCondition
    {
        $this->is_destroyed = $isDestroyed;
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    public function setDisabled(bool $isDisabled): SpacecraftCondition
    {
        $this->is_disabled = $isDisabled;
        return $this;
    }

    public function getState(): SpacecraftStateEnum
    {
        return $this->state;
    }

    public function setState(SpacecraftStateEnum $state): SpacecraftCondition
    {
        $this->state = $state;
        return $this;
    }

    public function isUnderRepair(): bool
    {
        return $this->getState()->isRepairState();
    }

    public function isUnderRetrofit(): bool
    {
        return $this->getState() === SpacecraftStateEnum::RETROFIT;
    }
}
