<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\PirateProtection;

use Stu\Exception\AccessViolationException;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class PirateProtection implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PIRATE_PROTECTION';

    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private PirateWrathManagerInterface $pirateWrathManager, private PirateProtectionRequestInterface $pirateProtectionRequest) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser()->getId();
        $user = $context->getUser();
        $context->setView(ShowDeals::VIEW_IDENTIFIER);

        $prestige = $this->pirateProtectionRequest->getPrestige();

        if ($prestige < 1) {
            $context->getInfo()->addInformation(_('Mindestens 1 Prestige ist erforderlich'));
            return;
        }

        if ($prestige > $user->getPrestige()) {
            $context->getInfo()->addInformation(sprintf(
                _('Nicht genügend Prestige vorhanden. Du hast nur %d Prestige'),
                $user->getPrestige()
            ));
            return;
        }

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolationException(sprintf(
                _('UserId %d does not have license for Pirate Protection'),
                $userId
            ));
        }

        $this->pirateWrathManager->setProtectionTimeoutFromPrestige($user, $prestige, $context);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
