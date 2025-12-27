<?php

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystem;

class SpacecraftStartup implements SpacecraftStartupInterface
{
    public function __construct(
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[\Override]
    public function startup(SpacecraftWrapperInterface $wrapper, array $additionalSystemTypes = []): void
    {
        $wrapper
            ->get()
            ->getSystems()
            ->filter(
                fn(SpacecraftSystem $system): bool => !$system->getMode()->isActivated()
                && $system->isHealthy()
            )
            ->map(fn(SpacecraftSystem $system): SpacecraftSystemTypeInterface => $this->spacecraftSystemManager->lookupSystem($system->getSystemType()))
            ->filter(
                fn(SpacecraftSystemTypeInterface $systemType): bool => $systemType
                    ->getDefaultMode()
                    ->isActivated() || in_array($systemType->getSystemType(), $additionalSystemTypes)
            )
            ->forAll(function (int $key, SpacecraftSystemTypeInterface $systemType) use ($wrapper): bool {
                $this->helper->activate($wrapper, $systemType->getSystemType(), new InformationWrapper());
                return true;
            });
    }
}
