<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Shiprump;
use Stu\Lib\ModuleScreen\ModuleSelectWrapper;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipBuildplanRepository")
 * @Table(
 *     name="stu_buildplans",
 *     indexes={
 *     }
 * )
 **/
class ShipBuildplan implements ShipBuildplanInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="integer") * */
    private $buildtime = 0;

    /** @Column(type="string", length=32, nullable=true) */
    private $signature = '';

    /** @Column(type="smallint") * */
    private $crew = 0;

    /** @Column(type="smallint") * */
    private $crew_percentage = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ShipBuildplanInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ShipBuildplanInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipBuildplanInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ShipBuildplanInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function isDeleteable(): bool
    {
        // @todo
        return false;
    }

    public static function createSignature(array $modules): string
    {
        return md5(implode('_', $modules));
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): ShipBuildplanInterface
    {
        $this->signature = $signature;

        return $this;
    }

    public function getCrew(): int
    {
        return $this->crew;
    }

    public function setCrew(int $crew): ShipBuildplanInterface
    {
        $this->crew = $crew;

        return $this;
    }

    public function getCrewPercentage(): int
    {
        return $this->crew_percentage;
    }

    public function setCrewPercentage(int $crewPercentage): ShipBuildplanInterface
    {
        $this->crew_percentage = $crewPercentage;

        return $this;
    }

    public function getRump(): Shiprump
    {
        return new Shiprump($this->getRumpId());
    }

    public function getModulesByType($type): array
    {
        // @todo refactor
        global $container;

        return $container->get(BuildplanModuleRepositoryInterface::class)->getByBuildplanAndModuleType(
            (int)$this->getId(),
            (int)$type
        );
    }

    public function getModules(): array
    {
        // @todo refactor
        global $container;

        return $container->get(BuildplanModuleRepositoryInterface::class)->getByBuildplan($this->getId());
    }

    public function getModule(): ModuleSelectWrapper
    {
        return new ModuleSelectWrapper($this);
    }
}
