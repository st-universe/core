<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

use Stu\Exception\AccessViolation;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class PirateProtection implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PIRATE_PROTECTION';

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;
    private PirateWrathManagerInterface $pirateWrathManager;

    private PirateProtectionRequestInterface $pirateProtectionRequest;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        PirateWrathManagerInterface $pirateWrathManager,
        PirateProtectionRequestInterface $pirateProtectionRequest
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->pirateWrathManager = $pirateWrathManager;
        $this->pirateProtectionRequest = $pirateProtectionRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $prestige = $this->pirateProtectionRequest->getPrestige();

        if ($prestige < 1) {
            $game->addInformation(_('Mindestens 1 Prestige ist erforderlich'));
            return;
        }

        if ($prestige > $user->getPrestige()) {
            $game->addInformation(sprintf(
                _('Nicht genügend Prestige vorhanden. Du hast nur %d Prestige'),
                $user->getPrestige()
            ));
            return;
        }

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Pirate Protection'),
                $userId
            ));
        }

        $this->pirateWrathManager->setProtectionTimeoutFromPrestige($user, $prestige, $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
