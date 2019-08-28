<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use ModuleScreenTab;
use ModuleScreenTabWrapper;
use ModuleSelector;
use ModuleSelectorSpecial;
use ShipBuildplans;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ShowModuleScreenBuildplan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN_BUILDPLAN';

    private $colonyLoader;

    private $showModuleScreenBuildplanRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleScreenBuildplanRequestInterface $showModuleScreenBuildplanRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleScreenBuildplanRequest = $showModuleScreenBuildplanRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleScreenBuildplanRequest->getColonyId(),
            $userId
        );

        $plan = new ShipBuildplans($this->showModuleScreenBuildplanRequest->getBuildplanId());
        if (!$plan->ownedByCurrentUser()) {
            return;
        }
        $rump = $plan->getRump();

        $moduleScreenTabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($i, $colony, $rump, $plan));
        }

        $moduleSelectors = [];
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($i == MODULE_TYPE_SPECIAL) {
                $moduleSelectors[] = new ModuleSelectorSpecial(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    $plan
                );
            } else {
                $moduleSelectors[] = new ModuleSelector(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    $plan,
                );
            }
        }

        $game->appendNavigationPart(
            sprintf('?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getNameWithoutMarkup());

        $game->appendNavigationPart(
            sprintf(
                '?id=%d&SHOW_MODULE_SCREEN=1&planid=%d',
                $colony->getId(),
                $plan->getId()
            ),
            _('Schiffbau')
        );
        $game->setPagetitle(_('Schiffbau'));
        $game->setTemplateFile('html/modulescreen.xhtml');
        $rump->enforceBuildableByUser($userId);

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('MODULE_SLOTS', range(1, MODULE_TYPE_COUNT));
    }
}
