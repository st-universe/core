<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;

#[MappedSuperclass]
abstract class TSpacecraftItem implements TSpacecraftItemInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $ship_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $fleet_id = null;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $warp_state = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tractor_warp_state = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $cloak_state = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $shield_state = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $uplink_state = null;

    #[Column(type: 'string')]
    private string $spacecraft_type = '';

    #[Column(type: 'string')]
    private string $ship_name = '';

    #[Column(type: 'integer')]
    private int $hull = 0;

    #[Column(type: 'integer')]
    private int $max_hull = 0;

    #[Column(type: 'integer')]
    private int $shield = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $web_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $web_finish_time = null;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'string')]
    private string $user_name = '';

    #[Column(type: 'integer')]
    private int $rump_category_id = 0;

    #[Column(type: 'string')]
    private string $rump_name = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $rump_role_id = null;

    #[Column(type: 'boolean')]
    private bool $has_logbook = false;

    #[Column(type: 'boolean')]
    private bool $has_crew = false;

    #[Override]
    public function getShipId(): int
    {
        return $this->ship_id;
    }

    #[Override]
    public function getFleetId(): ?int
    {
        return $this->fleet_id;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getWarpDriveState(): int
    {
        return $this->warp_state ?? 0;
    }

    #[Override]
    public function getTractorWarpState(): int
    {
        return $this->tractor_warp_state ?? 0;
    }

    #[Override]
    public function isCloaked(): int
    {
        return $this->cloak_state ?? 0;
    }

    #[Override]
    public function isShielded(): int
    {
        return $this->shield_state ?? 0;
    }

    #[Override]
    public function getUplinkState(): int
    {
        return $this->uplink_state ?? 0;
    }

    #[Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::from($this->spacecraft_type);
    }

    #[Override]
    public function getShipName(): string
    {
        return $this->ship_name;
    }

    #[Override]
    public function getHull(): int
    {
        return $this->hull;
    }

    #[Override]
    public function getMaxHull(): int
    {
        return $this->max_hull;
    }

    #[Override]
    public function getShield(): int
    {
        return $this->shield;
    }

    #[Override]
    public function getWebId(): ?int
    {
        return $this->web_id;
    }

    #[Override]
    public function getWebFinishTime(): ?int
    {
        return $this->web_finish_time;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function getUserName(): string
    {
        return $this->user_name;
    }

    #[Override]
    public function getRumpCategoryId(): int
    {
        return $this->rump_category_id;
    }

    #[Override]
    public function getRumpName(): string
    {
        return $this->rump_name;
    }

    #[Override]
    public function getRumpRoleId(): ?int
    {
        return $this->rump_role_id;
    }

    #[Override]
    public function hasLogbook(): bool
    {
        return $this->has_logbook;
    }

    #[Override]
    public function hasCrew(): bool
    {
        return $this->has_crew;
    }

    public static function addTSpacecraftItemFields(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('s', 'shipid', 'ship_id');
        $rsm->addFieldResult('s', 'fleetid', 'fleet_id');
        $rsm->addFieldResult('s', 'rumpid', 'rump_id');
        $rsm->addFieldResult('s', 'warpstate', 'warp_state');
        $rsm->addFieldResult('s', 'tractorwarpstate', 'tractor_warp_state');
        $rsm->addFieldResult('s', 'cloakstate', 'cloak_state');
        $rsm->addFieldResult('s', 'shieldstate', 'shield_state');
        $rsm->addFieldResult('s', 'uplinkstate', 'uplink_state');
        $rsm->addFieldResult('s', 'spacecrafttype', 'spacecraft_type');
        $rsm->addFieldResult('s', 'shipname', 'ship_name');
        $rsm->addFieldResult('s', 'hull', 'hull');
        $rsm->addFieldResult('s', 'maxhull', 'max_hull');
        $rsm->addFieldResult('s', 'shield', 'shield');
        $rsm->addFieldResult('s', 'webid', 'web_id');
        $rsm->addFieldResult('s', 'webfinishtime', 'web_finish_time');
        $rsm->addFieldResult('s', 'userid', 'user_id');
        $rsm->addFieldResult('s', 'username', 'user_name');
        $rsm->addFieldResult('s', 'rumpcategoryid', 'rump_category_id');
        $rsm->addFieldResult('s', 'rumpname', 'rump_name');
        $rsm->addFieldResult('s', 'rumproleid', 'rump_role_id');
        $rsm->addFieldResult('s', 'haslogbook', 'has_logbook');
        $rsm->addFieldResult('s', 'hascrew', 'has_crew');
    }
}
