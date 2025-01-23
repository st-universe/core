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
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\RepairTaskRepository;

#[Table(name: 'stu_repair_task')]
#[Entity(repositoryClass: RepairTaskRepository::class)]
class RepairTask implements RepairTaskInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $spacecraft_id = 0;

    #[Column(type: 'integer')]
    private int $finish_time = 0;

    #[Column(type: 'integer', enumType: SpacecraftSystemTypeEnum::class)]
    private SpacecraftSystemTypeEnum $system_type = SpacecraftSystemTypeEnum::HULL;

    #[Column(type: 'integer')]
    private int $healing_percentage = 0;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'Spacecraft')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftInterface $spacecraft;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): RepairTaskInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function setSpacecraft(SpacecraftInterface $spacecraft): RepairTaskInterface
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function setFinishTime(int $finishTime): RepairTaskInterface
    {
        $this->finish_time = $finishTime;
        return $this;
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    #[Override]
    public function setSystemType(SpacecraftSystemTypeEnum $type): RepairTaskInterface
    {
        $this->system_type = $type;
        return $this;
    }

    #[Override]
    public function getHealingPercentage(): int
    {
        return $this->healing_percentage;
    }

    #[Override]
    public function setHealingPercentage(int $healingPercentage): RepairTaskInterface
    {
        $this->healing_percentage = $healingPercentage;
        return $this;
    }
}
