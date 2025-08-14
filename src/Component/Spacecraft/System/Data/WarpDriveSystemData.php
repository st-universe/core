<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Module\Control\StuTime;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Module\Control\GameController;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;

class WarpDriveSystemData extends AbstractSystemData
{
    // warpdrive fields
    public int $wd = 0;
    public int $maxwd = 0;
    public int $split = 100;
    public bool $autoCarryOver = false;
    public int $warpsignature = 0;
    public int $wstimer = 0;

    private StuTime $stuTime;
    private SpacecraftRumpRepositoryInterface $rumpRepository;
    private DatabaseUserRepositoryInterface $databaseUserRepository;
    private GameController $game;

    public function __construct(
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        StatusBarFactoryInterface $statusBarFactory,
        StuTime $stuTime,
        SpacecraftRumpRepositoryInterface $rumpRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        GameController $game
    ) {
        parent::__construct($shipSystemRepository, $statusBarFactory);
        $this->stuTime = $stuTime;
        $this->rumpRepository = $rumpRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->game = $game;
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPDRIVE;
    }

    #[Override]
    public function update(): void
    {
        $this->split = max(0, min(100, $this->split));

        parent::update();
    }

    /**
     * @return array<SpacecraftRump>
     */
    public function getAllPossibleRumps(): array
    {
        $userId = $this->game->getUser()->getId();

        $allRumps = $this->rumpRepository->getList();

        $possibleRumps = [];

        foreach ($allRumps as $rump) {
            if (!$rump->getIsNpc() && $rump->getDatabaseId() !== null && ($rump->getCategoryId()->value < 9)) {
                if ($this->databaseUserRepository->exists($userId, $rump->getDatabaseId())) {
                    $possibleRumps[$rump->getId()] = $rump;
                }
            }
        }

        ksort($possibleRumps);

        return array_values($possibleRumps);
    }

    public function getWarpDrive(): int
    {
        return $this->wd;
    }

    public function setWarpDrive(int $wd): WarpDriveSystemData
    {
        $this->wd = $wd;
        return $this;
    }

    public function lowerWarpDrive(int $amount): WarpDriveSystemData
    {
        $this->wd -= $amount;
        return $this;
    }

    public function setMaxWarpDrive(int $maxwd): WarpDriveSystemData
    {
        $this->maxwd = $maxwd;
        return $this;
    }

    public function getTheoreticalMaxWarpdrive(): int
    {
        return $this->maxwd;
    }

    /**
     * proportional to warpdrive system status
     */
    public function getMaxWarpdrive(): int
    {
        return (int) (ceil($this->maxwd
            * $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus() / 100));
    }

    public function getWarpDriveSplit(): int
    {
        return $this->split;
    }

    public function setWarpDriveSplit(int $split): WarpDriveSystemData
    {
        $this->split = $split;
        return $this;
    }

    public function getAutoCarryOver(): bool
    {
        return $this->autoCarryOver;
    }

    public function setAutoCarryOver(bool $autoCarryOver): WarpDriveSystemData
    {
        $this->autoCarryOver = $autoCarryOver;
        return $this;
    }

    public function getWarpdrivePercentage(): int
    {
        $currentWarpdrive = $this->getWarpDrive();
        $maxWarpdrive = $this->getMaxWarpdrive();

        if ($currentWarpdrive === 0) {
            return 0;
        }
        if ($maxWarpdrive === 0) {
            return 100;
        }

        return (int)floor($currentWarpdrive / $maxWarpdrive * 100);
    }

    public function getWarpDriveStatusBar(): string
    {
        return $this->getStatusBar(
            _('Warpantrieb'),
            $this->getWarpDrive(),
            $this->getMaxWarpdrive(),
            StatusBarColorEnum::BLUE
        )
            ->render();
    }

    public function getWarpDriveStatusBarBig(): string
    {
        return $this->getStatusBar(
            _('Warpantrieb'),
            $this->getWarpDrive(),
            $this->getMaxWarpdrive(),
            StatusBarColorEnum::BLUE
        )
            ->setSizeModifier(1.6)
            ->render();
    }

    public function getWarpSignature(): int
    {
        return $this->warpsignature;
    }

    public function setWarpSignature(int $warpsignature): WarpDriveSystemData
    {
        $this->warpsignature = $warpsignature;
        return $this;
    }

    public function getWarpSignatureTimer(): int
    {
        return $this->wstimer;
    }

    public function setWarpSignatureTimer(int $wstimer): WarpDriveSystemData
    {
        $this->wstimer = $wstimer;
        return $this;
    }

    public function isWarpSignatureActive(): bool
    {
        return $this->getWarpSignature() > 0 && $this->getWarpSignatureTimer() + 300 >= $this->stuTime->time();
    }
}
