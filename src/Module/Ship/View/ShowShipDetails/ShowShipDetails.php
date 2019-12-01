<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipDetails;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;

final class ShowShipDetails implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPDETAILS';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Schiffsinformationen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/shipdetails');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'HULL_STATUS_BAR',
            (new TalStatusBar())
                ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
                ->setLabel(_('Hülle'))
                ->setMaxValue($ship->getMaxHuell())
                ->setValue($ship->getHuell())
                ->render()
        );
        $game->setTemplateVar(
            'SHIELD_STATUS_BAR',
            (new TalStatusBar())
                ->setColor($ship->getShieldState() === true ? StatusBarColorEnum::STATUSBAR_BLUE : StatusBarColorEnum::STATUSBAR_DARKBLUE)
                ->setLabel(_('Schilde'))
                ->setMaxValue($ship->getMaxShield())
                ->setValue($ship->getShield())
                ->render()
        );
        $game->setTemplateVar(
            'EPS_STATUS_BAR',
            (new TalStatusBar())
                ->setColor(StatusBarColorEnum::STATUSBAR_YELLOW)
                ->setLabel(_('Energie'))
                ->setMaxValue($ship->getMaxEps())
                ->setValue($ship->getEps())
                ->render()
        );
    }
}
