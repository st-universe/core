<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use AccessViolation;
use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowModuleScreen implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN';

    private $colonyLoader;

    private $showModuleScreenRequest;

    private $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleScreenRequestInterface $showModuleScreenRequest,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleScreenRequest = $showModuleScreenRequest;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleScreenRequest->getColonyId(),
            $userId
        );

        $rump = $this->shipRumpRepository->find($this->showModuleScreenRequest->getRumpId());

        if ($rump === null || !array_key_exists($rump->getId(), $this->shipRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation();
        }

        $moduleScreenTabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($i, $colony, $rump));
        }

        $moduleSelectors = [];
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($i == MODULE_TYPE_SPECIAL) {
                $moduleSelectors[] = new ModuleSelectorSpecial(
                    $i,
                    $colony,
                    $rump,
                    $userId
                );
            } else {
                $moduleSelectors[] = new ModuleSelector(
                    $i,
                    $colony,
                    $rump,
                    $userId
                );
            }
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName());

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
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('MODULE_SCREEN_TABS', $moduleScreenTabs);
        $game->setTemplateVar('MODULE_SLOTS', range(1, MODULE_TYPE_COUNT));
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('PLAN', false);
    }
}
