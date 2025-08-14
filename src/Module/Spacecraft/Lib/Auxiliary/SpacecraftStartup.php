<?php

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class SpacecraftStartup implements SpacecraftStartupInterface
{
    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly ActivatorDeactivatorHelperInterface $helper
    ) {}

    public function startup(SpacecraftWrapperInterface $wrapper, array $additionalSystemTypes = []): void
    {
        $isSaveNeeded = false;

        $wrapper
            ->get()
            ->getSystems()
            ->filter(fn(SpacecraftSystem $system): bool => !$system->getMode()->isActivated() && $system->isHealthy())
            ->map(fn(SpacecraftSystem $system): SpacecraftSystemTypeInterface =>  $this->spacecraftSystemManager->lookupSystem($system->getSystemType()))
            ->filter(fn(SpacecraftSystemTypeInterface $systemType): bool => $systemType->getDefaultMode()->isActivated()
                || in_array($systemType->getSystemType(), $additionalSystemTypes))
            ->forAll(function (int $key, SpacecraftSystemTypeInterface $systemType) use ($wrapper, &$isSaveNeeded): bool {
                $isSaveNeeded = $this->helper->activate($wrapper, $systemType->getSystemType(), new InformationWrapper()) || $isSaveNeeded;
                return true;
            });

        if ($isSaveNeeded) {
            $this->spacecraftRepository->save($wrapper->get());
        }
    }
}
