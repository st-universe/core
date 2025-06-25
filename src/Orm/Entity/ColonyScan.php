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
use Stu\Orm\Repository\ColonyScanRepository;

#[Table(name: 'stu_colony_scan')]
#[Entity(repositoryClass: ColonyScanRepository::class)]
class ColonyScan implements ColonyScanInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colony_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $colony_user_id = 0;

    #[Column(type: 'string', nullable: true)]
    private ?string $colony_name = '';

    #[Column(type: 'string')]
    private string $colony_user_name = '';

    #[Column(type: 'text')]
    private string $mask;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;


    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;


    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    #[Override]
    public function setColonyId(int $colonyid): ColonyScanInterface
    {
        $this->colony_id = $colonyid;
        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setUserId(int $userid): ColonyScanInterface
    {
        $this->user_id = $userid;
        return $this;
    }

    #[Override]
    public function getColonyUserId(): int
    {
        return $this->colony_user_id;
    }

    #[Override]
    public function setColonyUserId(int $colonyuserid): ColonyScanInterface
    {
        $this->colony_user_id = $colonyuserid;
        return $this;
    }

    #[Override]
    public function getColonyName(): ?string
    {
        return $this->colony_name;
    }

    #[Override]
    public function setColonyName(?string $colonyname): ColonyScanInterface
    {
        $this->colony_name = $colonyname;
        return $this;
    }

    #[Override]
    public function getColonyUserName(): string
    {
        return $this->colony_user_name;
    }

    #[Override]
    public function setColonyUserName(string $colonyusername): ColonyScanInterface
    {
        $this->colony_user_name = $colonyusername;
        return $this;
    }

    #[Override]
    public function getFieldData(): string
    {
        return $this->mask;
    }

    #[Override]
    public function setFieldData(string $fieldData): ColonyScanInterface
    {
        $this->mask = $fieldData;
        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): ColonyScanInterface
    {
        $this->date = $date;
        return $this;
    }

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): ColonyScanInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): ColonyScanInterface
    {
        $this->user = $user;
        return $this;
    }

    #[Override]
    public function isAbandoned(): bool
    {
        return $this->getColony()->getUserId() !== $this->colony_user_id;
    }
}
