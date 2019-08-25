<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use ModuleScreenTab;
use ModuleScreenTabWrapper;
use ModuleSelector;
use ModuleSelectorSpecial;
use request;
use Shiprump;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleScreen implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $rump = new Shiprump(request::indInt('rump'));

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
