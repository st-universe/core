<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Shiprump;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleScreen implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN';

    private $colonyLoader;

    private $showModuleScreenRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleScreenRequestInterface $showModuleScreenRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleScreenRequest = $showModuleScreenRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleScreenRequest->getColonyId(),
            $userId
        );

        $rump = new Shiprump($this->showModuleScreenRequest->getRumpId());

        $moduleScreenTabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($i, $colony, $rump, false));
        }

        $moduleSelectors = [];
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($i == MODULE_TYPE_SPECIAL) {
                $moduleSelectors[] = new ModuleSelectorSpecial(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    false
                );
            } else {
                $moduleSelectors[] = new ModuleSelector(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    false
                );
            }
        }

        $game->appendNavigationPart(
            sprintf(
                '?id=%d&SHOW_MODULE_SCREEN=1&rump=%d',
                $colony->getId(),
                $rump->getId()
            ),
            _('Schiffbau')
        );
        $game->setPagetitle(_('Schiffbau'));
        $game->setTemplateFile('html/modulescreen.xhtml');
        $rump->enforceBuildableByUser($userId);

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('MODULE_SCREEN_TABS', $moduleScreenTabs);
        $game->setTemplateVar('MODULE_SLOTS', range(1, MODULE_TYPE_COUNT));
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('PLAN', false);
    }
}
