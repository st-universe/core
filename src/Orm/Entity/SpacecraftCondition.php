<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\Trait\SpacecraftHullColorStyleTrait;

#[Table(name: 'stu_spacecraft_condition')]
#[Entity]
class SpacecraftCondition implements SpacecraftConditionInterface
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
    #[OneToOne(targetEntity: 'Spacecraft', inversedBy: 'condition')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $spacecraft;

    // transitive fields
    private bool $is_destroyed = false;

    public function __construct(SpacecraftInterface $spacecraft)
    {
        $this->spacecraft = $spacecraft;
    }

    #[Override]
    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getHull(): int
    {
        return $this->hull;
    }

    #[Override]
    public function setHull(int $hull): SpacecraftConditionInterface
    {
        $this->hull = $hull;
        return $this;
    }

    #[Override]
    public function changeHull(int $amount): SpacecraftConditionInterface
    {
        $this->hull += $amount;
        return $this;
    }

    #[Override]
    public function getShield(): int
    {
        return $this->shield;
    }

    #[Override]
    public function setShield(int $shield): SpacecraftConditionInterface
    {
        $this->shield = $shield;
        return $this;
    }

    #[Override]
    public function changeShield(int $amount): SpacecraftConditionInterface
    {
        $this->shield += $amount;
        return $this;
    }

    #[Override]
    public function isDestroyed(): bool
    {
        return $this->is_destroyed;
    }

    #[Override]
    public function setIsDestroyed(bool $isDestroyed): SpacecraftConditionInterface
    {
        $this->is_destroyed = $isDestroyed;
        return $this;
    }

    #[Override]
    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    #[Override]
    public function setDisabled(bool $isDisabled): SpacecraftConditionInterface
    {
        $this->is_disabled = $isDisabled;
        return $this;
    }

    #[Override]
    public function getState(): SpacecraftStateEnum
    {
        return $this->state;
    }

    #[Override]
    public function setState(SpacecraftStateEnum $state): SpacecraftConditionInterface
    {
        $this->state = $state;
        return $this;
    }

    #[Override]
    public function isUnderRepair(): bool
    {
        return $this->getState()->isRepairState();
    }

    #[Override]
    public function isUnderRetrofit(): bool
    {
        return $this->getState() === SpacecraftStateEnum::RETROFIT;
    }
}
