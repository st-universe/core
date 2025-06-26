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
use Stu\Orm\Repository\AllianceSettingsRepository;

#[Table(name: 'stu_alliance_settings')]
#[Entity(repositoryClass: AllianceSettingsRepository::class)]
class AllianceSettings
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $setting = '';

    #[Column(type: 'string')]
    private string $value = '';

    #[ManyToOne(targetEntity: Alliance::class)]
    #[JoinColumn(name: 'alliance_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Alliance $alliance;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    public function setAlliance(Alliance $alliance): AllianceSettings
    {
        $this->alliance = $alliance;
        return $this;
    }

    public function getSetting(): string
    {
        return $this->setting;
    }

    public function setSetting(string $setting): AllianceSettings
    {
        $this->setting = $setting;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): AllianceSettings
    {
        $this->value = $value;
        return $this;
    }
}
