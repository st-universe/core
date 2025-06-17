<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use Override;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ShowModuleScreen implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ShowModuleScreenRequestInterface $showModuleScreenRequest,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private BuildplanHangarRepositoryInterface $buildplanHangarRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showModuleScreenRequest->getColonyId(),
            $userId,
            false
        );

        $rump = $this->spacecraftRumpRepository->find($this->showModuleScreenRequest->getRumpId());

        if ($rump === null || !array_key_exists($rump->getId(), $this->spacecraftRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation();
        }

        $buildplanHangar = $this->buildplanHangarRepository->getByRump($rump->getId());
        if ($buildplanHangar !== null) {
            throw new AccessViolation();
        }

        $moduleSelectors = [];
        foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {

            $moduleSelectors[] = $this->colonyLibFactory->createModuleSelector(
                $moduleType,
                $colony,
                $rump,
                $game->getUser()
            );
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName()
        );

        $game->appendNavigationPart(
            sprintf(
                '?id=%d&%s=1&rumpid=%d',
                $colony->getId(),
                self::VIEW_IDENTIFIER,
                $rump->getId()
            ),
            _('Schiffbau')
        );

        $game->setPagetitle(_('Schiffbau'));
        $game->setViewTemplate('html/ship/construction/moduleScreen.twig');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('PLAN', false);
        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
        );
    }
}
