<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'stu_spacecraft_assimilated')]
class SpacecraftAssimilated
{
    #[Id]
    #[OneToOne(targetEntity: Spacecraft::class, inversedBy: 'assimilation')]
    #[JoinColumn(name: 'spacecraft_id', referencedColumnName: 'id', nullable: false)]
    private Spacecraft $spacecraft;

    #[ManyToOne(targetEntity: Spacecraft::class)]
    #[JoinColumn(name: 'source_id', referencedColumnName: 'id', nullable: true)]
    private ?Spacecraft $source = null;

    #[Column(type: 'integer')]
    private int $progress = 0;

    #[Column(name: 'initial_progress', type: 'integer')]
    private int $initialProgress = 0;

    #[Column(name: 'started_at', type: 'integer', nullable: true)]
    private ?int $startedAt = null;

    #[Column(name: 'duration_seconds', type: 'integer')]
    private int $durationSeconds = 900;

    #[Column(name: 'borg_user_id', type: 'integer')]
    private int $borgUserId = 0;

    #[Column(name: 'nanite_commodity_id', type: 'integer')]
    private int $naniteCommodityId = 76;

    #[Column(name: 'nanite_reward_min', type: 'integer')]
    private int $naniteRewardMin = 1;

    #[Column(name: 'nanite_reward_max', type: 'integer')]
    private int $naniteRewardMax = 30;

    #[Column(name: 'completed_at', type: 'integer', nullable: true)]
    private ?int $completedAt = null;

    public function getSpacecraft(): Spacecraft
    {
        return $this->spacecraft;
    }

    public function setSpacecraft(Spacecraft $spacecraft): self
    {
        $this->spacecraft = $spacecraft;
        if ($spacecraft->getAssimilation() !== $this) {
            $spacecraft->setAssimilation($this);
        }
        return $this;
    }

    public function getSource(): ?Spacecraft
    {
        return $this->source;
    }

    public function setSource(?Spacecraft $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = max(0, min(10000, $progress));
        return $this;
    }

    public function getProgressAt(int $now): int
    {
        if (!$this->isActive()) {
            return $this->progress;
        }
        $elapsed = intdiv(max(0, $now - $this->startedAt), 60) * 60;
        return min(10000, max($this->progress, $this->initialProgress + intdiv($elapsed * (10000 - $this->initialProgress), max(1, $this->durationSeconds))));
    }

    public function start(Spacecraft $source, int $now, int $duration): self
    {
        $this->source = $source;
        $this->borgUserId = $source->getUser()->getId();
        $this->initialProgress = $this->progress;
        $this->startedAt = $now;
        $this->durationSeconds = max(60, $duration);
        $this->completedAt = null;
        return $this;
    }

    public function getStartedAt(): ?int
    {
        return $this->startedAt;
    }

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function getInitialProgress(): int
    {
        return $this->initialProgress;
    }

    public function isActive(): bool
    {
        return $this->startedAt !== null && $this->source !== null && $this->completedAt === null;
    }

    public function isComplete(): bool
    {
        return $this->completedAt !== null && $this->progress === 10000;
    }

    public function freeze(int $now): self
    {
        $this->progress = $this->getProgressAt($now);
        $this->startedAt = null;
        $this->source = null;
        return $this;
    }

    public function complete(int $now): self
    {
        $this->progress = 10000;
        $this->startedAt = null;
        $this->completedAt = $now;
        return $this;
    }

    public function getBorgUserId(): int
    {
        return $this->borgUserId;
    }

    public function getNaniteCommodityId(): int
    {
        return $this->naniteCommodityId;
    }

    public function setNaniteCommodityId(int $id): self
    {
        $this->naniteCommodityId = $id;
        return $this;
    }

    public function setNaniteReward(int $minimum, int $maximum): self
    {
        $this->naniteRewardMin = max(1, $minimum);
        $this->naniteRewardMax = max($this->naniteRewardMin, $maximum);
        return $this;
    }

    public function getNaniteRewardMin(): int
    {
        return $this->naniteRewardMin;
    }

    public function getNaniteRewardMax(): int
    {
        return $this->naniteRewardMax;
    }
}
