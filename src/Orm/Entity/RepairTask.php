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
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Repository\RepairTaskRepository;

#[Table(name: 'stu_repair_task')]
#[Entity(repositoryClass: RepairTaskRepository::class)]
class RepairTask
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

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'spacecraft_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Spacecraft $spacecraft;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): RepairTask
    {
        $this->user = $user;
        return $this;
    }

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(Spacecraft $spacecraft): RepairTask
    {
        $this->spacecraft = $spacecraft;
        return $this;
    }

    public function setFinishTime(int $finishTime): RepairTask
    {
        $this->finish_time = $finishTime;
        return $this;
    }

    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return $this->system_type;
    }

    public function setSystemType(SpacecraftSystemTypeEnum $type): RepairTask
    {
        $this->system_type = $type;
        return $this;
    }

    public function getHealingPercentage(): int
    {
        return $this->healing_percentage;
    }

    public function setHealingPercentage(int $healingPercentage): RepairTask
    {
        $this->healing_percentage = $healingPercentage;
        return $this;
    }
}
