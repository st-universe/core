<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\TholianWebRepository;

#[Table(name: 'stu_tholian_web')]
#[Entity(repositoryClass: TholianWebRepository::class)]
class TholianWeb extends Spacecraft
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $finished_time = 0;

    /**
     * @var ArrayCollection<int, Spacecraft>
     */
    #[OneToMany(targetEntity: Spacecraft::class, mappedBy: 'holdingWeb')]
    private Collection $capturedSpacecrafts;

    public function __construct()
    {
        parent::__construct();
        $this->capturedSpacecrafts = new ArrayCollection();
    }

    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::THOLIAN_WEB;
    }

    public function getFleet(): ?Fleet
    {
        return null;
    }

    public function getFinishedTime(): ?int
    {
        return $this->finished_time;
    }

    public function setFinishedTime(?int $time): TholianWeb
    {
        $this->finished_time = $time;

        return $this;
    }

    public function isFinished(?int $currentTime = null): bool
    {
        //uninitialized
        if ($this->finished_time === 0) {
            return false;
        }

        //finished
        if ($this->finished_time === null) {
            return true;
        }

        $time = $currentTime ?? time();

        return $this->finished_time < $time;
    }

    /**
     * @return Collection<int, Spacecraft>
     */
    public function getCapturedSpacecrafts(): Collection
    {
        return $this->capturedSpacecrafts;
    }

    public function updateFinishTime(int $time): void
    {
        $this->finished_time = $time;
    }

    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        throw new BadMethodCallException('unsupported operation');
    }
}
