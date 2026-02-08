<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowEpsUsage;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystem;

final class ShowEpsUsage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_EPS_USAGE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private readonly SpacecraftLoaderInterface $spacecraftLoader,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle('EPS Verbrauch');
        $game->setMacroInAjaxWindow('html/spacecraft/epsUsage.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar(
            'SYSTEM_WRAPPERS',
            $this->sortByEnergyConsumption(
                $this->spacecraftSystemManager
                    ->getActiveSystems($wrapper->get())
                    ->filter(fn (SpacecraftSystem $system): bool => $this->spacecraftSystemManager->getEnergyConsumption($system->getSystemType()) > 0)
                    ->map(fn (SpacecraftSystem $system): SystemEpsUsageWrapper => new SystemEpsUsageWrapper(
                        $system,
                        $this->spacecraftSystemManager->getEnergyConsumption($system->getSystemType())
                    ))
                    ->toArray()
            )
        );
    }

    /**
     * @param array<SystemEpsUsageWrapper> $wrappers
     *
     * @return array<SystemEpsUsageWrapper>
     */
    private function sortByEnergyConsumption(array $wrappers): array
    {
        usort(
            $wrappers,
            fn (SystemEpsUsageWrapper $a, SystemEpsUsageWrapper $b): int => $b->getUsage() <=> $a->getUsage()
        );

        return $wrappers;
    }
}
