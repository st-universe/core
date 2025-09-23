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
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum;
use Stu\Component\Ship\Wormhole\WormholeEntryModeEnum;
use Stu\Orm\Repository\WormholeRestrictionRepository;

#[Table(name: 'stu_wormhole_restrictions')]
#[Entity(repositoryClass: WormholeRestrictionRepository::class)]
class WormholeRestriction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: WormholeEntry::class)]
    #[JoinColumn(name: 'wormhole_entry_id', nullable: false, referencedColumnName: 'id')]
    private WormholeEntry $wormholeEntry;

    #[Column(type: 'integer')]
    private int $target = 0;

    #[Column(type: 'smallint', enumType: WormholeEntryTypeEnum::class, nullable: true)]
    private ?WormholeEntryTypeEnum $privilege_type = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $mode = WormholeEntryModeEnum::ALLOW->value;

    public function getId(): int
    {
        return $this->id;
    }

    public function getWormholeEntry(): WormholeEntry
    {
        return $this->wormholeEntry;
    }

    public function setWormholeEntry(WormholeEntry $wormholeEntry): WormholeRestriction
    {
        $this->wormholeEntry = $wormholeEntry;
        return $this;
    }

    public function getTargetId(): int
    {
        return $this->target;
    }

    public function setTargetId(int $targetId): WormholeRestriction
    {
        $this->target = $targetId;
        return $this;
    }

    public function getPrivilegeType(): ?WormholeEntryTypeEnum
    {
        return $this->privilege_type;
    }

    public function setPrivilegeType(?WormholeEntryTypeEnum $privilegeType): WormholeRestriction
    {
        $this->privilege_type = $privilegeType;
        return $this;
    }


    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode): WormholeRestriction
    {
        $this->mode = $mode;
        return $this;
    }
}
