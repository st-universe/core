<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use Override;
use request;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShowModuleScreenBuildplan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN_BUILDPLAN';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ShipCrewCalculatorInterface $shipCrewCalculator,
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $user->getId(),
            false
        );

        $planId = $game->getViewContext(ViewContextTypeEnum::BUILDPLAN) ?? request::indInt('planid');

        $plan = $this->shipBuildplanRepository->find($planId);
        if ($plan === null || $plan->getUser() !== $user) {
            throw new SanityCheckException('This buildplan belongs to someone else', null, self::VIEW_IDENTIFIER);
        }

        $rump = $plan->getRump();

        $moduleSelectors = [];
        foreach (ShipModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {

            $moduleSelectors[] = $this->colonyLibFactory->createModuleSelector(
                $moduleType,
                $colony,
                $rump,
                $user,
                $plan
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
                '?id=%d&SHOW_MODULE_SCREEN_BUILDPLAN=1&planid=%d',
                $colony->getId(),
                $plan->getId()
            ),
            _('Schiffbau')
        );

        $game->setPagetitle(_('Schiffbau'));
        $game->setViewTemplate('html/ship/construction/moduleScreen.twig');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
        );
    }
}
