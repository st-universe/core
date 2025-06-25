<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\TholianWebRepository;

#[Table(name: 'stu_tholian_web')]
#[Entity(repositoryClass: TholianWebRepository::class)]
class TholianWeb extends Spacecraft implements TholianWebInterface
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $finished_time = 0;

    /**
     * @var ArrayCollection<int, SpacecraftInterface>
     */
    #[OneToMany(targetEntity: Spacecraft::class, mappedBy: 'holdingWeb')]
    private Collection $capturedSpacecrafts;

    public function __construct()
    {
        parent::__construct();
        $this->capturedSpacecrafts = new ArrayCollection();
    }

    #[Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::THOLIAN_WEB;
    }

    #[Override]
    public function getFleet(): ?FleetInterface
    {
        return null;
    }

    #[Override]
    public function getFinishedTime(): ?int
    {
        return $this->finished_time;
    }

    #[Override]
    public function setFinishedTime(?int $time): TholianWebInterface
    {
        $this->finished_time = $time;

        return $this;
    }

    #[Override]
    public function isFinished(): bool
    {
        //uninitialized
        if ($this->finished_time === 0) {
            return false;
        }

        //finished
        if ($this->finished_time === null) {
            return true;
        }

        return $this->finished_time < time();
    }

    #[Override]
    public function getCapturedSpacecrafts(): Collection
    {
        return $this->capturedSpacecrafts;
    }

    #[Override]
    public function updateFinishTime(int $time): void
    {
        $this->finished_time = $time;
    }

    #[Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        throw new RuntimeException('unsupported operation');
    }
}
