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
use Stu\Orm\Repository\AllianceSettingsRepository;

#[Table(name: 'stu_alliance_settings')]
#[Entity(repositoryClass: AllianceSettingsRepository::class)]
class AllianceSettings implements AllianceSettingsInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $alliance_id;

    #[Column(type: 'string')]
    private string $setting = '';

    #[Column(type: 'string')]
    private string $value = '';

    #[ManyToOne(targetEntity: 'Alliance')]
    #[JoinColumn(name: 'alliance_id', referencedColumnName: 'id')]
    private AllianceInterface $alliance;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getAllianceId(): int
    {
        return $this->alliance_id;
    }

    #[Override]
    public function setAllianceId(int $allianceId): AllianceSettingsInterface
    {
        $this->alliance_id = $allianceId;
        return $this;
    }

    #[Override]
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    #[Override]
    public function setAlliance(AllianceInterface $alliance): AllianceSettingsInterface
    {
        $this->alliance = $alliance;
        $this->alliance_id = $alliance->getId();
        return $this;
    }

    #[Override]
    public function getSetting(): string
    {
        return $this->setting;
    }

    #[Override]
    public function setSetting(string $setting): AllianceSettingsInterface
    {
        $this->setting = $setting;
        return $this;
    }

    #[Override]
    public function getValue(): string
    {
        return $this->value;
    }

    #[Override]
    public function setValue(string $value): AllianceSettingsInterface
    {
        $this->value = $value;
        return $this;
    }
}
